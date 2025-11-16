<?php
require_once 'config.php';

// معالجة حفظ الإعدادات
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_integration') {
    $service_name = $_POST['service_name'] ?? '';
    $is_enabled = isset($_POST['is_enabled']) ? 'true' : 'false';
    
    // بناء JSON config حسب نوع الخدمة
    $config = [];
    
    switch ($service_name) {
        case 'github':
            $config = [
                'token' => $_POST['github_token'] ?? '',
                'repo' => $_POST['github_repo'] ?? '',
                'branch' => $_POST['github_branch'] ?? 'main'
            ];
            break;
            
        case 'hostinger':
            $config = [
                'api_key' => $_POST['hostinger_api_key'] ?? '',
                'ftp_host' => $_POST['hostinger_ftp_host'] ?? '',
                'ftp_user' => $_POST['hostinger_ftp_user'] ?? '',
                'ftp_pass' => $_POST['hostinger_ftp_pass'] ?? ''
            ];
            break;
            
        case 'ollama':
            $config = [
                'url' => $_POST['ollama_url'] ?? 'http://localhost:11434'
            ];
            break;
            
        case 'copilot':
            $config = [
                'api_key' => $_POST['copilot_api_key'] ?? ''
            ];
            break;
            
        case 'local_db':
            $config = [
                'host' => $_POST['db_host'] ?? 'localhost',
                'user' => $_POST['db_user'] ?? 'root',
                'password' => $_POST['db_password'] ?? '',
                'database' => $_POST['db_database'] ?? 'alwakeel_db'
            ];
            break;
    }
    
    $config_json = json_encode($config, JSON_UNESCAPED_UNICODE);
    
    try {
        // تحديث أو إدراج الإعدادات
        $stmt = $conn->prepare("
            INSERT INTO integrations (service_name, is_enabled, config) 
            VALUES (:service_name, :is_enabled, :config)
            ON DUPLICATE KEY UPDATE 
                is_enabled = :is_enabled,
                config = :config,
                updated_at = CURRENT_TIMESTAMP
        ");
        
        $stmt->execute([
            ':service_name' => $service_name,
            ':is_enabled' => $is_enabled,
            ':config' => $config_json
        ]);
        
        $message = "تم حفظ إعدادات $service_name بنجاح!";
        $message_type = 'success';
        
    } catch (PDOException $e) {
        $message = "خطأ في حفظ الإعدادات: " . $e->getMessage();
        $message_type = 'error';
    }
}

