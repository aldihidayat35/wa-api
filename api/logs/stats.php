<?php
/**
 * Get Statistics
 * GET /api/logs/stats.php
 */

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/database.php';

// Only allow GET
if (!Request::isGet()) {
    Response::error('Method not allowed', 405);
}

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    $period = Request::getQuery('period', 'today'); // today, week, month, all
    
    // Get overall statistics
    $statsQuery = "SELECT 
                    COUNT(*) as total_activities,
                    SUM(CASE WHEN log_type = 'message' THEN 1 ELSE 0 END) as total_messages,
                    SUM(CASE WHEN log_type = 'error' THEN 1 ELSE 0 END) as total_errors,
                    COUNT(DISTINCT session_id) as active_sessions
                   FROM activity_logs";
    
    // Add date filter
    switch ($period) {
        case 'today':
            $statsQuery .= " WHERE DATE(created_at) = CURDATE()";
            break;
        case 'week':
            $statsQuery .= " WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            break;
        case 'month':
            $statsQuery .= " WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            break;
    }
    
    $stmt = $conn->prepare($statsQuery);
    $stmt->execute();
    $stats = $stmt->fetch();
    
    // Get activity by type
    $typeQuery = "SELECT 
                    log_type,
                    COUNT(*) as count
                  FROM activity_logs";
    
    if ($period !== 'all') {
        $typeQuery .= " WHERE " . substr(strstr($statsQuery, 'WHERE'), 6);
    }
    
    $typeQuery .= " GROUP BY log_type";
    
    $typeStmt = $conn->prepare($typeQuery);
    $typeStmt->execute();
    $activityByType = $typeStmt->fetchAll();
    
    // Get daily activity for chart (last 7 days)
    $chartQuery = "SELECT 
                    DATE(created_at) as date,
                    log_type,
                    COUNT(*) as count
                   FROM activity_logs
                   WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                   GROUP BY DATE(created_at), log_type
                   ORDER BY date ASC";
    
    $chartStmt = $conn->prepare($chartQuery);
    $chartStmt->execute();
    $dailyActivity = $chartStmt->fetchAll();
    
    // Get top sessions
    $sessionQuery = "SELECT 
                        session_id,
                        COUNT(*) as activity_count,
                        MAX(created_at) as last_activity
                     FROM activity_logs
                     WHERE session_id IS NOT NULL";
    
    if ($period !== 'all') {
        $sessionQuery .= " AND " . substr(strstr($statsQuery, 'WHERE'), 6);
    }
    
    $sessionQuery .= " GROUP BY session_id ORDER BY activity_count DESC LIMIT 5";
    
    $sessionStmt = $conn->prepare($sessionQuery);
    $sessionStmt->execute();
    $topSessions = $sessionStmt->fetchAll();
    
    Response::success('Statistics retrieved successfully', [
        'period' => $period,
        'overall' => [
            'total_activities' => (int)$stats['total_activities'],
            'total_messages' => (int)$stats['total_messages'],
            'total_errors' => (int)$stats['total_errors'],
            'active_sessions' => (int)$stats['active_sessions']
        ],
        'activity_by_type' => $activityByType,
        'daily_activity' => $dailyActivity,
        'top_sessions' => $topSessions
    ]);
    
} catch (Exception $e) {
    Response::error('Server error: ' . $e->getMessage(), 500);
}
?>
