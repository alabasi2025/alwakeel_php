<?php
/**
 * ملف إعدادات قاعدة البيانات - مثال
 * 
 * انسخ هذا الملف إلى config.php وعدّل الإعدادات
 * cp config.example.php config.php
 */

// إعدادات قاعدة البيانات
$host = 'localhost';
$dbname = 'alwakeel_db';
$username = 'root';
$password = ''; // أدخل كلمة المرور هنا

// محاولة الاتصال
try {
    $conn = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch(PDOException $e) {
    // في حالة الخطأ، سجّل الخطأ ولا تعرضه
    error_log("Database connection failed: " . $e->getMessage());
    die("فشل الاتصال بقاعدة البيانات. تحقق من الإعدادات.");
}
?>