// جلب الإعدادات الحالية
$integrations = [];
try {
    $stmt = $conn->query("SELECT * FROM integrations ORDER BY service_name");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $row['config'] = json_decode($row['config'], true);
        $integrations[$row['service_name']] = $row;
    }
} catch (PDOException $e) {
    $message = "خطأ في جلب الإعدادات: " . $e->getMessage();
    $message_type = 'error';
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الربط - Integration Management</title>
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
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .header h1 {
            color: #667eea;
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .header p {
            color: #666;
            font-size: 14px;
        }
        
        .nav-links {
            margin-top: 20px;
        }
        
        .nav-links a {
            display: inline-block;
            padding: 10px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 20px;
            margin-left: 10px;
            font-size: 14px;
            transition: transform 0.2s;
        }
        
        .nav-links a:hover {
            transform: translateY(-2px);
        }
        
        .message {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .integration-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 20px;
        }
        
        .integration-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .integration-card h2 {
            color: #667eea;
            font-size: 24px;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .integration-card .subtitle {
            color: #999;
            font-size: 13px;
            margin-bottom: 20px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .status-badge.enabled {
            background: #d4edda;
            color: #155724;
        }
        
        .status-badge.disabled {
            background: #f8d7da;
            color: #721c24;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }
        
        .form-group input[type="text"],
        .form-group input[type="password"],
        .form-group input[type="url"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .form-group input::placeholder {
            color: #999;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        
        .checkbox-group label {
            margin: 0;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: transform 0.2s;
            width: 100%;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .icon {
            font-size: 28px;
        }
        
        .info-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            font-size: 13px;
            color: #666;
            border-right: 4px solid #667eea;
        }
        
        .info-box strong {
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚙️ إدارة الربط والإعدادات</h1>
            <p>Integration Management - تكوين الخدمات الخارجية والمحلية</p>
            <div class="nav-links">
                <a href="alwakeel.php">🏠 الواجهة الرئيسية</a>
                <a href="chat.php">💬 الدردشة الذكية</a>
                <a href="backup.php">💾 النسخ الاحتياطي</a>
                <a href="migrate.php">🔄 ترحيل القاعدة</a>
            </div>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message_type === 'success' ? '✅' : '❌'; ?> <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="integration-grid">
            <!-- GitHub Integration -->
            <div class="integration-card">
                <h2>
                    <span class="icon">🐙</span>
                    GitHub
                    <span class="status-badge <?php echo ($integrations['github']['is_enabled'] ?? 'false') === 'true' ? 'enabled' : 'disabled'; ?>">
                        <?php echo ($integrations['github']['is_enabled'] ?? 'false') === 'true' ? 'مفعّل' : 'معطّل'; ?>
                    </span>
                </h2>
                <p class="subtitle">مزامنة الكود مع GitHub Repository</p>
                
                <form method="POST">
                    <input type="hidden" name="action" value="save_integration">
                    <input type="hidden" name="service_name" value="github">
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="github_enabled" name="is_enabled" 
                               <?php echo ($integrations['github']['is_enabled'] ?? 'false') === 'true' ? 'checked' : ''; ?>>
                        <label for="github_enabled">تفعيل الربط مع GitHub</label>
                    </div>
                    
                    <div class="form-group">
                        <label>🔑 Personal Access Token</label>
                        <input type="password" name="github_token" 
                               value="<?php echo htmlspecialchars($integrations['github']['config']['token'] ?? ''); ?>"
                               placeholder="ghp_xxxxxxxxxxxxxxxxxxxx">
                    </div>
                    
                    <div class="form-group">
                        <label>📦 Repository (username/repo)</label>
                        <input type="text" name="github_repo" 
                               value="<?php echo htmlspecialchars($integrations['github']['config']['repo'] ?? ''); ?>"
                               placeholder="alabasi2025/alwakeel_php">
                    </div>
                    
                    <div class="form-group">
                        <label>🌿 Branch</label>
                        <input type="text" name="github_branch" 
                               value="<?php echo htmlspecialchars($integrations['github']['config']['branch'] ?? 'main'); ?>"
                               placeholder="main">
                    </div>
                    
                    <button type="submit" class="btn">💾 حفظ إعدادات GitHub</button>
                    
                    <div class="info-box">
                        <strong>💡 كيفية الحصول على Token:</strong><br>
                        GitHub → Settings → Developer settings → Personal access tokens → Generate new token
                    </div>
                </form>
            </div>
            
            <!-- Hostinger Integration -->
            <div class="integration-card">
                <h2>
                    <span class="icon">🌐</span>
                    Hostinger
                    <span class="status-badge <?php echo ($integrations['hostinger']['is_enabled'] ?? 'false') === 'true' ? 'enabled' : 'disabled'; ?>">
                        <?php echo ($integrations['hostinger']['is_enabled'] ?? 'false') === 'true' ? 'مفعّل' : 'معطّل'; ?>
                    </span>
                </h2>
                <p class="subtitle">نشر المشروع على Hostinger</p>
                
                <form method="POST">
                    <input type="hidden" name="action" value="save_integration">
                    <input type="hidden" name="service_name" value="hostinger">
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="hostinger_enabled" name="is_enabled"
                               <?php echo ($integrations['hostinger']['is_enabled'] ?? 'false') === 'true' ? 'checked' : ''; ?>>
                        <label for="hostinger_enabled">تفعيل الربط مع Hostinger</label>
                    </div>
                    
                    <div class="form-group">
                        <label>🔑 API Key (اختياري)</label>
                        <input type="password" name="hostinger_api_key" 
                               value="<?php echo htmlspecialchars($integrations['hostinger']['config']['api_key'] ?? ''); ?>"
                               placeholder="API Key من لوحة Hostinger">
                    </div>
                    
                    <div class="form-group">
                        <label>🖥️ FTP Host</label>
                        <input type="text" name="hostinger_ftp_host" 
                               value="<?php echo htmlspecialchars($integrations['hostinger']['config']['ftp_host'] ?? ''); ?>"
                               placeholder="ftp.yourdomain.com">
                    </div>
                    
                    <div class="form-group">
                        <label>👤 FTP Username</label>
                        <input type="text" name="hostinger_ftp_user" 
                               value="<?php echo htmlspecialchars($integrations['hostinger']['config']['ftp_user'] ?? ''); ?>"
                               placeholder="u123456789">
                    </div>
                    
                    <div class="form-group">
                        <label>🔒 FTP Password</label>
                        <input type="password" name="hostinger_ftp_pass" 
                               value="<?php echo htmlspecialchars($integrations['hostinger']['config']['ftp_pass'] ?? ''); ?>"
                               placeholder="كلمة مرور FTP">
                    </div>
                    
                    <button type="submit" class="btn">💾 حفظ إعدادات Hostinger</button>
                    
                    <div class="info-box">
                        <strong>💡 معلومات FTP:</strong><br>
                        يمكنك الحصول على معلومات FTP من لوحة تحكم Hostinger → Files → FTP Accounts
                    </div>
                </form>
            </div>
            
            <!-- Ollama Integration -->
            <div class="integration-card">
                <h2>
                    <span class="icon">🦙</span>
                    Ollama
                    <span class="status-badge <?php echo ($integrations['ollama']['is_enabled'] ?? 'false') === 'true' ? 'enabled' : 'disabled'; ?>">
                        <?php echo ($integrations['ollama']['is_enabled'] ?? 'false') === 'true' ? 'مفعّل' : 'معطّل'; ?>
                    </span>
                </h2>
                <p class="subtitle">ذكاء اصطناعي محلي (Local AI)</p>
                
                <form method="POST">
                    <input type="hidden" name="action" value="save_integration">
                    <input type="hidden" name="service_name" value="ollama">
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="ollama_enabled" name="is_enabled"
                               <?php echo ($integrations['ollama']['is_enabled'] ?? 'false') === 'true' ? 'checked' : ''; ?>>
                        <label for="ollama_enabled">تفعيل Ollama المحلي</label>
                    </div>
                    
                    <div class="form-group">
                        <label>🔗 Ollama URL</label>
                        <input type="url" name="ollama_url" 
                               value="<?php echo htmlspecialchars($integrations['ollama']['config']['url'] ?? 'http://localhost:11434'); ?>"
                               placeholder="http://localhost:11434">
                    </div>
                    
                    <button type="submit" class="btn">💾 حفظ إعدادات Ollama</button>
                    
                    <div class="info-box">
                        <strong>💡 تثبيت Ollama:</strong><br>
                        قم بتحميل Ollama من <a href="https://ollama.ai" target="_blank">ollama.ai</a> وتشغيله محلياً
                    </div>
                </form>
            </div>
            
            <!-- Copilot Integration -->
            <div class="integration-card">
                <h2>
                    <span class="icon">🤖</span>
                    Copilot
                    <span class="status-badge <?php echo ($integrations['copilot']['is_enabled'] ?? 'false') === 'true' ? 'enabled' : 'disabled'; ?>">
                        <?php echo ($integrations['copilot']['is_enabled'] ?? 'false') === 'true' ? 'مفعّل' : 'معطّل'; ?>
                    </span>
                </h2>
                <p class="subtitle">ذكاء اصطناعي سحابي (Cloud AI)</p>
                
                <form method="POST">
                    <input type="hidden" name="action" value="save_integration">
                    <input type="hidden" name="service_name" value="copilot">
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="copilot_enabled" name="is_enabled"
                               <?php echo ($integrations['copilot']['is_enabled'] ?? 'false') === 'true' ? 'checked' : ''; ?>>
                        <label for="copilot_enabled">تفعيل Copilot API</label>
                    </div>
                    
                    <div class="form-group">
                        <label>🔑 API Key</label>
                        <input type="password" name="copilot_api_key" 
                               value="<?php echo htmlspecialchars($integrations['copilot']['config']['api_key'] ?? ''); ?>"
                               placeholder="sk-xxxxxxxxxxxxxxxxxxxx">
                    </div>
                    
                    <button type="submit" class="btn">💾 حفظ إعدادات Copilot</button>
                    
                    <div class="info-box">
                        <strong>💡 ملاحظة:</strong><br>
                        يمكنك استخدام OpenAI API أو أي API متوافق مع Copilot
                    </div>
                </form>
            </div>
            
            <!-- Local Database Integration -->
            <div class="integration-card">
                <h2>
                    <span class="icon">🗄️</span>
                    قاعدة البيانات المحلية
                    <span class="status-badge <?php echo ($integrations['local_db']['is_enabled'] ?? 'true') === 'true' ? 'enabled' : 'disabled'; ?>">
                        <?php echo ($integrations['local_db']['is_enabled'] ?? 'true') === 'true' ? 'مفعّل' : 'معطّل'; ?>
                    </span>
                </h2>
                <p class="subtitle">إعدادات MySQL المحلية</p>
                
                <form method="POST">
                    <input type="hidden" name="action" value="save_integration">
                    <input type="hidden" name="service_name" value="local_db">
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="local_db_enabled" name="is_enabled" checked disabled>
                        <label for="local_db_enabled">قاعدة البيانات المحلية (مطلوبة)</label>
                    </div>
                    
                    <div class="form-group">
                        <label>🖥️ Host</label>
                        <input type="text" name="db_host" 
                               value="<?php echo htmlspecialchars($integrations['local_db']['config']['host'] ?? 'localhost'); ?>"
                               placeholder="localhost">
                    </div>
                    
                    <div class="form-group">
                        <label>👤 Username</label>
                        <input type="text" name="db_user" 
                               value="<?php echo htmlspecialchars($integrations['local_db']['config']['user'] ?? 'root'); ?>"
                               placeholder="root">
                    </div>
                    
                    <div class="form-group">
                        <label>🔒 Password</label>
                        <input type="password" name="db_password" 
                               value="<?php echo htmlspecialchars($integrations['local_db']['config']['password'] ?? ''); ?>"
                               placeholder="كلمة المرور (فارغة في XAMPP)">
                    </div>
                    
                    <div class="form-group">
                        <label>📊 Database Name</label>
                        <input type="text" name="db_database" 
                               value="<?php echo htmlspecialchars($integrations['local_db']['config']['database'] ?? 'alwakeel_db'); ?>"
                               placeholder="alwakeel_db">
                    </div>
                    
                    <button type="submit" class="btn">💾 حفظ إعدادات قاعدة البيانات</button>
                    
                    <div class="info-box">
                        <strong>⚠️ تحذير:</strong><br>
                        تغيير هذه الإعدادات قد يؤدي إلى فقدان الاتصال بقاعدة البيانات
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
