<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ترحيل قاعدة البيانات - Alwakeel Migration</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 800px;
            width: 100%;
            padding: 40px;
        }
        
        h1 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .status {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 15px;
            font-size: 14px;
        }
        
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
            transition: transform 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .table-list {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin: 15px 0;
        }
        
        .table-list ul {
            list-style: none;
            padding-right: 0;
        }
        
        .table-list li {
            padding: 8px 0;
            border-bottom: 1px solid #dee2e6;
        }
        
        .table-list li:last-child {
            border-bottom: none;
        }
        
        .table-list li::before {
            content: "✓ ";
            color: #28a745;
            font-weight: bold;
            margin-left: 8px;
        }
        
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔄 ترحيل قاعدة البيانات</h1>
        <p class="subtitle">Database Migration Tool - Alwakeel Agent Interface</p>
        
        <?php
        // تضمين ملف الاتصال بقاعدة البيانات
        require_once 'config.php';
        
        $messages = [];
        $errors = [];
        $tables_created = [];
        
        try {
            // قراءة ملف database.sql
            $sql_file = __DIR__ . '/database.sql';
            
            if (!file_exists($sql_file)) {
                throw new Exception("ملف database.sql غير موجود!");
            }
            
            $sql_content = file_get_contents($sql_file);
            
            if ($sql_content === false) {
                throw new Exception("فشل في قراءة ملف database.sql");
            }
            
            // تقسيم الاستعلامات
            $statements = array_filter(
                array_map('trim', explode(';', $sql_content)),
                function($stmt) {
                    return !empty($stmt) && 
                           !preg_match('/^--/', $stmt) && 
                           !preg_match('/^\/\*/', $stmt);
                }
            );
            
            $messages[] = "تم العثور على " . count($statements) . " استعلام SQL";
            
            // تنفيذ الاستعلامات
            $success_count = 0;
            $skip_count = 0;
            
            foreach ($statements as $statement) {
                $statement = trim($statement);
                
                // تخطي التعليقات والأسطر الفارغة
                if (empty($statement) || 
                    preg_match('/^--/', $statement) || 
                    preg_match('/^\/\*/', $statement)) {
                    continue;
                }
                
                // تخطي USE database (سنستخدم الاتصال الموجود)
                if (preg_match('/^USE\s+/i', $statement)) {
                    $skip_count++;
                    continue;
                }
                
                // تخطي CREATE DATABASE (القاعدة موجودة بالفعل)
                if (preg_match('/^CREATE\s+DATABASE/i', $statement)) {
                    $skip_count++;
                    continue;
                }
                
                try {
                    $conn->exec($statement);
                    $success_count++;
                    
                    // استخراج اسم الجدول من CREATE TABLE
                    if (preg_match('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?(\w+)`?/i', $statement, $matches)) {
                        $tables_created[] = $matches[1];
                    }
                    
                } catch (PDOException $e) {
                    // تجاهل أخطاء "الجدول موجود بالفعل" و "مفتاح مكرر"
                    if (strpos($e->getMessage(), 'already exists') === false && 
                        strpos($e->getMessage(), 'Duplicate entry') === false) {
                        $errors[] = "خطأ في تنفيذ الاستعلام: " . $e->getMessage();
                    }
                }
            }
            
            $messages[] = "تم تنفيذ $success_count استعلام بنجاح";
            $messages[] = "تم تخطي $skip_count استعلام";
            
            // التحقق من الجداول المنشأة
            $stmt = $conn->query("SHOW TABLES");
            $existing_tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $messages[] = "الجداول الموجودة في قاعدة البيانات: " . count($existing_tables);
            
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
        
        // عرض الرسائل
        if (!empty($errors)) {
            foreach ($errors as $error) {
                echo '<div class="status error">❌ ' . htmlspecialchars($error) . '</div>';
            }
        }
        
        if (!empty($messages)) {
            foreach ($messages as $message) {
                echo '<div class="status success">✅ ' . htmlspecialchars($message) . '</div>';
            }
        }
        
        // عرض الجداول المنشأة
        if (!empty($tables_created)) {
            echo '<div class="status info">';
            echo '<strong>📋 الجداول التي تم إنشاؤها/تحديثها:</strong>';
            echo '<div class="table-list"><ul>';
            foreach ($tables_created as $table) {
                echo '<li><code>' . htmlspecialchars($table) . '</code></li>';
            }
            echo '</ul></div>';
            echo '</div>';
        }
        
        // عرض جميع الجداول الموجودة
        if (isset($existing_tables) && !empty($existing_tables)) {
            echo '<div class="status info">';
            echo '<strong>🗄️ جميع الجداول في قاعدة البيانات:</strong>';
            echo '<div class="table-list"><ul>';
            foreach ($existing_tables as $table) {
                echo '<li><code>' . htmlspecialchars($table) . '</code></li>';
            }
            echo '</ul></div>';
            echo '</div>';
        }
        
        // رسالة نهائية
        if (empty($errors)) {
            echo '<div class="status success">';
            echo '<strong>🎉 تم الترحيل بنجاح!</strong><br>';
            echo 'قاعدة البيانات جاهزة للاستخدام. يمكنك الآن استخدام النظام بالكامل.';
            echo '</div>';
        } else {
            echo '<div class="status warning">';
            echo '<strong>⚠️ اكتمل الترحيل مع بعض التحذيرات</strong><br>';
            echo 'يرجى مراجعة الأخطاء أعلاه.';
            echo '</div>';
        }
        ?>
        
        <div style="margin-top: 30px; text-align: center;">
            <a href="alwakeel.php" class="btn">🚀 الذهاب إلى الواجهة الرئيسية</a>
            <a href="integrations.php" class="btn">⚙️ إعدادات الربط</a>
        </div>
        
        <div class="status info" style="margin-top: 20px; font-size: 12px;">
            <strong>💡 ملاحظة:</strong> هذا السكريبت آمن للتشغيل عدة مرات. 
            سيقوم بإنشاء الجداول فقط إذا لم تكن موجودة، ولن يحذف أي بيانات موجودة.
        </div>
    </div>
</body>
</html>
