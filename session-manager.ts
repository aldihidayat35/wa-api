import { Boom } from '@hapi/boom'
import P from 'pino'
import makeWASocket, {
	DisconnectReason,
	fetchLatestBaileysVersion,
	useMultiFileAuthState,
	makeCacheableSignalKeyStore,
	WASocket,
	AnyMessageContent
} from './src'

const logger = P({ level: 'silent' })

export interface Session {
	id: string
	sock: WASocket | null
	qrCode: string | null
	isConnected: boolean
	user: any
	type: 'qr' | 'pairing'
	phoneNumber?: string
	createdAt: Date
}

export class SessionManager {
	private sessions: Map<string, Session> = new Map()
	private socketIO: any

	constructor(io: any) {
		this.socketIO = io
	}

	createSession(sessionId: string): Session {
		if (this.sessions.has(sessionId)) {
			return this.sessions.get(sessionId)!
		}

		const session: Session = {
			id: sessionId,
			sock: null,
			qrCode: null,
			isConnected: false,
			user: null,
			type: 'qr',
			createdAt: new Date()
		}

		this.sessions.set(sessionId, session)
		return session
	}

	getSession(sessionId: string): Session | undefined {
		return this.sessions.get(sessionId)
	}

	getAllSessions(): Session[] {
		return Array.from(this.sessions.values()).map(s => ({
			id: s.id,
			isConnected: s.isConnected,
			user: s.user,
			type: s.type,
			phoneNumber: s.phoneNumber,
			createdAt: s.createdAt,
			sock: null,
			qrCode: null
		}))
	}

	async deleteSession(sessionId: string): Promise<void> {
		const session = this.sessions.get(sessionId)
		if (session) {
			if (session.sock) {
				try {
					await session.sock.logout()
				} catch (error) {
					console.error('Error logging out session:', error)
				}
			}
			this.sessions.delete(sessionId)
		}
	}

	async startSession(
		sessionId: string,
		type: 'qr' | 'pairing',
		phoneNumber?: string
	): Promise<void> {
		let session = this.getSession(sessionId)
		
		if (!session) {
			session = this.createSession(sessionId)
		}

		if (session.isConnected) {
			throw new Error('Session already connected')
		}

		if (session.sock) {
			throw new Error('Session already in progress')
		}

		session.type = type
		session.phoneNumber = phoneNumber

		const authFolder = `baileys_auth_info_${sessionId}`
		const { state, saveCreds } = await useMultiFileAuthState(authFolder)
		const { version } = await fetchLatestBaileysVersion()

		const sock = makeWASocket({
			version,
			logger,
			printQRInTerminal: type === 'qr',
			auth: {
				creds: state.creds,
				keys: makeCacheableSignalKeyStore(state.keys, logger),
			},
			browser: [`WhatsApp Web ${sessionId}`, 'Chrome', '1.0.0'],
			getMessage: async () => undefined
		})

		session.sock = sock

		// Handle connection updates
		sock.ev.on('connection.update', async (update) => {
			const { connection, lastDisconnect, qr } = update

			if (qr && type === 'qr') {
				session.qrCode = qr
				console.log(`QR Code generated for session ${sessionId}`)
				this.socketIO.emit('qr', { sessionId, qr })
			}

			if (connection === 'close') {
				const shouldReconnect = 
					(lastDisconnect?.error as Boom)?.output?.statusCode !== DisconnectReason.loggedOut
				
				session.sock = null
				session.isConnected = false
				session.qrCode = null

				if (shouldReconnect) {
					console.log(`Session ${sessionId} closed, reconnecting...`)
					this.socketIO.emit('session-status', {
						sessionId,
						status: 'reconnecting'
					})
					setTimeout(() => this.startSession(sessionId, type, phoneNumber), 3000)
				} else {
					console.log(`Session ${sessionId} logged out`)
					this.socketIO.emit('session-status', {
						sessionId,
						status: 'disconnected',
						isConnected: false
					})
				}
			} else if (connection === 'open') {
				session.isConnected = true
				session.qrCode = null
				session.user = sock.user
				console.log(`Session ${sessionId} connected!`)
				this.socketIO.emit('session-status', {
					sessionId,
					status: 'connected',
					isConnected: true,
					user: sock.user
				})
			}
		})

		// Save credentials
		sock.ev.on('creds.update', saveCreds)

		// Handle incoming messages
		sock.ev.on('messages.upsert', async ({ messages }) => {
			for (const msg of messages) {
				if (msg.message && !msg.key.fromMe) {
					this.socketIO.emit('message-received', {
						sessionId,
						from: msg.key.remoteJid,
						message: msg.message,
						timestamp: msg.messageTimestamp
					})
				}
			}
		})

		// Request pairing code
		if (type === 'pairing' && phoneNumber && !sock.authState.creds.registered) {
			const code = await sock.requestPairingCode(phoneNumber.replace(/[^0-9]/g, ''))
			this.socketIO.emit('pairing-code', { sessionId, code })
		}
	}

	async sendMessage(
		sessionId: string,
		phone: string,
		message: string
	): Promise<void> {
		const session = this.getSession(sessionId)
		
		if (!session || !session.sock || !session.isConnected) {
			throw new Error('Session not connected')
		}

		const jid = phone.includes('@s.whatsapp.net')
			? phone
			: `${phone.replace(/[^0-9]/g, '')}@s.whatsapp.net`

		await session.sock.sendMessage(jid, { text: message })
	}

	async sendImage(
		sessionId: string,
		phone: string,
		imageBuffer: Buffer,
		caption?: string,
		mimetype?: string,
		filename?: string
	): Promise<void> {
		const session = this.getSession(sessionId)
		
		if (!session || !session.sock || !session.isConnected) {
			throw new Error('Session not connected')
		}

		const jid = phone.includes('@s.whatsapp.net')
			? phone
			: `${phone.replace(/[^0-9]/g, '')}@s.whatsapp.net`

		const messageContent: AnyMessageContent = {
			image: imageBuffer,
		}

		if (caption) {
			messageContent.caption = caption
		}

		if (mimetype) {
			messageContent.mimetype = mimetype
		}

		if (filename) {
			messageContent.fileName = filename
		}

		console.log('📤 Sending image to', jid, 'size:', imageBuffer.length, 'bytes')
		await session.sock.sendMessage(jid, messageContent)
		console.log('✅ Image sent successfully')
	}

	async logout(sessionId: string): Promise<void> {
		const session = this.getSession(sessionId)
		
		if (session && session.sock) {
			await session.sock.logout()
			session.sock = null
			session.isConnected = false
			session.qrCode = null
			session.user = null
			this.socketIO.emit('session-status', {
				sessionId,
				status: 'disconnected',
				isConnected: false
			})
		}
	}
}
