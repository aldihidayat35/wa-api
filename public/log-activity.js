// Initialize Socket.IO
const socket = io()

// State
let allLogs = []
let filteredLogs = []
let currentFilter = 'all'
let searchQuery = ''

// Statistics
let stats = {
    messages: 0,
    sessions: 0,
    errors: 0,
    total: 0
}

// Load components function
async function loadComponents() {
    try {
        // Load Header
        const headerResponse = await fetch('components/header.html')
        const headerHTML = await headerResponse.text()
        document.getElementById('header-container').innerHTML = headerHTML
        
        // Load Sidebar
        const sidebarResponse = await fetch('components/sidebar.html')
        const sidebarHTML = await sidebarResponse.text()
        document.getElementById('sidebar-container').innerHTML = sidebarHTML
        
        // Load Footer
        const footerResponse = await fetch('components/footer.html')
        const footerHTML = await footerResponse.text()
        document.getElementById('footer-container').innerHTML = footerHTML
        
        console.log('✅ All components loaded successfully')
        
        // Initialize components after loading
        initializeComponents()
    } catch (error) {
        console.error('❌ Error loading components:', error)
    }
}

// Initialize components after they are loaded
function initializeComponents() {
    // Re-initialize Metronic components if needed
    if (typeof KTMenu !== 'undefined') {
        KTMenu.createInstances()
    }
    if (typeof KTDrawer !== 'undefined') {
        KTDrawer.createInstances()
    }
    if (typeof KTScroll !== 'undefined') {
        KTScroll.createInstances()
    }
}

// Call loadComponents when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    loadComponents()
    initializePage()
})

// DOM Elements
const logContainer = document.getElementById('log-container')
const statMessages = document.getElementById('stat-messages')
const statSessions = document.getElementById('stat-sessions')
const statErrors = document.getElementById('stat-errors')
const statTotal = document.getElementById('stat-total')
const searchInput = document.getElementById('search-input')
const filterButtons = document.querySelectorAll('.filter-btn')
const statsCards = document.querySelectorAll('.stats-card')
const exportLogsBtn = document.getElementById('export-logs-btn')
const clearLogsBtn = document.getElementById('clear-logs-btn')
const logLimit = document.getElementById('log-limit')

// Initialize Page
function initializePage() {
    setupEventListeners()
    loadLogsFromStorage()
    requestLiveData()
}

// Setup Event Listeners
function setupEventListeners() {
    // Filter buttons
    filterButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            filterButtons.forEach(b => b.classList.remove('active'))
            this.classList.add('active')
            currentFilter = this.dataset.filter
            applyFilters()
        })
    })
    
    // Stats cards filter
    statsCards.forEach(card => {
        card.addEventListener('click', function() {
            const filter = this.dataset.filter
            filterButtons.forEach(btn => {
                if (btn.dataset.filter === filter) {
                    btn.click()
                }
            })
        })
    })
    
    // Search input
    searchInput.addEventListener('input', function() {
        searchQuery = this.value.toLowerCase()
        applyFilters()
    })
    
    // Export button
    exportLogsBtn.addEventListener('click', exportLogs)
    
    // Clear button
    clearLogsBtn.addEventListener('click', clearAllLogs)
    
    // Log limit
    logLimit.addEventListener('change', renderLogs)
}

// Socket Event Handlers
socket.on('connect', () => {
    console.log('✅ Connected to server')
    addLog('system', 'Terhubung ke Server', 'Koneksi ke server WhatsApp berhasil dibuat')
})

socket.on('disconnect', () => {
    console.log('❌ Disconnected from server')
    addLog('error', 'Terputus dari Server', 'Koneksi ke server WhatsApp terputus')
})

socket.on('all-sessions', (sessions) => {
    stats.sessions = sessions.filter(s => s.isConnected).length
    updateStats()
})

socket.on('session-status', (data) => {
    if (data.isConnected) {
        addLog('session', 'Session Login', `Session "${data.sessionId}" berhasil login`, {
            sessionId: data.sessionId,
            user: data.user?.name || data.user?.id || 'Unknown'
        })
    } else {
        addLog('session', 'Session Logout', `Session "${data.sessionId}" telah logout`, {
            sessionId: data.sessionId
        })
    }
})

socket.on('message-sent', (data) => {
    addLog('message', 'Pesan Terkirim', `Pesan berhasil dikirim ke ${data.to}`, {
        sessionId: data.sessionId,
        recipient: data.to,
        messageId: data.messageId
    })
})

