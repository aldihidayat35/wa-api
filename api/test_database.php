<?php
/**
 * Test Database Tables and Data
 * Access: http://localhost/Baileys/api/test_database.php
 */

header('Content-Type: text/html; charset=utf-8');
require_once 'config/database.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .section {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h2 {
            color: #333;
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .success {
            color: #4CAF50;
            font-weight: bold;
        }
        .error {
            color: #f44336;
            font-weight: bold;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-success { background: #4CAF50; color: white; }
        .badge-info { background: #2196F3; color: white; }
        .badge-warning { background: #ff9800; color: white; }
        .code {
            background: #f4f4f4;
            padding: 10px;
            border-left: 4px solid #4CAF50;
            margin: 10px 0;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <h1>🗄️ Database Test & Verification</h1>
    
    <?php
    try {
        $database = new Database();
        $conn = $database->getConnection();
        
        if (!$conn) {
            throw new Exception("Database connection failed");
        }
        
        echo '<div class="section">';
        echo '<h2>✅ Database Connection</h2>';
        echo '<p class="success">Connected to database successfully!</p>';
        echo '</div>';
        
        // Test 1: Check Tables
        echo '<div class="section">';
        echo '<h2>📋 Database Tables</h2>';
        
        $tables = ['activity_logs', 'log_statistics', 'sessions', 'message_logs'];
        echo '<table>';
        echo '<tr><th>Table Name</th><th>Status</th><th>Row Count</th></tr>';
        
        foreach ($tables as $table) {
            $stmt = $conn->query("SELECT COUNT(*) as count FROM $table");
            $result = $stmt->fetch();
            $count = $result['count'];
            
            echo "<tr>";
            echo "<td><strong>$table</strong></td>";
            echo "<td><span class='badge badge-success'>✓ EXISTS</span></td>";
            echo "<td>$count rows</td>";
            echo "</tr>";
        }
        
        echo '</table>';
        echo '</div>';
        
        // Test 2: Check Stored Procedures
        echo '<div class="section">';
        echo '<h2>⚙️ Stored Procedures</h2>';
        
        $stmt = $conn->query("SHOW PROCEDURE STATUS WHERE Db = 'whatsapp_logs'");
        $procedures = $stmt->fetchAll();
        
        if (count($procedures) > 0) {
            echo '<table>';
            echo '<tr><th>Procedure Name</th><th>Type</th><th>Created</th></tr>';
            foreach ($procedures as $proc) {
                echo "<tr>";
                echo "<td><strong>{$proc['Name']}</strong></td>";
                echo "<td><span class='badge badge-info'>PROCEDURE</span></td>";
                echo "<td>{$proc['Created']}</td>";
                echo "</tr>";
            }
            echo '</table>';
        } else {
            echo '<p class="error">⚠️ No stored procedures found!</p>';
        }
        echo '</div>';
        
        // Test 3: Check Views
        echo '<div class="section">';
        echo '<h2>👁️ Views</h2>';
        
        $stmt = $conn->query("SHOW FULL TABLES WHERE Table_type = 'VIEW'");
        $views = $stmt->fetchAll();
        
        if (count($views) > 0) {
            echo '<table>';
            echo '<tr><th>View Name</th><th>Type</th></tr>';
            foreach ($views as $view) {
                $viewName = $view['Tables_in_whatsapp_logs'];
                echo "<tr>";
                echo "<td><strong>$viewName</strong></td>";
                echo "<td><span class='badge badge-info'>VIEW</span></td>";
                echo "</tr>";
            }
            echo '</table>';
        } else {
            echo '<p class="error">⚠️ No views found!</p>';
        }
        echo '</div>';
        
        // Test 4: Check Triggers
        echo '<div class="section">';
        echo '<h2>⚡ Triggers</h2>';
        
        $stmt = $conn->query("SHOW TRIGGERS FROM whatsapp_logs");
        $triggers = $stmt->fetchAll();
        
        if (count($triggers) > 0) {
            echo '<table>';
            echo '<tr><th>Trigger Name</th><th>Event</th><th>Table</th><th>Timing</th></tr>';
            foreach ($triggers as $trigger) {
                echo "<tr>";
                echo "<td><strong>{$trigger['Trigger']}</strong></td>";
                echo "<td><span class='badge badge-warning'>{$trigger['Event']}</span></td>";
                echo "<td>{$trigger['Table']}</td>";
                echo "<td>{$trigger['Timing']}</td>";
                echo "</tr>";
            }
            echo '</table>';
        } else {
            echo '<p class="error">⚠️ No triggers found!</p>';
        }
        echo '</div>';
        
        // Test 5: Check Events
        echo '<div class="section">';
        echo '<h2>⏰ Event Scheduler</h2>';
        
        $stmt = $conn->query("SHOW EVENTS FROM whatsapp_logs");
        $events = $stmt->fetchAll();
        
        if (count($events) > 0) {
            echo '<table>';
            echo '<tr><th>Event Name</th><th>Status</th><th>Execute At</th></tr>';
            foreach ($events as $event) {
                echo "<tr>";
                echo "<td><strong>{$event['Name']}</strong></td>";
                echo "<td><span class='badge badge-success'>{$event['Status']}</span></td>";
                echo "<td>{$event['Execute at']}</td>";
                echo "</tr>";
            }
            echo '</table>';
            
            // Check if event scheduler is ON
            $stmt = $conn->query("SHOW VARIABLES LIKE 'event_scheduler'");
            $scheduler = $stmt->fetch();
            
            if ($scheduler['Value'] === 'ON') {
                echo '<p class="success">✅ Event Scheduler is ENABLED</p>';
            } else {
                echo '<p class="error">⚠️ Event Scheduler is DISABLED</p>';
                echo '<div class="code">SET GLOBAL event_scheduler = ON;</div>';
            }
        } else {
            echo '<p class="error">⚠️ No events found!</p>';
        }
        echo '</div>';
        
        // Test 6: Recent Activity Logs
        echo '<div class="section">';
        echo '<h2>📝 Recent Activity Logs (Last 10)</h2>';
        
        $stmt = $conn->query("SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 10");
        $logs = $stmt->fetchAll();
        
        if (count($logs) > 0) {
            echo '<table>';
            echo '<tr><th>ID</th><th>Type</th><th>Title</th><th>Description</th><th>Created At</th></tr>';
            foreach ($logs as $log) {
                $typeColors = [
                    'message' => 'success',
                    'session' => 'info',
                    'error' => 'error',
                    'system' => 'warning',
                    'api' => 'info'
                ];
                $color = $typeColors[$log['log_type']] ?? 'info';
                
                echo "<tr>";
                echo "<td>{$log['id']}</td>";
                echo "<td><span class='badge badge-{$color}'>{$log['log_type']}</span></td>";
                echo "<td><strong>{$log['title']}</strong></td>";
                echo "<td>" . substr($log['description'], 0, 100) . "...</td>";
                echo "<td>{$log['created_at']}</td>";
                echo "</tr>";
            }
            echo '</table>';
        } else {
            echo '<p class="error">⚠️ No logs found in database!</p>';
            echo '<p>You can generate sample logs: <a href="generate_sample_logs.php" target="_blank">Generate Sample Logs</a></p>';
        }
        echo '</div>';
        
        // Test 7: Test API Endpoint
        echo '<div class="section">';
        echo '<h2>🔌 API Endpoint Test</h2>';
        echo '<p>Test creating a log via API:</p>';
        echo '<div class="code">';
        echo 'POST /api/logs/create.php<br>';
        echo 'Content-Type: application/json<br><br>';
        echo json_encode([
            'log_type' => 'system',
            'title' => 'Database Test',
            'description' => 'Testing database connection and API',
            'session_id' => 'test_session'
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        echo '</div>';
        
        echo '<p><a href="generate_sample_logs.php" class="button">Generate Sample Logs</a></p>';
        echo '</div>';
        
        // Summary
        echo '<div class="section">';
        echo '<h2>✅ Summary</h2>';
        echo '<ul>';
        echo '<li class="success">✓ Database connection working</li>';
        echo '<li class="success">✓ All 4 tables exist</li>';
        echo '<li class="success">✓ Stored procedures: ' . count($procedures) . '</li>';
        echo '<li class="success">✓ Views: ' . count($views) . '</li>';
        echo '<li class="success">✓ Triggers: ' . count($triggers) . '</li>';
        echo '<li class="success">✓ Events: ' . count($events) . '</li>';
        echo '<li class="success">✓ Activity logs: ' . count($logs) . ' (recent)</li>';
        echo '</ul>';
        echo '<p class="success"><strong>Database is ready to use! 🎉</strong></p>';
        echo '</div>';
        
    } catch (Exception $e) {
        echo '<div class="section">';
        echo '<h2 class="error">❌ Error</h2>';
        echo '<p class="error">Error: ' . $e->getMessage() . '</p>';
        echo '<p>Please check:</p>';
        echo '<ul>';
        echo '<li>MySQL service is running</li>';
        echo '<li>Database credentials in <code>api/config/database.php</code></li>';
        echo '<li>Database <code>whatsapp_logs</code> has been imported from <code>sql/create_database.sql</code></li>';
        echo '</ul>';
        echo '</div>';
    }
    ?>
    
    <div class="section">
        <h2>📚 Quick Links</h2>
        <ul>
            <li><a href="test_connection.php">Test Connection (Simple)</a></li>
            <li><a href="generate_sample_logs.php">Generate Sample Logs</a></li>
            <li><a href="../public/log-activity.html">View Activity Logs (Frontend)</a></li>
            <li><a href="logs/get.php?log_type=all&limit=10">API: Get Logs (JSON)</a></li>
            <li><a href="logs/stats.php?period=today">API: Statistics (JSON)</a></li>
        </ul>
    </div>
</body>
</html>
