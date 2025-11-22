<?php
/**
 * Database Configuration
 * WhatsApp Multi-Session Activity Logs
 */

class Database {
    // Database credentials
    private $host = "localhost";
    private $db_name = "whatsapp_logs";
    private $username = "root";  // Ganti dengan username MySQL Anda
    private $password = "";      // Ganti dengan password MySQL Anda
    private $charset = "utf8mb4";
    
    public $conn;
    
    /**
     * Get database connection
     */
    public function getConnection() {
        $this->conn = null;
        
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];
            
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        
        return $this->conn;
    }
    
    /**
     * Close connection
     */
    public function closeConnection() {
        $this->conn = null;
    }
    
    /**
     * Test connection
     */
    public static function testConnection() {
        try {
            $db = new self();
            $conn = $db->getConnection();
            
            if($conn) {
                // Get server info
                $version = $conn->getAttribute(PDO::ATTR_SERVER_VERSION);
                $driver = $conn->getAttribute(PDO::ATTR_DRIVER_NAME);
                
                return [
                    'success' => true,
                    'message' => 'Database connection successful',
                    'data' => [
                        'database' => $db->db_name,
                        'host' => $db->host,
                        'driver' => $driver,
                        'server_version' => $version
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Database connection failed',
                    'data' => 'Could not establish connection'
                ];
            }
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database connection error',
                'data' => $e->getMessage()
            ];
        }
    }
}

/**
 * Response Helper
 */
class Response {
    
    public static function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    public static function success($message, $data = null, $statusCode = 200) {
        $response = [
            'success' => true,
            'message' => $message
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        self::json($response, $statusCode);
    }
    
    public static function error($message, $statusCode = 400, $errors = null) {
        $response = [
            'success' => false,
            'message' => $message
        ];
        
        if ($errors !== null) {
            $response['errors'] = $errors;
        }
        
        self::json($response, $statusCode);
    }
}

/**
 * Request Helper
 */
class Request {
    
    public static function getBody() {
        $body = file_get_contents('php://input');
        return json_decode($body, true);
    }
    
    public static function getQuery($key = null, $default = null) {
        if ($key === null) {
            return $_GET;
        }
        return isset($_GET[$key]) ? $_GET[$key] : $default;
    }
    
    public static function getClientIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }
    
    public static function getUserAgent() {
        return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    }
    
    public static function isPost() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
    
    public static function isGet() {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }
    
    public static function isPut() {
        return $_SERVER['REQUEST_METHOD'] === 'PUT';
    }
    
    public static function isDelete() {
        return $_SERVER['REQUEST_METHOD'] === 'DELETE';
    }
}

/**
 * Validator Helper
 */
class Validator {
    
    public static function required($value, $fieldName) {
        if (empty($value) && $value !== '0' && $value !== 0) {
            return "$fieldName is required";
        }
        return null;
    }
    
    public static function minLength($value, $min, $fieldName) {
        if (strlen($value) < $min) {
            return "$fieldName must be at least $min characters";
        }
        return null;
    }
    
    public static function maxLength($value, $max, $fieldName) {
        if (strlen($value) > $max) {
            return "$fieldName must not exceed $max characters";
        }
        return null;
    }
    
    public static function email($value, $fieldName) {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return "$fieldName must be a valid email address";
        }
        return null;
    }
    
    public static function inArray($value, $array, $fieldName) {
        if (!in_array($value, $array)) {
            return "$fieldName must be one of: " . implode(', ', $array);
        }
        return null;
    }
    
    public static function validate($data, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $fieldRules) {
            $value = isset($data[$field]) ? $data[$field] : null;
            
            foreach ($fieldRules as $rule => $params) {
                $error = null;
                
                switch ($rule) {
                    case 'required':
                        $error = self::required($value, $field);
                        break;
                    case 'min':
                        $error = self::minLength($value, $params, $field);
                        break;
                    case 'max':
                        $error = self::maxLength($value, $params, $field);
                        break;
                    case 'email':
                        $error = self::email($value, $field);
                        break;
                    case 'in':
                        $error = self::inArray($value, $params, $field);
                        break;
                }
                
                if ($error) {
                    $errors[$field] = $error;
                    break;
                }
            }
        }
        
        return $errors;
    }
}
?>