socket.on('message-received', (data) => {
    addLog('message', 'Pesan Diterima', `Pesan masuk dari ${data.from}`, {
        sessionId: data.sessionId,
        sender: data.from,
        preview: data.message?.substring(0, 50) + '...'
    })
})

socket.on('qr', (data) => {
    addLog('session', 'QR Code Dibuat', `QR Code untuk session "${data.sessionId}" telah dibuat`, {
        sessionId: data.sessionId
    })
})

socket.on('pairing-code', (data) => {
    addLog('session', 'Pairing Code Dibuat', `Pairing code untuk session "${data.sessionId}": ${data.code}`, {
        sessionId: data.sessionId,
        code: data.code
    })
})

socket.on('error', (error) => {
    addLog('error', 'Terjadi Kesalahan', error, {
        errorType: 'Socket Error'
    })
})

// Listen for custom API events
socket.on('api-request', (data) => {
    addLog('api', 'API Request', `${data.method} ${data.endpoint}`, {
        method: data.method,
        endpoint: data.endpoint,
        ip: data.ip
    })
})

// Add Log Entry
function addLog(type, title, description, metadata = {}) {
    const log = {
        id: Date.now() + Math.random(),
        type: type,
        title: title,
        description: description,
        metadata: metadata,
        timestamp: new Date().toISOString(),
        humanTime: formatHumanTime(new Date())
    }
    
    allLogs.unshift(log)
    
    // Update statistics
    if (type === 'message') stats.messages++
    if (type === 'error') stats.errors++
    stats.total++
    
    updateStats()
    saveLogsToStorage()
    applyFilters()
    
    // Save to database (async, don't wait)
    saveLogToDatabase(log)
}

// Save Log to Database via API
async function saveLogToDatabase(log) {
    console.log('📝 Attempting to save log to database:', log.title)
    
    const requestData = {
        log_type: log.type,
        title: log.title,
        description: log.description,
        session_id: log.metadata.sessionId || null,
        phone_number: log.metadata.recipient || log.metadata.sender || log.metadata.phone || null,
        metadata: log.metadata
    }
    
    console.log('📦 Request data:', requestData)
    console.log('🌐 API URL:', 'http://localhost/Baileys/api/logs/create.php')
    
    try {
        const response = await fetch('http://localhost/Baileys/api/logs/create.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(requestData)
        })
        
        console.log('📡 Response status:', response.status, response.statusText)
        
        if (!response.ok) {
            const errorText = await response.text()
            console.error('❌ HTTP Error:', response.status, errorText)
            return
        }
        
        const data = await response.json()
        console.log('📥 Response data:', data)
        
        if (data.success) {
            console.log('✅ Log saved to database successfully! ID:', data.data?.id)
        } else {
            console.error('❌ API returned error:', data.message, data.errors)
        }
    } catch (error) {
        console.error('❌ Error saving log to database:', error.message)
        console.error('Stack:', error.stack)
    }
}

// Apply Filters
function applyFilters() {
    filteredLogs = allLogs.filter(log => {
        // Filter by type
        if (currentFilter !== 'all' && log.type !== currentFilter) {
            return false
        }
        
        // Filter by search
        if (searchQuery) {
            const searchText = `${log.title} ${log.description}`.toLowerCase()
            if (!searchText.includes(searchQuery)) {
                return false
            }
        }
        
        return true
    })
    
    renderLogs()
}

// Render Logs
function renderLogs() {
    const limit = logLimit.value === 'all' ? filteredLogs.length : parseInt(logLimit.value)
    const logsToRender = filteredLogs.slice(0, limit)
    
    if (logsToRender.length === 0) {
        logContainer.innerHTML = `
            <div class="text-center py-10">
                <span class="svg-icon svg-icon-5tx svg-icon-muted mb-5">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path opacity="0.3" d="M19 22H5C4.4 22 4 21.6 4 21V3C4 2.4 4.4 2 5 2H14L20 8V21C20 21.6 19.6 22 19 22Z" fill="currentColor"/>
                        <path d="M15 8H20L14 2V7C14 7.6 14.4 8 15 8Z" fill="currentColor"/>
                    </svg>
                </span>
                <p class="text-muted fw-semibold fs-5">Tidak ada log yang sesuai dengan filter</p>
            </div>
        `
        return
    }
    
    logContainer.innerHTML = logsToRender.map(log => createLogHTML(log)).join('')
}

