<?php
/**
 * Get Activity Logs
 * GET /api/logs/get.php
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
    
    // Get query parameters
    $log_type = Request::getQuery('log_type', 'all');
    $session_id = Request::getQuery('session_id');
    $search = Request::getQuery('search');
    $limit = Request::getQuery('limit', 100);
    $offset = Request::getQuery('offset', 0);
    $date_from = Request::getQuery('date_from');
    $date_to = Request::getQuery('date_to');
    
    // Build query
    $query = "SELECT * FROM activity_logs WHERE 1=1";
    $params = [];
    
    // Filter by log type
    if ($log_type !== 'all') {
        $query .= " AND log_type = :log_type";
        $params[':log_type'] = $log_type;
    }
    
    // Filter by session
    if ($session_id) {
        $query .= " AND session_id = :session_id";
        $params[':session_id'] = $session_id;
    }
    
    // Search in title and description
    if ($search) {
        $query .= " AND (title LIKE :search OR description LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }
    
    // Date range filter
    if ($date_from) {
        $query .= " AND DATE(created_at) >= :date_from";
        $params[':date_from'] = $date_from;
    }
    
    if ($date_to) {
        $query .= " AND DATE(created_at) <= :date_to";
        $params[':date_to'] = $date_to;
    }
    
    // Order and limit
    $query .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
    
    $stmt = $conn->prepare($query);
    
    // Bind parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $logs = $stmt->fetchAll();
    
    // Decode JSON metadata
    foreach ($logs as &$log) {
        if ($log['metadata']) {
            $log['metadata'] = json_decode($log['metadata'], true);
        }
    }
    
    // Get total count
    $countQuery = "SELECT COUNT(*) as total FROM activity_logs WHERE 1=1";
    foreach ($params as $key => $value) {
        if (strpos($key, 'limit') === false && strpos($key, 'offset') === false) {
            $countQuery .= str_replace('1=1 AND', '', strstr($query, $key));
            break;
        }
    }
    
    $countStmt = $conn->prepare("SELECT COUNT(*) as total FROM activity_logs WHERE 1=1" . 
        ($log_type !== 'all' ? " AND log_type = :log_type" : "") .
        ($session_id ? " AND session_id = :session_id" : "") .
        ($search ? " AND (title LIKE :search OR description LIKE :search)" : "") .
        ($date_from ? " AND DATE(created_at) >= :date_from" : "") .
        ($date_to ? " AND DATE(created_at) <= :date_to" : "")
    );
    
    foreach ($params as $key => $value) {
        if (strpos($key, 'limit') === false && strpos($key, 'offset') === false) {
            $countStmt->bindValue($key, $value);
        }
    }
    
    $countStmt->execute();
    $total = $countStmt->fetch()['total'];
    
    Response::success('Logs retrieved successfully', [
        'logs' => $logs,
        'pagination' => [
            'total' => (int)$total,
            'limit' => (int)$limit,
            'offset' => (int)$offset,
            'has_more' => ($offset + $limit) < $total
        ]
    ]);
    
} catch (Exception $e) {
    Response::error('Server error: ' . $e->getMessage(), 500);
}
?>
