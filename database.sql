-- إنشاء قاعدة البيانات
CREATE DATABASE IF NOT EXISTS alwakeel_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE alwakeel_db;

-- ============================================
-- الجداول الأساسية (موجودة مسبقاً)
-- ============================================

-- جدول الأوامر (commands)
CREATE TABLE IF NOT EXISTS commands (
    id INT AUTO_INCREMENT PRIMARY KEY,
    command_text TEXT NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول النتائج (results)
CREATE TABLE IF NOT EXISTS results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    command_id INT NOT NULL,
    result_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (command_id) REFERENCES commands(id) ON DELETE CASCADE,
    INDEX idx_command (command_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- الجداول الجديدة (للوكيل الذكي)
-- ============================================

-- جدول إعدادات الربط (integrations)
-- يحتوي على مفاتيح API وإعدادات الخدمات الخارجية
CREATE TABLE IF NOT EXISTS integrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_name VARCHAR(50) NOT NULL UNIQUE COMMENT 'github, hostinger, ollama, copilot, local_db',
    is_enabled ENUM('true', 'false') DEFAULT 'false' NOT NULL,
    config TEXT NOT NULL COMMENT 'JSON string with encrypted sensitive data',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_service (service_name),
    INDEX idx_enabled (is_enabled)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول سجل الأوامر (command_history)
-- يحتوي على سياق الأوامر المنفذة للتعلم
CREATE TABLE IF NOT EXISTS command_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    command_id INT NOT NULL,
    user_id INT DEFAULT NULL COMMENT 'optional, can be null for system commands',
    context TEXT COMMENT 'JSON string with conversation context',
    execution_time INT COMMENT 'milliseconds',
    ai_engine VARCHAR(50) COMMENT 'ollama, copilot, langchain',
    success ENUM('true', 'false') NOT NULL,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (command_id) REFERENCES commands(id) ON DELETE CASCADE,
    INDEX idx_command (command_id),
    INDEX idx_ai_engine (ai_engine),
    INDEX idx_success (success),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول بيانات التعلم (learning_data)
-- يحتوي على الأنماط والاقتراحات المستخلصة
CREATE TABLE IF NOT EXISTS learning_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pattern TEXT NOT NULL COMMENT 'النمط المكتشف',
    suggestion TEXT NOT NULL COMMENT 'الاقتراح الذكي',
    frequency INT DEFAULT 1 NOT NULL COMMENT 'عدد مرات الظهور',
    confidence INT DEFAULT 50 NOT NULL COMMENT 'نسبة الثقة (0-100)',
    category VARCHAR(50) COMMENT 'sql, git, deployment, etc.',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_confidence (confidence),
    INDEX idx_frequency (frequency)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول سجل المزامنة (sync_logs)
-- يحتوي على سجل عمليات المزامنة مع GitHub وHostinger
CREATE TABLE IF NOT EXISTS sync_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service VARCHAR(50) NOT NULL COMMENT 'github, hostinger',
    action VARCHAR(50) NOT NULL COMMENT 'pull, push, deploy, backup',
    status ENUM('pending', 'running', 'success', 'failed') DEFAULT 'pending' NOT NULL,
    details TEXT COMMENT 'JSON string with sync details',
    files_affected INT DEFAULT 0,
    error_message TEXT,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    INDEX idx_service (service),
    INDEX idx_action (action),
    INDEX idx_status (status),
    INDEX idx_started (started_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- بيانات تجريبية
-- ============================================

-- بيانات تجريبية للأوامر
INSERT INTO commands (command_text, status) VALUES
('أمر تجريبي أول', 'completed'),
('أمر تجريبي ثاني', 'processing'),
('أمر تجريبي ثالث', 'pending')
ON DUPLICATE KEY UPDATE id=id;

-- بيانات تجريبية للنتائج
INSERT INTO results (command_id, result_text) VALUES
(1, 'تم تنفيذ الأمر الأول بنجاح'),
(1, 'نتيجة إضافية للأمر الأول'),
(2, 'جاري معالجة الأمر الثاني')
ON DUPLICATE KEY UPDATE id=id;

-- بيانات تجريبية للإعدادات (معطلة افتراضياً)
INSERT INTO integrations (service_name, is_enabled, config) VALUES
('github', 'false', '{"token":"","repo":"","branch":"main"}'),
('hostinger', 'false', '{"api_key":"","ftp_host":"","ftp_user":"","ftp_pass":""}'),
('ollama', 'false', '{"url":"http://localhost:11434"}'),
('copilot', 'false', '{"api_key":""}'),
('local_db', 'true', '{"host":"localhost","user":"root","password":"","database":"alwakeel_db"}')
ON DUPLICATE KEY UPDATE updated_at=CURRENT_TIMESTAMP;

-- بيانات تجريبية للتعلم
INSERT INTO learning_data (pattern, suggestion, frequency, confidence, category) VALUES
('SELECT * FROM', 'استخدم SELECT مع أعمدة محددة بدلاً من * لتحسين الأداء', 5, 85, 'sql'),
('git push', 'تأكد من عمل git pull قبل git push لتجنب التعارضات', 3, 90, 'git'),
('backup', 'يُنصح بعمل نسخة احتياطية قبل أي تحديث كبير', 10, 95, 'deployment')
ON DUPLICATE KEY UPDATE frequency=frequency+1, updated_at=CURRENT_TIMESTAMP;
