-- إنشاء قاعدة البيانات
CREATE DATABASE IF NOT EXISTS alwakeel_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE alwakeel_db;

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

-- إدراج بيانات تجريبية
INSERT INTO commands (command_text, status) VALUES
('أمر تجريبي أول', 'completed'),
('أمر تجريبي ثاني', 'processing'),
('أمر تجريبي ثالث', 'pending');

INSERT INTO results (command_id, result_text) VALUES
(1, 'تم تنفيذ الأمر الأول بنجاح'),
(1, 'نتيجة إضافية للأمر الأول'),
(2, 'جاري معالجة الأمر الثاني');
