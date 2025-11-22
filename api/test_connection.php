<?php
/**
 * Test Database Connection
 * Access: http://localhost/Baileys/api/test_connection.php
 */

header('Content-Type: application/json');
require_once 'config/database.php';

// Test connection
$result = Database::testConnection();

// Format output
?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Connection Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .result {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .success {
            color: #10b981;
            font-size: 24px;
            font-weight: bold;
        }
        .error {
            color: #ef4444;
            font-size: 24px;
            font-weight: bold;
        }
        .info {
            margin-top: 20px;
            padding: 15px;
            background: #f3f4f6;
            border-radius: 5px;
        }
        .info h3 {
            margin-top: 0;
        }
        .code {
            background: #1f2937;
            color: #10b981;
            padding: 10px;
            border-radius: 5px;
            font-family: monospace;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="result">
        <h1>🗄️ Database Connection Test</h1>
        
        <?php if ($result['success']): ?>
            <p class="success">✅ <?php echo $result['message']; ?></p>
            
            <div class="info">
                <h3>📊 Connection Details:</h3>
                <p><strong>Database:</strong> <?php echo $result['data']['database']; ?></p>
                <p><strong>Host:</strong> <?php echo $result['data']['host']; ?></p>
                <p><strong>PDO Driver:</strong> <?php echo $result['data']['driver']; ?></p>
                <p><strong>Server Version:</strong> <?php echo $result['data']['server_version']; ?></p>
            </div>
            
            <div class="info">
                <h3>✅ Next Steps:</h3>
                <ol>
                    <li>Database connection is working!</li>
                    <li>You can now use the API endpoints</li>
                    <li>Open <code>log-activity.html</code> to test logging</li>
                    <li>Check the dashboard for statistics</li>
                </ol>
            </div>
            
        <?php else: ?>
            <p class="error">❌ <?php echo $result['message']; ?></p>
            
            <div class="info">
                <h3>🔧 Troubleshooting:</h3>
                <ol>
                    <li>Make sure MySQL service is running</li>
                    <li>Check database credentials in <code>api/config/database.php</code></li>
                    <li>Import <code>sql/create_database.sql</code> file</li>
                    <li>Verify database name is <code>whatsapp_logs</code></li>
                </ol>
                
                <h3>🗄️ Import Database:</h3>
                <div class="code">
                    mysql -u root -p &lt; sql/create_database.sql
                </div>
                
                <h3>📝 Error Details:</h3>
                <div class="code">
                    <?php echo isset($result['data']) ? $result['data'] : 'No additional details'; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="info">
            <h3>📚 Documentation:</h3>
            <p>Read <code>DATABASE_SETUP.md</code> for complete setup instructions</p>
        </div>
    </div>
</body>
</html>
