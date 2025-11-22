<?php
/**
 * Create Activity Log
 * POST /api/logs/create.php
 */

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/database.php';

// Only allow POST
if (!Request::isPost()) {
    Response::error('Method not allowed', 405);
}

// Get request body
$data = Request::getBody();

// Validation rules
$rules = [
    'log_type' => ['required' => true, 'in' => ['message', 'session', 'error', 'system', 'api']],
    'title' => ['required' => true, 'max' => 255],
    'description' => ['required' => true]
];

$errors = Validator::validate($data, $rules);

if (!empty($errors)) {
    Response::error('Validation failed', 400, $errors);
}

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    // Prepare insert query
    $query = "INSERT INTO activity_logs 
              (log_type, title, description, session_id, phone_number, metadata, ip_address, user_agent) 
              VALUES 
              (:log_type, :title, :description, :session_id, :phone_number, :metadata, :ip_address, :user_agent)";
    
    $stmt = $conn->prepare($query);
    
    // Bind parameters
    $stmt->bindParam(':log_type', $data['log_type']);
    $stmt->bindParam(':title', $data['title']);
    $stmt->bindParam(':description', $data['description']);
    
    $session_id = isset($data['session_id']) ? $data['session_id'] : null;
    $phone_number = isset($data['phone_number']) ? $data['phone_number'] : null;
    $metadata = isset($data['metadata']) ? json_encode($data['metadata']) : null;
    
    $stmt->bindParam(':session_id', $session_id);
    $stmt->bindParam(':phone_number', $phone_number);
    $stmt->bindParam(':metadata', $metadata);
    
    $ip_address = Request::getClientIP();
    $user_agent = Request::getUserAgent();
    
    $stmt->bindParam(':ip_address', $ip_address);
    $stmt->bindParam(':user_agent', $user_agent);
    
    // Execute query
    if ($stmt->execute()) {
        $log_id = $conn->lastInsertId();
        
        // Update daily statistics
        updateDailyStats($conn);
        
        Response::success('Log created successfully', [
            'id' => $log_id,
            'log_type' => $data['log_type'],
            'title' => $data['title']
        ], 201);
    } else {
        Response::error('Failed to create log', 500);
    }
    
} catch (Exception $e) {
    Response::error('Server error: ' . $e->getMessage(), 500);
}

/**
 * Update daily statistics
 */
function updateDailyStats($conn) {
    try {
        $query = "INSERT INTO log_statistics 
                  (stat_date, total_messages, total_sessions, total_errors, total_api_calls, total_activities)
                  SELECT 
                    CURDATE(),
                    SUM(CASE WHEN log_type = 'message' THEN 1 ELSE 0 END),
                    COUNT(DISTINCT CASE WHEN log_type = 'session' THEN session_id END),
                    SUM(CASE WHEN log_type = 'error' THEN 1 ELSE 0 END),
                    SUM(CASE WHEN log_type = 'api' THEN 1 ELSE 0 END),
                    COUNT(*)
                  FROM activity_logs
                  WHERE DATE(created_at) = CURDATE()
                  ON DUPLICATE KEY UPDATE
                    total_messages = VALUES(total_messages),
                    total_sessions = VALUES(total_sessions),
                    total_errors = VALUES(total_errors),
                    total_api_calls = VALUES(total_api_calls),
                    total_activities = VALUES(total_activities)";
        
        $stmt = $conn->prepare($query);
        $stmt->execute();
    } catch (Exception $e) {
        // Silent fail - stats update is not critical
    }
}
?>
