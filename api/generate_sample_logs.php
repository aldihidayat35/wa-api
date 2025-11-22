<?php
/**
 * Generate Sample Logs for Testing
 * Access: http://localhost/Baileys/api/generate_sample_logs.php
 */

header('Content-Type: application/json');
require_once 'config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    // Sample data
    $samples = [
        // Messages
        [
            'log_type' => 'message',
            'title' => 'Pesan Terkirim',
            'description' => 'Berhasil mengirim pesan ke 628123456789',
            'session_id' => 'session1',
            'phone_number' => '628123456789',
            'metadata' => json_encode(['messageId' => 'msg001', 'type' => 'text'])
        ],
        [
            'log_type' => 'message',
            'title' => 'Pesan Terkirim',
            'description' => 'Berhasil mengirim gambar ke 628987654321',
            'session_id' => 'session1',
            'phone_number' => '628987654321',
            'metadata' => json_encode(['messageId' => 'msg002', 'type' => 'image'])
        ],
        [
            'log_type' => 'message',
            'title' => 'Pesan Bulk Terkirim',
            'description' => 'Mengirim pesan ke 5 kontak (batch 1/3)',
            'session_id' => 'session2',
            'phone_number' => null,
            'metadata' => json_encode(['batchId' => 'batch001', 'totalContacts' => 15])
        ],
        
        // Sessions
        [
            'log_type' => 'session',
            'title' => 'Session Connected',
            'description' => 'Session session1 berhasil terhubung',
            'session_id' => 'session1',
            'phone_number' => null,
            'metadata' => json_encode(['platform' => 'WhatsApp Web', 'status' => 'connected'])
        ],
        [
            'log_type' => 'session',
            'title' => 'Session Disconnected',
            'description' => 'Session session2 terputus karena logout',
            'session_id' => 'session2',
            'phone_number' => null,
            'metadata' => json_encode(['reason' => 'user_logout'])
        ],
        [
            'log_type' => 'session',
            'title' => 'QR Code Generated',
            'description' => 'QR Code dibuat untuk session3',
            'session_id' => 'session3',
            'phone_number' => null,
            'metadata' => json_encode(['qrVersion' => 1])
        ],
        
        // Errors
        [
            'log_type' => 'error',
            'title' => 'Gagal Mengirim Pesan',
            'description' => 'Nomor 628111222333 tidak terdaftar di WhatsApp',
            'session_id' => 'session1',
            'phone_number' => '628111222333',
            'metadata' => json_encode(['errorCode' => 404, 'errorType' => 'invalid_number'])
        ],
        [
            'log_type' => 'error',
            'title' => 'Connection Timeout',
            'description' => 'Session session3 timeout saat mencoba koneksi',
            'session_id' => 'session3',
            'phone_number' => null,
            'metadata' => json_encode(['errorCode' => 408, 'errorType' => 'timeout'])
        ],
        [
            'log_type' => 'error',
            'title' => 'Rate Limit Exceeded',
            'description' => 'Terlalu banyak pesan dalam waktu singkat',
            'session_id' => 'session1',
            'phone_number' => null,
            'metadata' => json_encode(['errorCode' => 429, 'retryAfter' => 60])
        ],
        
        // System
        [
            'log_type' => 'system',
            'title' => 'Server Started',
            'description' => 'WhatsApp Bot Server dimulai pada port 3000',
            'session_id' => null,
            'phone_number' => null,
            'metadata' => json_encode(['port' => 3000, 'env' => 'production'])
        ],
        [
            'log_type' => 'system',
            'title' => 'Database Backup',
            'description' => 'Backup database berhasil dibuat',
            'session_id' => null,
            'phone_number' => null,
            'metadata' => json_encode(['backupSize' => '2.5MB', 'filename' => 'backup_20250123.sql'])
        ],
        [
            'log_type' => 'system',
            'title' => 'Auto-Cleanup Executed',
            'description' => 'Menghapus 150 log yang lebih lama dari 90 hari',
            'session_id' => null,
            'phone_number' => null,
            'metadata' => json_encode(['deletedCount' => 150, 'daysOld' => 90])
        ],
        
        // API
        [
            'log_type' => 'api',
            'title' => 'API Request Received',
            'description' => 'GET /api/sessions/list - 200 OK',
            'session_id' => null,
            'phone_number' => null,
            'metadata' => json_encode(['method' => 'GET', 'endpoint' => '/api/sessions/list', 'statusCode' => 200])
        ],
        [
            'log_type' => 'api',
            'title' => 'API Request Failed',
            'description' => 'POST /api/messages/send - 400 Bad Request',
            'session_id' => 'session1',
            'phone_number' => null,
            'metadata' => json_encode(['method' => 'POST', 'endpoint' => '/api/messages/send', 'statusCode' => 400, 'error' => 'Missing recipient'])
        ],
        [
            'log_type' => 'api',
            'title' => 'Webhook Delivered',
            'description' => 'Webhook dikirim ke https://example.com/webhook',
            'session_id' => 'session1',
            'phone_number' => null,
            'metadata' => json_encode(['url' => 'https://example.com/webhook', 'statusCode' => 200, 'responseTime' => '120ms'])
        ],
    ];
    
    // Insert sample logs
    $query = "INSERT INTO activity_logs 
              (log_type, title, description, session_id, phone_number, metadata, ip_address, user_agent) 
              VALUES 
              (:log_type, :title, :description, :session_id, :phone_number, :metadata, :ip_address, :user_agent)";
    
    $stmt = $conn->prepare($query);
    
    $inserted = 0;
    $ip_address = Request::getClientIP();
    $user_agent = 'Sample Data Generator';
    
    foreach ($samples as $log) {
        try {
            $stmt->bindParam(':log_type', $log['log_type']);
            $stmt->bindParam(':title', $log['title']);
            $stmt->bindParam(':description', $log['description']);
            $stmt->bindParam(':session_id', $log['session_id']);
            $stmt->bindParam(':phone_number', $log['phone_number']);
            $stmt->bindParam(':metadata', $log['metadata']);
            $stmt->bindParam(':ip_address', $ip_address);
            $stmt->bindParam(':user_agent', $user_agent);
            
            if ($stmt->execute()) {
                $inserted++;
            }
        } catch (Exception $e) {
            // Continue on error
        }
    }
    
    // Update statistics
    $updateQuery = "CALL UpdateDailyStats()";
    try {
        $conn->exec($updateQuery);
    } catch (Exception $e) {
        // Stored procedure might not exist yet
    }
    
    Response::success("Sample logs generated successfully", [
        'inserted' => $inserted,
        'total_samples' => count($samples),
        'message' => "Generated $inserted sample logs for testing"
    ]);
    
} catch (Exception $e) {
    Response::error('Failed to generate sample logs: ' . $e->getMessage(), 500);
}
?>
