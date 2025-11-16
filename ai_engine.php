<?php
require_once 'config.php';

/**
 * محرك الذكاء الاصطناعي - AI Engine
 * يدير التفاعل مع Ollama (محلي) و Copilot (سحابي) و LangChain
 */

class AIEngine {
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
     * اختيار محرك AI المناسب بناءً على نوع الطلب
     */
    public function routeRequest($message, $context = []) {
        $message_lower = mb_strtolower($message);
        
        // تحليل بسيط لتحديد المحرك المناسب
        $is_complex = strlen($message) > 200 || 
                     strpos($message_lower, 'تحليل') !== false ||
                     strpos($message_lower, 'شرح') !== false ||
                     strpos($message_lower, 'اقتراح') !== false;
        
        // استخدام Copilot للطلبات المعقدة، Ollama للبسيطة
        if ($is_complex && isset($this->integrations['copilot'])) {
            return $this->processCopilot($message, $context);
        } elseif (isset($this->integrations['ollama'])) {
            return $this->processOllama($message, $context);
        } else {
            return $this->processLocal($message, $context);
        }
    }
    
    /**
     * معالجة باستخدام Ollama المحلي
     */
    private function processOllama($message, $context = []) {
        if (!isset($this->integrations['ollama'])) {
            return [
                'success' => false,
                'message' => 'Ollama غير مفعّل',
                'engine' => 'none'
            ];
        }
        
        $ollama_url = $this->integrations['ollama']['url'];
        
        try {
            // بناء السياق
            $prompt = $this->buildPrompt($message, $context);
            
            // استدعاء Ollama API
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $ollama_url . '/api/generate');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                'model' => 'llama2', // أو أي نموذج آخر متاح
                'prompt' => $prompt,
                'stream' => false
            ]));
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($http_code === 200) {
                $result = json_decode($response, true);
                
                return [
                    'success' => true,
                    'message' => $result['response'] ?? 'لا يوجد رد',
                    'engine' => 'ollama',
                    'model' => 'llama2'
                ];
            } else {
                throw new Exception("فشل الاتصال بـ Ollama (HTTP {$http_code})");
            }
            
        } catch (Exception $e) {
            error_log("خطأ Ollama: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'فشل الاتصال بـ Ollama المحلي',
                'error' => $e->getMessage(),
                'engine' => 'ollama'
            ];
        }
    }
    
    /**
     * معالجة باستخدام Copilot API (OpenAI-compatible)
     */
    private function processCopilot($message, $context = []) {
        if (!isset($this->integrations['copilot'])) {
            return [
                'success' => false,
                'message' => 'Copilot غير مفعّل',
                'engine' => 'none'
            ];
        }
        
        $api_key = $this->integrations['copilot']['api_key'];
        
        try {
            // بناء الرسائل
            $messages = [
                [
                    'role' => 'system',
                    'content' => 'أنت وكيل ذكي متخصص في إدارة المشاريع والتطوير. تساعد المستخدمين في GitHub، Hostinger، SQL، والنسخ الاحتياطي.'
                ],
                [
                    'role' => 'user',
                    'content' => $message
                ]
            ];
            
            // إضافة السياق إذا وُجد
            if (!empty($context)) {
                array_splice($messages, 1, 0, [[
                    'role' => 'assistant',
                    'content' => 'السياق السابق: ' . json_encode($context, JSON_UNESCAPED_UNICODE)
                ]]);
            }
            
            // استدعاء OpenAI API (أو أي API متوافق)
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $api_key
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                'model' => 'gpt-3.5-turbo',
                'messages' => $messages,
                'temperature' => 0.7,
                'max_tokens' => 500
            ]));
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($http_code === 200) {
                $result = json_decode($response, true);
                
                return [
                    'success' => true,
                    'message' => $result['choices'][0]['message']['content'] ?? 'لا يوجد رد',
                    'engine' => 'copilot',
                    'model' => 'gpt-3.5-turbo',
                    'tokens' => $result['usage']['total_tokens'] ?? 0
                ];
            } else {
                throw new Exception("فشل الاتصال بـ Copilot API (HTTP {$http_code})");
            }
            
        } catch (Exception $e) {
            error_log("خطأ Copilot: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'فشل الاتصال بـ Copilot API',
                'error' => $e->getMessage(),
                'engine' => 'copilot'
            ];
        }
    }
    
    /**
     * معالجة محلية بسيطة (fallback)
     */
    private function processLocal($message, $context = []) {
        $message_lower = mb_strtolower($message);
        $response = '';
        
        // تحليل بسيط بناءً على الكلمات المفتاحية
        if (strpos($message_lower, 'github') !== false) {
            $response = "🐙 **GitHub Operations**\n\n";
            $response .= "يمكنني مساعدتك في:\n";
            $response .= "• سحب التحديثات (Pull)\n";
            $response .= "• رفع التحديثات (Push)\n";
            $response .= "• إدارة الفروع (Branches)\n\n";
            $response .= "استخدم الأوامر السريعة أو اذهب إلى صفحة المزامنة.";
            
        } elseif (strpos($message_lower, 'hostinger') !== false || strpos($message_lower, 'نشر') !== false) {
            $response = "🌐 **Hostinger Deployment**\n\n";
            $response .= "يمكنني نشر مشروعك على Hostinger عبر FTP.\n\n";
            $response .= "تأكد من:\n";
            $response .= "• تفعيل إعدادات Hostinger\n";
            $response .= "• إدخال بيانات FTP الصحيحة\n";
            $response .= "• عمل نسخة احتياطية قبل النشر";
            
        } elseif (strpos($message_lower, 'sql') !== false || strpos($message_lower, 'قاعدة') !== false) {
            $response = "🗄️ **SQL Operations**\n\n";
            $response .= "يمكنني تنفيذ استعلامات SQL.\n\n";
            $response .= "أمثلة:\n";
            $response .= "• SELECT * FROM commands\n";
            $response .= "• SHOW TABLES\n";
            $response .= "• INSERT INTO ...\n\n";
            $response .= "⚠️ تحذير: كن حذراً مع استعلامات DELETE و DROP";
            
        } elseif (strpos($message_lower, 'backup') !== false || strpos($message_lower, 'نسخة') !== false) {
            $response = "💾 **Backup System**\n\n";
            $response .= "النسخ الاحتياطي يشمل:\n";
            $response .= "• جميع ملفات PHP\n";
            $response .= "• قاعدة البيانات\n";
            $response .= "• الإعدادات\n\n";
            $response .= "يتم حفظ النسخ في مجلد /backups";
            
        } elseif (strpos($message_lower, 'help') !== false || strpos($message_lower, 'مساعدة') !== false) {
            $response = "📚 **دليل الاستخدام**\n\n";
            $response .= "الأوامر المتاحة:\n\n";
            $response .= "🔹 **GitHub**: إدارة المستودع\n";
            $response .= "🔹 **Hostinger**: نشر المشروع\n";
            $response .= "🔹 **SQL**: تنفيذ استعلامات\n";
            $response .= "🔹 **Backup**: نسخ احتياطية\n";
            $response .= "🔹 **Sync**: سجل المزامنة\n\n";
            $response .= "💡 نصيحة: استخدم الأزرار السريعة للوصول الفوري!";
            
        } else {
            // التعلم من الأوامر السابقة
            $suggestions = $this->getSuggestions($message);
            
            if (!empty($suggestions)) {
                $response = "💡 **اقتراحات بناءً على رسالتك:**\n\n";
                foreach ($suggestions as $suggestion) {
                    $response .= "• {$suggestion['suggestion']}\n";
                }
            } else {
                $response = "فهمت رسالتك: \"$message\"\n\n";
                $response .= "للحصول على أفضل النتائج:\n";
                $response .= "• فعّل Ollama للذكاء الاصطناعي المحلي\n";
                $response .= "• فعّل Copilot للتحليل المتقدم\n";
                $response .= "• استخدم الأوامر المباشرة (GitHub, SQL, Backup)";
            }
        }
        
        return [
            'success' => true,
            'message' => $response,
            'engine' => 'local',
            'model' => 'rule-based'
        ];
    }
    
    /**
     * بناء Prompt مع السياق
     */
    private function buildPrompt($message, $context = []) {
        $prompt = "أنت وكيل ذكي متخصص في إدارة المشاريع والتطوير.\n\n";
        
        if (!empty($context)) {
            $prompt .= "السياق السابق:\n";
            $prompt .= json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n\n";
        }
        
        $prompt .= "السؤال: $message\n\n";
        $prompt .= "الرد (بالعربية):";
        
        return $prompt;
    }
    
    /**
     * الحصول على اقتراحات من قاعدة التعلم
     */
    private function getSuggestions($message) {
        try {
            $message_lower = mb_strtolower($message);
            
            $stmt = $this->conn->prepare("
                SELECT suggestion, confidence, category 
                FROM learning_data 
                WHERE LOWER(pattern) LIKE :pattern 
                ORDER BY confidence DESC, frequency DESC 
                LIMIT 3
            ");
            
            $stmt->execute([':pattern' => "%$message_lower%"]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("خطأ في جلب الاقتراحات: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * التعلم من الأوامر (تحديث قاعدة التعلم)
     */
    public function learn($command, $result, $success) {
        try {
            // استخراج الأنماط
            $patterns = $this->extractPatterns($command);
            
            foreach ($patterns as $pattern) {
                // التحقق من وجود النمط
                $stmt = $this->conn->prepare("
                    SELECT id, frequency, confidence 
                    FROM learning_data 
                    WHERE pattern = :pattern
                ");
                $stmt->execute([':pattern' => $pattern]);
                $existing = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($existing) {
                    // تحديث النمط الموجود
                    $new_frequency = $existing['frequency'] + 1;
                    $new_confidence = min(100, $existing['confidence'] + ($success ? 5 : -3));
                    
                    $stmt = $this->conn->prepare("
                        UPDATE learning_data 
                        SET frequency = :frequency, 
                            confidence = :confidence,
                            updated_at = CURRENT_TIMESTAMP
                        WHERE id = :id
                    ");
                    $stmt->execute([
                        ':frequency' => $new_frequency,
                        ':confidence' => $new_confidence,
                        ':id' => $existing['id']
                    ]);
                } else {
                    // إضافة نمط جديد
                    $category = $this->categorizePattern($pattern);
                    
                    $stmt = $this->conn->prepare("
                        INSERT INTO learning_data (pattern, suggestion, frequency, confidence, category)
                        VALUES (:pattern, :suggestion, 1, 50, :category)
                    ");
                    $stmt->execute([
                        ':pattern' => $pattern,
                        ':suggestion' => $this->generateSuggestion($pattern, $result),
                        ':category' => $category
                    ]);
                }
            }
            
        } catch (PDOException $e) {
            error_log("خطأ في التعلم: " . $e->getMessage());
        }
    }
    
    /**
     * استخراج الأنماط من الأمر
     */
    private function extractPatterns($command) {
        $patterns = [];
        $command_lower = mb_strtolower($command);
        
        // الكلمات المفتاحية
        $keywords = ['github', 'git', 'pull', 'push', 'sql', 'select', 'insert', 
                    'backup', 'نسخة', 'hostinger', 'نشر', 'deploy'];
        
        foreach ($keywords as $keyword) {
            if (strpos($command_lower, $keyword) !== false) {
                $patterns[] = $keyword;
            }
        }
        
        return array_unique($patterns);
    }
    
    /**
     * تصنيف النمط
     */
    private function categorizePattern($pattern) {
        $categories = [
            'sql' => ['sql', 'select', 'insert', 'update', 'delete', 'database'],
            'git' => ['git', 'github', 'pull', 'push', 'commit', 'branch'],
            'deployment' => ['deploy', 'hostinger', 'نشر', 'ftp'],
            'backup' => ['backup', 'نسخة', 'احتياطي']
        ];
        
        foreach ($categories as $category => $keywords) {
            if (in_array(mb_strtolower($pattern), $keywords)) {
                return $category;
            }
        }
        
        return 'general';
    }
    
    /**
     * توليد اقتراح من النمط والنتيجة
     */
    private function generateSuggestion($pattern, $result) {
        return "عند استخدام '$pattern'، تذكر: " . mb_substr($result, 0, 100);
    }
    
    /**
     * حفظ سجل الأمر في command_history
     */
    public function logCommand($command_id, $context, $execution_time, $engine, $success, $error = null) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO command_history 
                (command_id, context, execution_time, ai_engine, success, error_message)
                VALUES (:command_id, :context, :execution_time, :ai_engine, :success, :error_message)
            ");
            
            $stmt->execute([
                ':command_id' => $command_id,
                ':context' => json_encode($context, JSON_UNESCAPED_UNICODE),
                ':execution_time' => $execution_time,
                ':ai_engine' => $engine,
                ':success' => $success ? 'true' : 'false',
                ':error_message' => $error
            ]);
            
        } catch (PDOException $e) {
            error_log("خطأ في تسجيل الأمر: " . $e->getMessage());
        }
    }
}

// معالجة طلبات AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    $engine = new AIEngine($conn);
    
    switch ($_POST['action']) {
        case 'process':
            $message = $_POST['message'] ?? '';
            $context = json_decode($_POST['context'] ?? '[]', true);
            
            $start_time = microtime(true);
            $result = $engine->routeRequest($message, $context);
            $execution_time = round((microtime(true) - $start_time) * 1000);
            
            echo json_encode(array_merge($result, [
                'execution_time' => $execution_time
            ]), JSON_UNESCAPED_UNICODE);
            break;
            
        case 'test_ollama':
            $result = $engine->processOllama('مرحبا، كيف حالك؟');
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            break;
            
        case 'test_copilot':
            $result = $engine->processCopilot('ما هي أفضل ممارسات Git؟');
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'عملية غير معروفة'
            ], JSON_UNESCAPED_UNICODE);
    }
    
    exit;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>محرك الذكاء الاصطناعي - AI Engine</title>
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
            max-width: 1000px;
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
        
        .nav-links a {
            display: inline-block;
            padding: 10px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 20px;
            margin-left: 10px;
            font-size: 14px;
            margin-top: 15px;
        }
        
        .test-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .test-section h2 {
            color: #667eea;
            margin-bottom: 20px;
        }
        
        .test-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .test-card {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 20px;
        }
        
        .test-card h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 18px;
        }
        
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 14px;
            width: 100%;
            margin-bottom: 10px;
        }
        
        .result {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            font-size: 13px;
            white-space: pre-wrap;
            display: none;
        }
        
        .info-box {
            background: #e3f2fd;
            padding: 20px;
            border-radius: 10px;
            border-right: 4px solid #2196f3;
        }
        
        .info-box h3 {
            color: #1976d2;
            margin-bottom: 10px;
        }
        
        .info-box ul {
            margin-right: 20px;
        }
        
        .info-box li {
            margin-bottom: 8px;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🤖 محرك الذكاء الاصطناعي</h1>
            <p>AI Engine - اختبار وإدارة محركات الذكاء الاصطناعي</p>
            <div class="nav-links">
                <a href="chat.php">💬 الدردشة</a>
                <a href="integrations.php">⚙️ الإعدادات</a>
                <a href="alwakeel.php">🏠 الرئيسية</a>
            </div>
        </div>
        
        <div class="test-section">
            <h2>🧪 اختبار المحركات</h2>
            <div class="test-grid">
                <div class="test-card">
                    <h3>🦙 Ollama (محلي)</h3>
                    <p style="font-size: 13px; color: #666; margin-bottom: 15px;">
                        اختبار الاتصال بـ Ollama المحلي
                    </p>
                    <button class="btn" onclick="testEngine('ollama')">اختبار Ollama</button>
                    <div class="result" id="ollama-result"></div>
                </div>
                
                <div class="test-card">
                    <h3>🤖 Copilot (سحابي)</h3>
                    <p style="font-size: 13px; color: #666; margin-bottom: 15px;">
                        اختبار الاتصال بـ Copilot API
                    </p>
                    <button class="btn" onclick="testEngine('copilot')">اختبار Copilot</button>
                    <div class="result" id="copilot-result"></div>
                </div>
            </div>
        </div>
        
        <div class="test-section">
            <div class="info-box">
                <h3>📚 كيفية الاستخدام</h3>
                <ul>
                    <li><strong>Ollama:</strong> قم بتثبيت Ollama محلياً من <a href="https://ollama.ai" target="_blank">ollama.ai</a></li>
                    <li><strong>Copilot:</strong> احصل على API Key من OpenAI أو أي خدمة متوافقة</li>
                    <li><strong>التكامل:</strong> فعّل الخدمات من صفحة الإعدادات</li>
                    <li><strong>الدردشة:</strong> استخدم واجهة الدردشة للتفاعل مع المحركات</li>
                </ul>
            </div>
        </div>
    </div>
    
    <script>
        async function testEngine(engine) {
            const resultDiv = document.getElementById(engine + '-result');
            const btn = event.target;
            
            btn.disabled = true;
            btn.textContent = 'جاري الاختبار...';
            resultDiv.style.display = 'block';
            resultDiv.textContent = '⏳ جاري الاتصال...';
            
            try {
                const formData = new FormData();
                formData.append('action', 'test_' + engine);
                
                const response = await fetch('ai_engine.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    resultDiv.textContent = '✅ نجح الاتصال!\n\n' +
                                          'المحرك: ' + result.engine + '\n' +
                                          'النموذج: ' + result.model + '\n\n' +
                                          'الرد:\n' + result.message;
                } else {
                    resultDiv.textContent = '❌ فشل الاتصال\n\n' +
                                          'الخطأ: ' + (result.error || result.message);
                }
            } catch (error) {
                resultDiv.textContent = '❌ خطأ في الاتصال:\n' + error.message;
            } finally {
                btn.disabled = false;
                btn.textContent = 'اختبار ' + (engine === 'ollama' ? 'Ollama' : 'Copilot');
            }
        }
    </script>
</body>
</html>
