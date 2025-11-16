<?php
/**
 * ملف إعدادات قاعدة البيانات
 * Database Configuration File
 */

// إعدادات قاعدة البيانات
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'alwakeel_db');

// إعدادات عامة
define('CHARSET', 'utf8mb4');
define('TIMEZONE', 'Asia/Riyadh');

// تعيين المنطقة الزمنية
date_default_timezone_set(TIMEZONE);

// إنشاء اتصال بقاعدة البيانات
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch(PDOException $e) {
        die(json_encode([
            'success' => false,
            'error' => 'فشل الاتصال بقاعدة البيانات: ' . $e->getMessage()
        ]));
    }
}

// دالة لإرجاع استجابة JSON
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// دالة لتنظيف المدخلات
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>