// Create Log HTML
function createLogHTML(log) {
    const iconConfig = getLogIconConfig(log.type)
    const metadataHTML = Object.keys(log.metadata).length > 0 
        ? `<div class="mt-2">
            ${Object.entries(log.metadata).map(([key, value]) => 
                `<span class="badge badge-light-info me-1 mb-1"><small>${formatMetadataKey(key)}: ${value}</small></span>`
            ).join('')}
           </div>`
        : ''
    
    return `
        <div class="log-item p-5 mb-3 rounded border border-gray-300" data-log-id="${log.id}" data-type="${log.type}">
            <div class="d-flex align-items-start">
                <div class="log-icon log-type-${log.type} me-4">
                    ${iconConfig.icon}
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h5 class="fw-bold mb-1">${log.title}</h5>
                            <p class="text-muted mb-0">${log.description}</p>
                            ${metadataHTML}
                        </div>
                        <div class="text-end">
                            <span class="badge badge-light-${iconConfig.color} mb-1">${iconConfig.label}</span>
                            <div class="text-muted fs-7">${log.humanTime}</div>
                            <div class="text-muted fs-8">${formatTime(log.timestamp)}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `
}

// Get Log Icon Config
function getLogIconConfig(type) {
    const configs = {
        message: {
            icon: '<i class="bi bi-chat-dots-fill fs-3"></i>',
            label: 'Pesan',
            color: 'primary'
        },
        session: {
            icon: '<i class="bi bi-person-check-fill fs-3"></i>',
            label: 'Session',
            color: 'success'
        },
        error: {
            icon: '<i class="bi bi-exclamation-triangle-fill fs-3"></i>',
            label: 'Error',
            color: 'danger'
        },
        system: {
            icon: '<i class="bi bi-gear-fill fs-3"></i>',
            label: 'System',
            color: 'warning'
        },
        api: {
            icon: '<i class="bi bi-lightning-charge-fill fs-3"></i>',
            label: 'API',
            color: 'info'
        }
    }
    
    return configs[type] || configs.system
}

// Format Metadata Key
function formatMetadataKey(key) {
    const keyMap = {
        sessionId: 'Session',
        user: 'User',
        recipient: 'Penerima',
        sender: 'Pengirim',
        messageId: 'ID Pesan',
        code: 'Kode',
        preview: 'Preview',
        method: 'Method',
        endpoint: 'Endpoint',
        ip: 'IP Address',
        errorType: 'Tipe Error'
    }
    
    return keyMap[key] || key
}

// Format Time
function formatTime(timestamp) {
    const date = new Date(timestamp)
    return date.toLocaleTimeString('id-ID', { 
        hour: '2-digit', 
        minute: '2-digit', 
        second: '2-digit' 
    })
}

