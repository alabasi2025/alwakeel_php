<?php
require_once 'config.php';

// الحصول على اتصال قاعدة البيانات
$conn = getDBConnection();

/**
 * محرك المزامنة - Sync Engine
 * يدير عمليات المزامنة مع GitHub و Hostinger
 */

class SyncEngine {
    private $conn;
    private $integrations = [];
    
    public function __construct($db_connection) {
        $this->conn = $db_connection;
        $this->loadIntegrations();
    }
    
    /**
     * تحميل إعدادات الربط من قاعدة البيانات
     */
    private function loadIntegrations() {
        try {
            $stmt = $this->conn->query("SELECT * FROM integrations WHERE is_enabled = 'true'");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $this->integrations[$row['service_name']] = json_decode($row['config'], true);
            }
        } catch (PDOException $e) {
            error_log("خطأ في تحميل الإعدادات: " . $e->getMessage());
        }
    }
    
    /**
     * تسجيل عملية مزامنة في قاعدة البيانات
     */
    private function logSync($service, $action, $status, $details = null, $files_affected = 0, $error_message = null) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO sync_logs (service, action, status, details, files_affected, error_message, started_at, completed_at)
                VALUES (:service, :action, :status, :details, :files_affected, :error_message, NOW(), NOW())
            ");
            
            $stmt->execute([
                ':service' => $service,
                ':action' => $action,
                ':status' => $status,
                ':details' => $details ? json_encode($details, JSON_UNESCAPED_UNICODE) : null,
                ':files_affected' => $files_affected,
                ':error_message' => $error_message
            ]);
            
            return $this->conn->lastInsertId();
        } catch (PDOException $e) {
            error_log("خطأ في تسجيل المزامنة: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * GitHub Pull - سحب التحديثات من GitHub
     */
    public function githubPull() {
        if (!isset($this->integrations['github'])) {
            return ['success' => false, 'message' => 'GitHub غير مفعّل'];
        }
        
        $config = $this->integrations['github'];
        $repo = $config['repo'];
        $branch = $config['branch'] ?? 'main';
        $token = $config['token'];
        
        $log_id = $this->logSync('github', 'pull', 'running');
        
        try {
            // استخدام GitHub API لجلب محتويات الريبو
            $api_url = "https://api.github.com/repos/{$repo}/zipball/{$branch}";
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $api_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: token {$token}",
                "User-Agent: Alwakeel-PHP-Agent"
            ]);
            
            $zip_content = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($http_code !== 200) {
                throw new Exception("فشل في جلب الملفات من GitHub (HTTP {$http_code})");
            }
            
            // حفظ الملف المضغوط مؤقتاً
            $temp_zip = sys_get_temp_dir() . '/github_pull_' . time() . '.zip';
            file_put_contents($temp_zip, $zip_content);
            
            // فك الضغط
            $zip = new ZipArchive();
            if ($zip->open($temp_zip) === TRUE) {
                $extract_path = __DIR__ . '/github_temp';
                $zip->extractTo($extract_path);
                $files_count = $zip->numFiles;
                $zip->close();
                
                // تنظيف
                unlink($temp_zip);
                
                $this->logSync('github', 'pull', 'success', 
                    ['repo' => $repo, 'branch' => $branch], 
                    $files_count
                );
                
                return [
                    'success' => true, 
                    'message' => "تم سحب {$files_count} ملف من GitHub بنجاح",
                    'files_count' => $files_count,
                    'extract_path' => $extract_path
                ];
            } else {
                throw new Exception("فشل في فك ضغط الملفات");
            }
            
        } catch (Exception $e) {
            $this->logSync('github', 'pull', 'failed', null, 0, $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * GitHub Push - رفع التحديثات إلى GitHub
     */
    public function githubPush($commit_message = 'تحديث تلقائي من الوكيل المحلي') {
        if (!isset($this->integrations['github'])) {
            return ['success' => false, 'message' => 'GitHub غير مفعّل'];
        }
        
        $config = $this->integrations['github'];
        $repo = $config['repo'];
        $branch = $config['branch'] ?? 'main';
        $token = $config['token'];
        
        $log_id = $this->logSync('github', 'push', 'running');
        
        try {
            // استخدام Git CLI إذا كان متاحاً
            if (!is_dir(__DIR__ . '/.git')) {
                // تهيئة Git إذا لم يكن موجوداً
                exec("cd " . __DIR__ . " && git init", $output, $return_code);
                exec("cd " . __DIR__ . " && git remote add origin https://{$token}@github.com/{$repo}.git", $output, $return_code);
            }
            
            // إضافة جميع الملفات
            exec("cd " . __DIR__ . " && git add .", $output, $return_code);
            
            // عمل commit
            exec("cd " . __DIR__ . " && git commit -m \"{$commit_message}\"", $output, $return_code);
            
            // رفع إلى GitHub
            exec("cd " . __DIR__ . " && git push -u origin {$branch}", $output, $return_code);
            
            if ($return_code === 0) {
                $this->logSync('github', 'push', 'success', 
                    ['repo' => $repo, 'branch' => $branch, 'message' => $commit_message]
                );
                
                return [
                    'success' => true,
                    'message' => 'تم رفع التحديثات إلى GitHub بنجاح',
                    'commit_message' => $commit_message
                ];
            } else {
                throw new Exception("فشل في رفع الملفات (Exit code: {$return_code})");
            }
            
        } catch (Exception $e) {
            $this->logSync('github', 'push', 'failed', null, 0, $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Hostinger Deploy - نشر المشروع على Hostinger عبر FTP
     */
    public function hostingerDeploy() {
        if (!isset($this->integrations['hostinger'])) {
            return ['success' => false, 'message' => 'Hostinger غير مفعّل'];
        }
        
        $config = $this->integrations['hostinger'];
        $ftp_host = $config['ftp_host'];
        $ftp_user = $config['ftp_user'];
        $ftp_pass = $config['ftp_pass'];
        
        $log_id = $this->logSync('hostinger', 'deploy', 'running');
        
        try {
            // الاتصال بـ FTP
            $ftp_conn = ftp_connect($ftp_host);
            if (!$ftp_conn) {
                throw new Exception("فشل الاتصال بـ FTP Server");
            }
            
            // تسجيل الدخول
            $login = ftp_login($ftp_conn, $ftp_user, $ftp_pass);
            if (!$login) {
                throw new Exception("فشل تسجيل الدخول إلى FTP");
            }
            
            // تفعيل الوضع السلبي
            ftp_pasv($ftp_conn, true);
            
            // رفع الملفات
            $files_uploaded = 0;
            $local_path = __DIR__;
            $remote_path = '/public_html'; // المسار الافتراضي في Hostinger
            
            // قائمة الملفات المراد رفعها
            $files_to_upload = [
                'alwakeel.php',
                'api.php',
                'backup.php',
                'integrations.php',
                'sync_engine.php',
                'chat.php',
                'ai_engine.php',
                'config.php',
                'database.sql',
                'migrate.php'
            ];
            
            foreach ($files_to_upload as $file) {
                $local_file = $local_path . '/' . $file;
                $remote_file = $remote_path . '/' . $file;
                
                if (file_exists($local_file)) {
                    if (ftp_put($ftp_conn, $remote_file, $local_file, FTP_BINARY)) {
                        $files_uploaded++;
                    }
                }
            }
            
            ftp_close($ftp_conn);
            
            $this->logSync('hostinger', 'deploy', 'success', 
                ['host' => $ftp_host], 
                $files_uploaded
            );
            
            return [
                'success' => true,
                'message' => "تم رفع {$files_uploaded} ملف إلى Hostinger بنجاح",
                'files_uploaded' => $files_uploaded
            ];
            
        } catch (Exception $e) {
            $this->logSync('hostinger', 'deploy', 'failed', null, 0, $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Backup - نسخ احتياطي تلقائي قبل المزامنة
     */
    public function createBackup() {
        try {
            $backup_dir = __DIR__ . '/backups';
            if (!is_dir($backup_dir)) {
                mkdir($backup_dir, 0755, true);
            }
            
            $backup_file = $backup_dir . '/backup_' . date('Y-m-d_H-i-s') . '.zip';
            
            $zip = new ZipArchive();
            if ($zip->open($backup_file, ZipArchive::CREATE) === TRUE) {
                $files = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator(__DIR__),
                    RecursiveIteratorIterator::LEAVES_ONLY
                );
                
                $files_count = 0;
                foreach ($files as $file) {
                    if (!$file->isDir() && 
                        strpos($file->getPathname(), '/backups/') === false &&
                        strpos($file->getPathname(), '/.git/') === false) {
                        
                        $filePath = $file->getRealPath();
                        $relativePath = substr($filePath, strlen(__DIR__) + 1);
                        
                        $zip->addFile($filePath, $relativePath);
                        $files_count++;
                    }
                }
                
                $zip->close();
                
                $this->logSync('local', 'backup', 'success', 
                    ['file' => $backup_file], 
                    $files_count
                );
                
                return [
                    'success' => true,
                    'message' => "تم إنشاء نسخة احتياطية ({$files_count} ملف)",
                    'backup_file' => $backup_file,
                    'files_count' => $files_count
                ];
            }
            
        } catch (Exception $e) {
            $this->logSync('local', 'backup', 'failed', null, 0, $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * الحصول على سجل المزامنة
     */
    public function getSyncLogs($limit = 50) {
        try {
            $stmt = $this->conn->prepare("
                SELECT * FROM sync_logs 
                ORDER BY started_at DESC 
                LIMIT :limit
            ");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("خطأ في جلب سجل المزامنة: " . $e->getMessage());
            return [];
        }
    }
}

// معالجة طلبات AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    $engine = new SyncEngine($conn);
    $response = [];
    
    switch ($_POST['action']) {
        case 'github_pull':
            $response = $engine->githubPull();
            break;
            
        case 'github_push':
            $commit_message = $_POST['commit_message'] ?? 'تحديث تلقائي من الوكيل المحلي';
            $response = $engine->githubPush($commit_message);
            break;
            
        case 'hostinger_deploy':
            $response = $engine->hostingerDeploy();
            break;
            
        case 'create_backup':
            $response = $engine->createBackup();
            break;
            
        case 'get_logs':
            $logs = $engine->getSyncLogs(50);
            $response = ['success' => true, 'logs' => $logs];
            break;
            
        default:
            $response = ['success' => false, 'message' => 'عملية غير معروفة'];
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// واجهة المستخدم
$engine = new SyncEngine($conn);
$logs = $engine->getSyncLogs(20);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>محرك المزامنة - Sync Engine</title>
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
        }
        
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .action-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            text-align: center;
        }
        
        .action-card h3 {
            color: #667eea;
            margin-bottom: 15px;
            font-size: 20px;
        }
        
        .action-card p {
            color: #666;
            font-size: 13px;
            margin-bottom: 20px;
        }
        
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        
        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .logs-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .logs-section h2 {
            color: #667eea;
            margin-bottom: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px;
            text-align: right;
            border-bottom: 1px solid #eee;
            font-size: 13px;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: bold;
        }
        
        .status-success { background: #d4edda; color: #155724; }
        .status-failed { background: #f8d7da; color: #721c24; }
        .status-running { background: #fff3cd; color: #856404; }
        .status-pending { background: #d1ecf1; color: #0c5460; }
        
        #message {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: none;
        }
        
        #message.success {
            background: #d4edda;
            color: #155724;
        }
        
        #message.error {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔄 محرك المزامنة</h1>
            <p>Sync Engine - إدارة المزامنة مع GitHub و Hostinger</p>
            <div class="nav-links">
                <a href="alwakeel.php">🏠 الواجهة الرئيسية</a>
                <a href="integrations.php">⚙️ إعدادات الربط</a>
                <a href="chat.php">💬 الدردشة الذكية</a>
            </div>
        </div>
        
        <div id="message"></div>
        
        <div class="actions-grid">
            <div class="action-card">
                <h3>🔽 سحب من GitHub</h3>
                <p>جلب آخر التحديثات من المستودع</p>
                <button class="btn" onclick="syncAction('github_pull')">سحب التحديثات</button>
            </div>
            
            <div class="action-card">
                <h3>🔼 رفع إلى GitHub</h3>
                <p>رفع التغييرات المحلية إلى المستودع</p>
                <button class="btn" onclick="syncAction('github_push')">رفع التحديثات</button>
            </div>
            
            <div class="action-card">
                <h3>🚀 نشر على Hostinger</h3>
                <p>رفع المشروع إلى السيرفر المباشر</p>
                <button class="btn" onclick="syncAction('hostinger_deploy')">نشر الآن</button>
            </div>
            
            <div class="action-card">
                <h3>💾 نسخة احتياطية</h3>
                <p>إنشاء نسخة احتياطية من المشروع</p>
                <button class="btn" onclick="syncAction('create_backup')">إنشاء نسخة</button>
            </div>
        </div>
        
        <div class="logs-section">
            <h2>📊 سجل المزامنة</h2>
            <table>
                <thead>
                    <tr>
                        <th>الخدمة</th>
                        <th>العملية</th>
                        <th>الحالة</th>
                        <th>الملفات</th>
                        <th>التاريخ</th>
                    </tr>
                </thead>
                <tbody id="logs-table">
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($log['service']); ?></td>
                            <td><?php echo htmlspecialchars($log['action']); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $log['status']; ?>">
                                    <?php echo $log['status']; ?>
                                </span>
                            </td>
                            <td><?php echo $log['files_affected']; ?></td>
                            <td><?php echo date('Y-m-d H:i', strtotime($log['started_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <script>
        function showMessage(text, type) {
            const msg = document.getElementById('message');
            msg.textContent = text;
            msg.className = type;
            msg.style.display = 'block';
            setTimeout(() => msg.style.display = 'none', 5000);
        }
        
        async function syncAction(action) {
            const btn = event.target;
            btn.disabled = true;
            btn.textContent = 'جاري التنفيذ...';
            
            try {
                const formData = new FormData();
                formData.append('action', action);
                
                const response = await fetch('sync_engine.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showMessage('✅ ' + result.message, 'success');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showMessage('❌ ' + result.message, 'error');
                }
            } catch (error) {
                showMessage('❌ خطأ في الاتصال: ' + error.message, 'error');
            } finally {
                btn.disabled = false;
                btn.textContent = btn.textContent.replace('جاري التنفيذ...', '');
            }
        }
    </script>
</body>
</html>
