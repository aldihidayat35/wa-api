<?php
/**
 * Clear Old Logs
 * DELETE /api/logs/clear.php
 */

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: DELETE, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/database.php';

// Allow DELETE or POST
if (!Request::isDelete() && !Request::isPost()) {
    Response::error('Method not allowed', 405);
}

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    $data = Request::getBody();
    
    $action = isset($data['action']) ? $data['action'] : 'old'; // 'old', 'all', 'by_type'
    
    switch ($action) {
        case 'all':
            // Clear all logs
            $query = "DELETE FROM activity_logs";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $deleted = $stmt->rowCount();
            
            // Reset statistics
            $conn->prepare("DELETE FROM log_statistics")->execute();
            
            $message = "All logs cleared successfully";
            break;
            
        case 'old':
            // Clear logs older than X days (default 90)
            $days = isset($data['days']) ? (int)$data['days'] : 90;
            
            $query = "DELETE FROM activity_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':days', $days, PDO::PARAM_INT);
            $stmt->execute();
            $deleted = $stmt->rowCount();
            
            $message = "Deleted logs older than $days days";
            break;
            
        case 'by_type':
            // Clear logs by type
            $log_type = isset($data['log_type']) ? $data['log_type'] : null;
            
            if (!$log_type) {
                Response::error('log_type is required for by_type action', 400);
            }
            
            $query = "DELETE FROM activity_logs WHERE log_type = :log_type";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':log_type', $log_type);
            $stmt->execute();
            $deleted = $stmt->rowCount();
            
            $message = "Deleted all '$log_type' logs";
            break;
            
        default:
            Response::error('Invalid action. Use: old, all, or by_type', 400);
    }
    
    Response::success($message, [
        'deleted_count' => $deleted,
        'action' => $action
    ]);
    
} catch (Exception $e) {
    Response::error('Server error: ' . $e->getMessage(), 500);
}
?>
