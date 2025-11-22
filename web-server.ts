import express from 'express'
import { createServer } from 'http'
import { Server as SocketIO } from 'socket.io'
import { SessionManager } from './session-manager'

const app = express()
const server = createServer(app)
const io = new SocketIO(server, {
	cors: {
		origin: '*',
		methods: ['GET', 'POST']
	}
})

const sessionManager = new SessionManager(io)

// Serve static files
app.use(express.static('public'))
app.use('/api', express.static('api')) // Serve API folder
app.use(express.json())

// Main route
app.get('/', (req, res) => {
	res.sendFile(__dirname + '/public/index.html')
})

// Socket.IO connection
io.on('connection', (socket) => {
	console.log('Client connected:', socket.id)

	// Send all sessions status
	const sessions = sessionManager.getAllSessions()
	socket.emit('all-sessions', sessions)

	// Get all sessions
	socket.on('get-sessions', () => {
		const sessions = sessionManager.getAllSessions()
		socket.emit('all-sessions', sessions)
	})

	// Create new session
	socket.on('create-session', (sessionId: string) => {
		try {
			sessionManager.createSession(sessionId)
			const sessions = sessionManager.getAllSessions()
			io.emit('all-sessions', sessions)
			socket.emit('message', `Session ${sessionId} created`)
		} catch (error: any) {
			socket.emit('error', error.message)
		}
	})

	// Start session with QR
	socket.on('start-session-qr', async (sessionId: string) => {
		try {
			await sessionManager.startSession(sessionId, 'qr')
			socket.emit('message', `Starting session ${sessionId} with QR code`)
		} catch (error: any) {
			socket.emit('error', error.message)
		}
	})

	// Start session with pairing code
	socket.on('start-session-pairing', async (data: { sessionId: string, phoneNumber: string }) => {
		try {
			await sessionManager.startSession(data.sessionId, 'pairing', data.phoneNumber)
			socket.emit('message', `Starting session ${data.sessionId} with pairing code`)
		} catch (error: any) {
			socket.emit('error', error.message)
		}
	})

	// Logout session
	socket.on('logout', async (sessionId: string) => {
		try {
			await sessionManager.logout(sessionId)
			socket.emit('message', `Session ${sessionId} logged out successfully`)
		} catch (error: any) {
			socket.emit('error', error.message)
		}
	})

	// Delete session
	socket.on('delete-session', async (sessionId: string) => {
		try {
			await sessionManager.deleteSession(sessionId)
			const sessions = sessionManager.getAllSessions()
			io.emit('all-sessions', sessions)
			socket.emit('message', `Session ${sessionId} deleted`)
		} catch (error: any) {
			socket.emit('error', error.message)
		}
	})

	// Send message
	socket.on('send-message', async (data: { sessionId: string, phone: string, message: string, messageContent?: string }) => {
		try {
			await sessionManager.sendMessage(data.sessionId, data.phone, data.message)
			socket.emit('message-sent', { 
				success: true, 
				sessionId: data.sessionId,
				to: data.phone,
				messageContent: data.messageContent || data.message // Include message content for logging
			})
		} catch (error: any) {
			socket.emit('error', error.message)
		}
	})

	// Handle image/media sending
	socket.on('send-image', async (data: { sessionId: string, phone: string, image: string, caption?: string, mimetype?: string, filename?: string }) => {
		try {
			console.log('📸 Received send-image request for', data.phone)
			
			// Convert base64 to buffer
			const imageBuffer = Buffer.from(data.image, 'base64')
			
			await sessionManager.sendImage(data.sessionId, data.phone, imageBuffer, data.caption || '', data.mimetype, data.filename)
			
			socket.emit('image-sent', { 
				success: true, 
				sessionId: data.sessionId,
				to: data.phone,
				caption: data.caption || '',
				filename: data.filename || ''
			})
			socket.emit('message-sent', { 
				success: true, 
				sessionId: data.sessionId,
				to: data.phone 
			})
		} catch (error: any) {
			console.error('❌ Error sending image:', error.message)
			socket.emit('send-error', { 
				phone: data.phone, 
				error: error.message 
			})
			socket.emit('error', error.message)
		}
	})

	socket.on('disconnect', () => {
		console.log('Client disconnected:', socket.id)
	})
})

const PORT = process.env.PORT || 3000

server.listen(PORT, () => {
	console.log(`🚀 Server running on http://localhost:${PORT}`)
	console.log(`📱 Open browser and visit http://localhost:${PORT}`)
})
