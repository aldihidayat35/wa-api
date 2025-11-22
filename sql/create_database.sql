-- ============================================
-- WhatsApp Multi-Session Activity Logs Database
-- ============================================

-- Create Database
CREATE DATABASE IF NOT EXISTS `whatsapp_logs` 
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `whatsapp_logs`;

-- ============================================
-- Table: activity_logs
-- Menyimpan semua aktivitas sistem
-- ============================================
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `log_type` ENUM('message', 'session', 'error', 'system', 'api') NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `session_id` VARCHAR(100) DEFAULT NULL,
  `phone_number` VARCHAR(20) DEFAULT NULL,
  `metadata` JSON DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` VARCHAR(500) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_log_type` (`log_type`),
  INDEX `idx_session_id` (`session_id`),
  INDEX `idx_created_at` (`created_at`),
  INDEX `idx_phone_number` (`phone_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: log_statistics
-- Menyimpan statistik harian untuk performa
-- ============================================
CREATE TABLE IF NOT EXISTS `log_statistics` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `stat_date` DATE NOT NULL UNIQUE,
  `total_messages` INT DEFAULT 0,
  `total_sessions` INT DEFAULT 0,
  `total_errors` INT DEFAULT 0,
  `total_api_calls` INT DEFAULT 0,
  `total_activities` INT DEFAULT 0,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_stat_date` (`stat_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: sessions
-- Menyimpan informasi session WhatsApp
-- ============================================
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `session_id` VARCHAR(100) NOT NULL UNIQUE,
  `session_name` VARCHAR(255) DEFAULT NULL,
  `phone_number` VARCHAR(20) DEFAULT NULL,
  `is_connected` BOOLEAN DEFAULT FALSE,
  `last_connected` TIMESTAMP NULL DEFAULT NULL,
  `last_disconnected` TIMESTAMP NULL DEFAULT NULL,
  `qr_generated_count` INT DEFAULT 0,
  `messages_sent` INT DEFAULT 0,
  `messages_received` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_session_id` (`session_id`),
  INDEX `idx_is_connected` (`is_connected`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: message_logs
-- Menyimpan detail pesan untuk tracking
-- ============================================
CREATE TABLE IF NOT EXISTS `message_logs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `session_id` VARCHAR(100) NOT NULL,
  `message_id` VARCHAR(255) DEFAULT NULL,
  `direction` ENUM('sent', 'received') NOT NULL,
  `phone_number` VARCHAR(20) NOT NULL,
  `message_type` ENUM('text', 'image', 'video', 'audio', 'document', 'other') DEFAULT 'text',
  `message_content` TEXT,
  `media_url` VARCHAR(500) DEFAULT NULL,
  `status` ENUM('pending', 'sent', 'delivered', 'read', 'failed') DEFAULT 'pending',
  `error_message` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_session_id` (`session_id`),
  INDEX `idx_phone_number` (`phone_number`),
  INDEX `idx_direction` (`direction`),
  INDEX `idx_created_at` (`created_at`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Stored Procedures
-- ============================================

-- Procedure: Get Activity Logs dengan Filter
DELIMITER //
CREATE PROCEDURE GetActivityLogs(
  IN p_log_type VARCHAR(20),
  IN p_session_id VARCHAR(100),
  IN p_limit INT,
  IN p_offset INT
)
BEGIN
  SELECT * FROM activity_logs
  WHERE 
    (p_log_type IS NULL OR p_log_type = 'all' OR log_type = p_log_type)
    AND (p_session_id IS NULL OR session_id = p_session_id)
  ORDER BY created_at DESC
  LIMIT p_limit OFFSET p_offset;
END //
DELIMITER ;

-- Procedure: Update Daily Statistics
DELIMITER //
CREATE PROCEDURE UpdateDailyStats()
BEGIN
  INSERT INTO log_statistics (
    stat_date,
    total_messages,
    total_sessions,
    total_errors,
    total_api_calls,
    total_activities
  )
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
    total_activities = VALUES(total_activities);
END //
DELIMITER ;

-- ============================================
-- Triggers
-- ============================================

-- Trigger: Auto-update session stats saat message
DELIMITER //
CREATE TRIGGER after_message_insert
AFTER INSERT ON message_logs
FOR EACH ROW
BEGIN
  UPDATE sessions
  SET 
    messages_sent = messages_sent + (CASE WHEN NEW.direction = 'sent' THEN 1 ELSE 0 END),
    messages_received = messages_received + (CASE WHEN NEW.direction = 'received' THEN 1 ELSE 0 END)
  WHERE session_id = NEW.session_id;
END //
DELIMITER ;

-- ============================================
-- Sample Data (Optional - for testing)
-- ============================================

-- Insert sample sessions
INSERT INTO `sessions` (`session_id`, `session_name`, `is_connected`) VALUES
('session1', 'Session Utama', TRUE),
('session2', 'Session Backup', FALSE);

-- Insert sample logs
INSERT INTO `activity_logs` (`log_type`, `title`, `description`, `session_id`, `metadata`) VALUES
('system', 'Sistem Dimulai', 'Aplikasi WhatsApp Multi-Session berhasil dijalankan', NULL, '{"version": "1.0.0"}'),
('session', 'Session Login', 'Session "session1" berhasil login', 'session1', '{"user": "Admin", "phone": "628123456789"}'),
('message', 'Pesan Terkirim', 'Pesan berhasil dikirim ke 628987654321', 'session1', '{"recipient": "628987654321", "messageId": "msg123"}');

-- ============================================
-- Cleanup Old Logs (Auto-delete logs > 90 days)
-- Jalankan via Cron Job harian
-- ============================================
CREATE EVENT IF NOT EXISTS cleanup_old_logs
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_TIMESTAMP
DO
  DELETE FROM activity_logs 
  WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);

-- ============================================
-- Indexes untuk Performance
-- ============================================

-- Composite index untuk query umum
CREATE INDEX idx_type_date ON activity_logs(log_type, created_at DESC);
CREATE INDEX idx_session_date ON activity_logs(session_id, created_at DESC);

-- Full-text search index untuk description
ALTER TABLE activity_logs ADD FULLTEXT INDEX ft_description (description);

-- ============================================
-- Views untuk Reporting
-- ============================================

-- View: Daily Activity Summary
CREATE OR REPLACE VIEW v_daily_activity AS
SELECT 
  DATE(created_at) as activity_date,
  log_type,
  COUNT(*) as total_count,
  COUNT(DISTINCT session_id) as unique_sessions
FROM activity_logs
GROUP BY DATE(created_at), log_type
ORDER BY activity_date DESC, log_type;

-- View: Session Performance
CREATE OR REPLACE VIEW v_session_performance AS
SELECT 
  s.session_id,
  s.session_name,
  s.is_connected,
  s.messages_sent,
  s.messages_received,
  COUNT(al.id) as total_activities,
  SUM(CASE WHEN al.log_type = 'error' THEN 1 ELSE 0 END) as error_count,
  s.last_connected,
  s.created_at
FROM sessions s
LEFT JOIN activity_logs al ON s.session_id = al.session_id
GROUP BY s.id
ORDER BY s.messages_sent DESC;

-- View: Recent Errors
CREATE OR REPLACE VIEW v_recent_errors AS
SELECT 
  id,
  title,
  description,
  session_id,
  created_at
FROM activity_logs
WHERE log_type = 'error'
ORDER BY created_at DESC
LIMIT 100;

-- ============================================
-- Grant Permissions (Adjust as needed)
-- ============================================

-- Create user for application
-- CREATE USER 'whatsapp_user'@'localhost' IDENTIFIED BY 'your_strong_password';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON whatsapp_logs.* TO 'whatsapp_user'@'localhost';
-- FLUSH PRIVILEGES;

-- ============================================
-- Backup Recommendation
-- ============================================

-- Run this command via cron for daily backup:
-- mysqldump -u root -p whatsapp_logs > /backup/whatsapp_logs_$(date +%Y%m%d).sql

-- ============================================
-- END OF SCHEMA
-- ============================================
