// Initialize Socket.IO
const socket = io()

// State
let allSessions = []
let stats = {
    total: 0,
    connected: 0,
    messagesSent: 0,
    successRate: 0
}

// Load components
async function loadComponents() {
    try {
        const headerResponse = await fetch('components/header.html')
        const headerHTML = await headerResponse.text()
        document.getElementById('header-container').innerHTML = headerHTML
        
        const sidebarResponse = await fetch('components/sidebar.html')
        const sidebarHTML = await sidebarResponse.text()
        document.getElementById('sidebar-container').innerHTML = sidebarHTML
        
        const footerResponse = await fetch('components/footer.html')
        const footerHTML = await footerResponse.text()
        document.getElementById('footer-container').innerHTML = footerHTML
        
        console.log('✅ Components loaded')
        
        initializeComponents()
    } catch (error) {
        console.error('❌ Error loading components:', error)
    }
}

function initializeComponents() {
    if (typeof KTMenu !== 'undefined') KTMenu.createInstances()
    if (typeof KTDrawer !== 'undefined') KTDrawer.createInstances()
    if (typeof KTScroll !== 'undefined') KTScroll.createInstances()
}

// Socket Events
socket.on('connect', () => {
    console.log('🔌 Connected to server')
    socket.emit('get-sessions')
    loadActivityLogs()
})

socket.on('all-sessions', (sessions) => {
    console.log('📋 Received sessions:', sessions)
    allSessions = sessions
    updateStats()
    renderActiveSessions()
})

socket.on('session-status', (data) => {
    console.log('🔄 Session status update:', data)
    socket.emit('get-sessions')
})

// Update Statistics
function updateStats() {
    stats.total = allSessions.length
    stats.connected = allSessions.filter(s => s.isConnected).length
    
    // Update DOM
    document.getElementById('stat-total-sessions').textContent = stats.total
    document.getElementById('stat-connected-sessions').textContent = stats.connected
    
    // Load message stats from API
    loadMessageStats()
}

// Load Message Statistics from Database
async function loadMessageStats() {
    try {
        const response = await fetch('http://localhost/Baileys/api/logs/get.php?log_type=message&limit=1000')
        const data = await response.json()
        
        if (data.success && data.data.logs) {
            const logs = data.data.logs
            
            // Count today's messages
            const today = new Date().toDateString()
            const todayMessages = logs.filter(log => {
                const logDate = new Date(log.created_at).toDateString()
                return logDate === today
            })
            
            stats.messagesSent = todayMessages.length
            document.getElementById('stat-messages-sent').textContent = stats.messagesSent
            
            // Calculate success rate
            const errorResponse = await fetch('http://localhost/Baileys/api/logs/get.php?log_type=error&limit=1000')
            const errorData = await errorResponse.json()
            
            if (errorData.success && errorData.data.logs) {
                const errors = errorData.data.logs
                const todayErrors = errors.filter(log => {
                    const logDate = new Date(log.created_at).toDateString()
                    return logDate === today
                })
                
                const totalAttempts = stats.messagesSent + todayErrors.length
                if (totalAttempts > 0) {
                    stats.successRate = Math.round((stats.messagesSent / totalAttempts) * 100)
                } else {
                    stats.successRate = 100
                }
                
                document.getElementById('stat-success-rate').textContent = stats.successRate + '%'
            }
        }
    } catch (error) {
        console.error('Error loading message stats:', error)
    }
}

// Render Active Sessions
function renderActiveSessions() {
    const container = document.getElementById('active-sessions-list')
    
    if (allSessions.length === 0) {
        container.innerHTML = `
            <div class="text-center py-10">
                <i class="bi bi-inbox fs-5x text-muted mb-5"></i>
                <h3 class="text-muted">Belum ada session</h3>
                <p class="text-gray-600 mb-5">Buat session baru untuk memulai</p>
                <a href="manage-sessions.html" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Buat Session
                </a>
            </div>
        `
        return
    }
    
    container.innerHTML = allSessions.map(session => {
        const isConnected = session.isConnected
        const statusClass = isConnected ? 'success' : 'danger'
        const statusText = isConnected ? 'Connected' : 'Disconnected'
        const statusIcon = isConnected ? 'check-circle' : 'x-circle'
        const userName = session.user?.name || session.user?.id?.split(':')[0] || 'Unknown'
        const phoneNumber = session.user?.id?.split(':')[0] || '-'
        
        return `
            <div class="d-flex align-items-center bg-light-${statusClass} rounded p-5 mb-5">
                <div class="symbol symbol-50px me-5">
                    <span class="symbol-label bg-white">
                        <i class="bi bi-whatsapp fs-2x text-success"></i>
                    </span>
                </div>
                <div class="flex-grow-1">
                    <div class="fw-bold text-gray-800 fs-6">${session.id}</div>
                    <div class="text-muted fs-7">
                        ${isConnected ? `<i class="bi bi-person"></i> ${userName} • <i class="bi bi-telephone"></i> ${phoneNumber}` : 'Not connected'}
                    </div>
                </div>
                <div class="text-end">
                    <span class="badge badge-${statusClass} badge-lg">
                        <i class="bi bi-${statusIcon}"></i> ${statusText}
                    </span>
                </div>
            </div>
        `
    }).join('')
}

// Load Recent Activity
async function loadActivityLogs() {
    try {
        const response = await fetch('http://localhost/Baileys/api/logs/get.php?limit=10')
        const data = await response.json()
        
        if (data.success && data.data.logs) {
            renderRecentActivity(data.data.logs)
        }
    } catch (error) {
        console.error('Error loading activity logs:', error)
    }
}

// Render Recent Activity
function renderRecentActivity(logs) {
    const container = document.getElementById('recent-activity-list')
    
    if (logs.length === 0) {
        container.innerHTML = `
            <div class="text-center py-10">
                <i class="bi bi-clock-history fs-3x text-muted mb-3"></i>
                <p class="text-muted">Belum ada aktivitas</p>
            </div>
        `
        return
    }
    
    container.innerHTML = `
        <div class="table-responsive">
            <table class="table table-row-bordered table-row-dashed gy-4 align-middle fw-bold">
                <thead class="fs-7 text-gray-400 text-uppercase">
                    <tr>
                        <th class="min-w-250px">Activity</th>
                        <th class="min-w-150px">Type</th>
                        <th class="min-w-150px">Session</th>
                        <th class="min-w-150px">Time</th>
                    </tr>
                </thead>
                <tbody class="fs-6">
                    ${logs.map(log => {
                        const typeClass = log.log_type === 'error' ? 'danger' : (log.log_type === 'message' ? 'success' : 'info')
                        const typeIcon = log.log_type === 'error' ? 'exclamation-circle' : (log.log_type === 'message' ? 'send-check' : 'info-circle')
                        const time = new Date(log.created_at).toLocaleString('id-ID')
                        
                        return `
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-${typeIcon} fs-2 text-${typeClass} me-3"></i>
                                        <div>
                                            <div class="text-gray-800">${log.title}</div>
                                            <div class="text-muted fs-7">${log.description}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-light-${typeClass}">${log.log_type}</span>
                                </td>
                                <td class="text-gray-600">${log.session_id || '-'}</td>
                                <td class="text-gray-600">${time}</td>
                            </tr>
                        `
                    }).join('')}
                </tbody>
            </table>
        </div>
    `
}

// Auto refresh every 30 seconds
setInterval(() => {
    socket.emit('get-sessions')
    loadActivityLogs()
}, 30000)

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    loadComponents()
})

console.log('✅ Dashboard initialized')
