<?php
/**
 * Export Logs
 * GET /api/logs/export.php
 */

header('Access-Control-Allow-Origin: *');
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
    
    $format = Request::getQuery('format', 'csv'); // csv, json
    $log_type = Request::getQuery('log_type', 'all');
    $date_from = Request::getQuery('date_from');
    $date_to = Request::getQuery('date_to');
    
    // Build query
    $query = "SELECT 
                id,
                log_type,
                title,
                description,
                session_id,
                phone_number,
                metadata,
                ip_address,
                created_at
              FROM activity_logs WHERE 1=1";
    
    $params = [];
    
    if ($log_type !== 'all') {
        $query .= " AND log_type = :log_type";
        $params[':log_type'] = $log_type;
    }
    
    if ($date_from) {
        $query .= " AND DATE(created_at) >= :date_from";
        $params[':date_from'] = $date_from;
    }
    
    if ($date_to) {
        $query .= " AND DATE(created_at) <= :date_to";
        $params[':date_to'] = $date_to;
    }
    
    $query .= " ORDER BY created_at DESC";
    
    $stmt = $conn->prepare($query);
    
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->execute();
    $logs = $stmt->fetchAll();
    
    // Export based on format
    if ($format === 'csv') {
        exportCSV($logs);
    } else {
        exportJSON($logs);
    }
    
} catch (Exception $e) {
    Response::error('Server error: ' . $e->getMessage(), 500);
}

/**
 * Export as CSV
 */
function exportCSV($logs) {
    $filename = 'whatsapp_logs_' . date('Y-m-d_His') . '.csv';
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    // Add BOM for UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // CSV Header
    fputcsv($output, ['ID', 'Type', 'Title', 'Description', 'Session ID', 'Phone', 'IP Address', 'Created At']);
    
    // CSV Rows
    foreach ($logs as $log) {
        fputcsv($output, [
            $log['id'],
            $log['log_type'],
            $log['title'],
            $log['description'],
            $log['session_id'],
            $log['phone_number'],
            $log['ip_address'],
            $log['created_at']
        ]);
    }
    
    fclose($output);
    exit;
}

/**
 * Export as JSON
 */
function exportJSON($logs) {
    $filename = 'whatsapp_logs_' . date('Y-m-d_His') . '.json';
    
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Decode metadata
    foreach ($logs as &$log) {
        if ($log['metadata']) {
            $log['metadata'] = json_decode($log['metadata'], true);
        }
    }
    
    $exportData = [
        'exported_at' => date('Y-m-d H:i:s'),
        'total_records' => count($logs),
        'logs' => $logs
    ];
    
    echo json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}
?>