// Format Human Time
function formatHumanTime(date) {
    const now = new Date()
    const diff = now - date
    
    const seconds = Math.floor(diff / 1000)
    const minutes = Math.floor(seconds / 60)
    const hours = Math.floor(minutes / 60)
    const days = Math.floor(hours / 24)
    
    if (seconds < 60) return 'Baru saja'
    if (minutes < 60) return `${minutes} menit yang lalu`
    if (hours < 24) return `${hours} jam yang lalu`
    if (days < 7) return `${days} hari yang lalu`
    
    return date.toLocaleDateString('id-ID', { 
        day: 'numeric', 
        month: 'short', 
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

// Update Statistics
function updateStats() {
    statMessages.textContent = stats.messages
    statSessions.textContent = stats.sessions
    statErrors.textContent = stats.errors
    statTotal.textContent = stats.total
}

// Save Logs to LocalStorage
function saveLogsToStorage() {
    try {
        // Keep only last 500 logs to prevent storage overflow
        const logsToSave = allLogs.slice(0, 500)
        localStorage.setItem('whatsapp_logs', JSON.stringify({
            logs: logsToSave,
            stats: stats,
            timestamp: new Date().toISOString()
        }))
    } catch (error) {
        console.error('Failed to save logs:', error)
    }
}

// Load Logs from LocalStorage
function loadLogsFromStorage() {
    try {
        const saved = localStorage.getItem('whatsapp_logs')
        if (saved) {
            const data = JSON.parse(saved)
            allLogs = data.logs || []
            stats = data.stats || { messages: 0, sessions: 0, errors: 0, total: 0 }
            updateStats()
            applyFilters()
            
            addLog('system', 'Log Dimuat', `${allLogs.length} log aktivitas sebelumnya berhasil dimuat dari LocalStorage`)
        }
        
        // Also load from database
        loadLogsFromDatabase()
    } catch (error) {
        console.error('Failed to load logs:', error)
    }
}

// Load Logs from Database
async function loadLogsFromDatabase() {
    try {
        const response = await fetch('http://localhost/Baileys/api/logs/get.php?log_type=all&limit=500&offset=0')
        const data = await response.json()
        
        if (data.success && data.data && data.data.logs && data.data.logs.length > 0) {
            // Merge database logs with local logs (avoid duplicates)
            const dbLogs = data.data.logs.map(log => {
                let metadata = {}
                try {
                    metadata = typeof log.metadata === 'string' ? JSON.parse(log.metadata) : (log.metadata || {})
                } catch (e) {
                    metadata = {}
                }
                
                return {
                    id: 'db_' + log.id,
                    type: log.log_type,
                    title: log.title,
                    description: log.description,
                    metadata: metadata,
                    timestamp: log.created_at,
                    humanTime: formatHumanTime(new Date(log.created_at))
                }
            })
            
            // Create a map of existing logs by timestamp + title for faster lookup
            const existingLogsMap = new Map()
            allLogs.forEach(log => {
                const key = `${log.title}_${new Date(log.timestamp).getTime()}`
                existingLogsMap.set(key, true)
            })
            
            // Only add logs that don't exist in local storage
            let newLogsAdded = 0
            dbLogs.forEach(dbLog => {
                const key = `${dbLog.title}_${new Date(dbLog.timestamp).getTime()}`
                if (!existingLogsMap.has(key)) {
                    allLogs.push(dbLog)
                    newLogsAdded++
                }
            })
            
            // Sort by timestamp (newest first)
            allLogs.sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp))
            
            // Recalculate stats
            recalculateStats()
            applyFilters()
            
            console.log(`✅ Loaded ${data.data.logs.length} logs from database (${newLogsAdded} new)`)
        } else {
            console.log('ℹ️ No logs found in database')
        }
    } catch (error) {
        console.error('❌ Error loading logs from database:', error)
    }
}

// Recalculate Statistics
function recalculateStats() {
    stats.messages = allLogs.filter(log => log.type === 'message').length
    stats.errors = allLogs.filter(log => log.type === 'error').length
    stats.total = allLogs.length
    updateStats()
}

// Export Logs
function exportLogs() {
    // Export from database via API
    const format = confirm('Export sebagai CSV?\n\nOK = CSV\nCancel = JSON') ? 'csv' : 'json'
    
    const exportUrl = `../api/logs/export.php?format=${format}&log_type=${currentFilter}`
    
    // Open in new window to trigger download
    window.open(exportUrl, '_blank')
    
    addLog('system', 'Log Diekspor', `Log telah diekspor dalam format ${format.toUpperCase()}`)
}

// Clear All Logs
async function clearAllLogs() {
    if (confirm('Apakah Anda yakin ingin menghapus semua log aktivitas?\n\nLog di database juga akan dihapus!\n\nTindakan ini tidak dapat dibatalkan!')) {
        try {
            // Clear from database
            const response = await fetch('http://localhost/Baileys/api/logs/clear.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'all'
                })
            })
            
            const data = await response.json()
            
            if (data.success) {
                // Clear local storage
                allLogs = []
                stats = { messages: 0, sessions: 0, errors: 0, total: 0 }
                updateStats()
                saveLogsToStorage()
                applyFilters()
                
                setTimeout(() => {
                    addLog('system', 'Log Dibersihkan', `${data.data.deleted_count || 0} log aktivitas telah dihapus dari database`)
                }, 100)
            } else {
                alert('Gagal menghapus log dari database: ' + data.message)
            }
        } catch (error) {
            console.error('❌ Error clearing logs:', error)
            alert('Terjadi kesalahan saat menghapus log')
        }
    }
}

// Request Live Data
function requestLiveData() {
    socket.emit('get-sessions')
}

// Auto-update human time every minute
setInterval(() => {
    allLogs = allLogs.map(log => ({
        ...log,
        humanTime: formatHumanTime(new Date(log.timestamp))
    }))
    renderLogs()
}, 60000)

console.log('✅ Log Activity page initialized')
