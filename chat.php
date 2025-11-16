<?php
require_once 'config.php';
require_once 'sidebar.php';
require_once 'ai_engine.php';

// معالجة طلبات AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'send_message':
            $message = $_POST['message'] ?? '';
            
            // حفظ الأمر في قاعدة البيانات
            try {
                $stmt = $conn->prepare("INSERT INTO commands (command_text, status) VALUES (:command_text, 'pending')");
                $stmt->execute([':command_text' => $message]);
                $command_id = $conn->lastInsertId();
                
                // معالجة الرسالة (سيتم ربطها بمحرك AI لاحقاً)
                $response = processMessage($message, $command_id, $conn);
                
                echo json_encode([
                    'success' => true,
                    'response' => $response,
                    'command_id' => $command_id
                ], JSON_UNESCAPED_UNICODE);
                
            } catch (PDOException $e) {
                echo json_encode([
                    'success' => false,
                    'error' => $e->getMessage()
                ], JSON_UNESCAPED_UNICODE);
            }
            exit;
            
        case 'get_history':
            try {
                $stmt = $conn->query("
                    SELECT c.*, r.result_text 
                    FROM commands c 
                    LEFT JOIN results r ON c.id = r.command_id 
                    ORDER BY c.created_at DESC 
                    LIMIT 50
                ");
                $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'success' => true,
                    'history' => $history
                ], JSON_UNESCAPED_UNICODE);
                
            } catch (PDOException $e) {
                echo json_encode([
                    'success' => false,
                    'error' => $e->getMessage()
                ], JSON_UNESCAPED_UNICODE);
            }
            exit;
            
        case 'get_suggestions':
            try {
                $stmt = $conn->query("
                    SELECT suggestion, confidence, category 
                    FROM learning_data 
                    ORDER BY confidence DESC, frequency DESC 
                    LIMIT 5
                ");
                $suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'success' => true,
                    'suggestions' => $suggestions
                ], JSON_UNESCAPED_UNICODE);
                
            } catch (PDOException $e) {
                echo json_encode([
                    'success' => false,
                    'error' => $e->getMessage()
                ], JSON_UNESCAPED_UNICODE);
            }
            exit;
    }
}

/**
 * معالجة الرسالة وإرجاع الرد عبر محرك AI
 */
function processMessage($message, $command_id, $conn) {
    // محاولة استخدام محرك AI
    try {
        $ai_engine = new AIEngine($conn);
        $ai_response = $ai_engine->routeRequest($message);
        
        if ($ai_response['success']) {
            $response = $ai_response['message'];
            $response .= "\n\n🤖 *محرك: {$ai_response['engine']} | نموذج: {$ai_response['model']}*";
        } else {
            // Fallback إلى المعالجة المحلية
            $response = processMessageLocal($message);
        }
    } catch (Exception $e) {
        error_log("خطأ AI Engine: " . $e->getMessage());
        $response = processMessageLocal($message);
    }
    
    // حفظ الرد في قاعدة البيانات
    try {
        $stmt = $conn->prepare("INSERT INTO results (command_id, result_text) VALUES (:command_id, :result_text)");
        $stmt->execute([
            ':command_id' => $command_id,
            ':result_text' => $response
        ]);
        
        // تحديث حالة الأمر
        $stmt = $conn->prepare("UPDATE commands SET status = 'completed' WHERE id = :id");
        $stmt->execute([':id' => $command_id]);
        
    } catch (PDOException $e) {
        error_log("خطأ في حفظ الرد: " . $e->getMessage());
    }
    
    return $response;
}

/**
 * معالجة محلية بسيطة (Fallback)
 */
function processMessageLocal($message) {
    $message_lower = mb_strtolower($message);
    
    // تحليل بسيط للرسالة
    if (strpos($message_lower, 'مرحبا') !== false || strpos($message_lower, 'السلام') !== false) {
        $response = "مرحباً بك! أنا الوكيل الذكي، كيف يمكنني مساعدتك اليوم؟";
        
    } elseif (strpos($message_lower, 'github') !== false || strpos($message_lower, 'جيت') !== false) {
        $response = "يمكنني مساعدتك في:\n- سحب التحديثات من GitHub\n- رفع التحديثات إلى GitHub\n- إدارة المستودع\n\nما الذي تريد القيام به؟";
        
    } elseif (strpos($message_lower, 'hostinger') !== false || strpos($message_lower, 'نشر') !== false) {
        $response = "يمكنني نشر المشروع على Hostinger عبر FTP. هل تريد المتابعة؟";
        
    } elseif (strpos($message_lower, 'backup') !== false || strpos($message_lower, 'نسخة احتياطية') !== false) {
        $response = "سأقوم بإنشاء نسخة احتياطية من المشروع. يرجى الانتظار...";
        
    } elseif (strpos($message_lower, 'sql') !== false || strpos($message_lower, 'قاعدة') !== false) {
        $response = "يمكنني تنفيذ استعلامات SQL. ما الاستعلام الذي تريد تنفيذه؟";
        
    } elseif (strpos($message_lower, 'help') !== false || strpos($message_lower, 'مساعدة') !== false) {
        $response = "📋 الأوامر المتاحة:\n\n" .
                   "🔹 GitHub: سحب/رفع التحديثات\n" .
                   "🔹 Hostinger: نشر المشروع\n" .
                   "🔹 Backup: نسخة احتياطية\n" .
                   "🔹 SQL: تنفيذ استعلامات\n" .
                   "🔹 Sync: عرض سجل المزامنة\n\n" .
                   "اكتب أمرك بلغة طبيعية وسأساعدك!";
        
    } else {
        $response = "فهمت رسالتك: \"$message\"\n\n" .
                   "للحصول على أفضل النتائج:\n" .
                   "• فعّل Ollama للذكاء الاصطناعي المحلي\n" .
                   "• فعّل Copilot للتحليل المتقدم\n" .
                   "• استخدم الأوامر المباشرة (GitHub, SQL, Backup)";
    }
    
    return $response;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الدردشة الذكية - Smart Chat</title>
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
        }
        
        /* تعديل لتفادي التداخل مع Sidebar */
        @media (min-width: 769px) {
            body {
                margin-right: 300px;
            }
        }
        
        body {
            flex-direction: column;
        }
        
        .header {
            background: white;
            padding: 20px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            color: #667eea;
            font-size: 24px;
        }
        
        .nav-links a {
            display: inline-block;
            padding: 8px 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 15px;
            margin-left: 10px;
            font-size: 13px;
        }
        
        .chat-container {
            flex: 1;
            display: flex;
            max-width: 1400px;
            width: 100%;
            margin: 20px auto;
            gap: 20px;
            padding: 0 20px;
            overflow: hidden;
        }
        
        .sidebar {
            width: 300px;
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow-y: auto;
        }
        
        .sidebar h3 {
            color: #667eea;
            margin-bottom: 15px;
            font-size: 18px;
        }
        
        .suggestion-item {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s;
            border-right: 3px solid #667eea;
        }
        
        .suggestion-item:hover {
            background: #e9ecef;
            transform: translateX(-5px);
        }
        
        .suggestion-item .text {
            font-size: 13px;
            color: #333;
            margin-bottom: 5px;
        }
        
        .suggestion-item .meta {
            font-size: 11px;
            color: #999;
        }
        
        .chat-main {
            flex: 1;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .messages-container {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background: #f8f9fa;
        }
        
        .message {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }
        
        .message.user {
            flex-direction: row-reverse;
        }
        
        .message-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }
        
        .message.user .message-avatar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .message.bot .message-avatar {
            background: #e9ecef;
        }
        
        .message-content {
            max-width: 70%;
            padding: 15px 20px;
            border-radius: 15px;
            font-size: 14px;
            line-height: 1.6;
            white-space: pre-wrap;
        }
        
        .message.user .message-content {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-bottom-left-radius: 5px;
        }
        
        .message.bot .message-content {
            background: white;
            color: #333;
            border-bottom-right-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .message-time {
            font-size: 11px;
            color: #999;
            margin-top: 5px;
        }
        
        .input-container {
            padding: 20px;
            background: white;
            border-top: 1px solid #eee;
        }
        
        .input-wrapper {
            display: flex;
            gap: 10px;
        }
        
        #message-input {
            flex: 1;
            padding: 15px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.3s;
        }
        
        #message-input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        #send-btn {
            padding: 15px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: transform 0.2s;
        }
        
        #send-btn:hover {
            transform: translateY(-2px);
        }
        
        #send-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .typing-indicator {
            display: none;
            padding: 15px 20px;
            background: white;
            border-radius: 15px;
            width: fit-content;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .typing-indicator span {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #667eea;
            margin: 0 2px;
            animation: typing 1.4s infinite;
        }
        
        .typing-indicator span:nth-child(2) {
            animation-delay: 0.2s;
        }
        
        .typing-indicator span:nth-child(3) {
            animation-delay: 0.4s;
        }
        
        @keyframes typing {
            0%, 60%, 100% {
                transform: translateY(0);
            }
            30% {
                transform: translateY(-10px);
            }
        }
        
        .quick-actions {
            display: flex;
            gap: 10px;
            padding: 10px 20px;
            background: #f8f9fa;
            overflow-x: auto;
        }
        
        .quick-action {
            padding: 8px 16px;
            background: white;
            border: 2px solid #667eea;
            color: #667eea;
            border-radius: 20px;
            cursor: pointer;
            font-size: 12px;
            white-space: nowrap;
            transition: all 0.3s;
        }
        
        .quick-action:hover {
            background: #667eea;
            color: white;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>💬 الدردشة الذكية</h1>
        <div class="nav-links">
            <a href="alwakeel.php">🏠 الرئيسية</a>
            <a href="integrations.php">⚙️ الإعدادات</a>
            <a href="sync_engine.php">🔄 المزامنة</a>
        </div>
    </div>
    
    <div class="chat-container">
        <div class="sidebar">
            <h3>💡 اقتراحات ذكية</h3>
            <div id="suggestions-list">
                <div class="suggestion-item" onclick="useSuggestion(this)">
                    <div class="text">استخدم SELECT مع أعمدة محددة بدلاً من *</div>
                    <div class="meta">SQL • ثقة: 85%</div>
                </div>
                <div class="suggestion-item" onclick="useSuggestion(this)">
                    <div class="text">تأكد من عمل git pull قبل git push</div>
                    <div class="meta">Git • ثقة: 90%</div>
                </div>
                <div class="suggestion-item" onclick="useSuggestion(this)">
                    <div class="text">يُنصح بعمل نسخة احتياطية قبل التحديث</div>
                    <div class="meta">Deployment • ثقة: 95%</div>
                </div>
            </div>
        </div>
        
        <div class="chat-main">
            <div class="quick-actions">
                <div class="quick-action" onclick="sendQuickMessage('سحب التحديثات من GitHub')">🔽 سحب من GitHub</div>
                <div class="quick-action" onclick="sendQuickMessage('رفع التحديثات إلى GitHub')">🔼 رفع إلى GitHub</div>
                <div class="quick-action" onclick="sendQuickMessage('نشر على Hostinger')">🚀 نشر</div>
                <div class="quick-action" onclick="sendQuickMessage('إنشاء نسخة احتياطية')">💾 نسخة احتياطية</div>
                <div class="quick-action" onclick="sendQuickMessage('عرض سجل المزامنة')">📊 السجل</div>
            </div>
            
            <div class="messages-container" id="messages">
                <div class="message bot">
                    <div class="message-avatar">🤖</div>
                    <div>
                        <div class="message-content">
مرحباً! أنا الوكيل الذكي 🤖

يمكنني مساعدتك في:
• إدارة GitHub (سحب/رفع التحديثات)
• النشر على Hostinger
• إنشاء نسخ احتياطية
• تنفيذ استعلامات SQL
• عرض السجلات والإحصائيات

اكتب أمرك بلغة طبيعية أو استخدم الأزرار السريعة أعلاه!
                        </div>
                        <div class="message-time"><?php echo date('H:i'); ?></div>
                    </div>
                </div>
            </div>
            
            <div class="input-container">
                <div class="input-wrapper">
                    <input type="text" id="message-input" placeholder="اكتب رسالتك هنا..." 
                           onkeypress="if(event.key==='Enter') sendMessage()">
                    <button id="send-btn" onclick="sendMessage()">إرسال 📤</button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        const messagesContainer = document.getElementById('messages');
        const messageInput = document.getElementById('message-input');
        const sendBtn = document.getElementById('send-btn');
        
        function addMessage(text, isUser = false) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${isUser ? 'user' : 'bot'}`;
            
            const time = new Date().toLocaleTimeString('ar-SA', { hour: '2-digit', minute: '2-digit' });
            
            messageDiv.innerHTML = `
                <div class="message-avatar">${isUser ? '👤' : '🤖'}</div>
                <div>
                    <div class="message-content">${text}</div>
                    <div class="message-time">${time}</div>
                </div>
            `;
            
            messagesContainer.appendChild(messageDiv);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
        
        function showTyping() {
            const typing = document.createElement('div');
            typing.className = 'typing-indicator';
            typing.id = 'typing';
            typing.innerHTML = '<span></span><span></span><span></span>';
            typing.style.display = 'block';
            messagesContainer.appendChild(typing);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
        
        function hideTyping() {
            const typing = document.getElementById('typing');
            if (typing) typing.remove();
        }
        
        async function sendMessage() {
            const message = messageInput.value.trim();
            if (!message) return;
            
            // إضافة رسالة المستخدم
            addMessage(message, true);
            messageInput.value = '';
            sendBtn.disabled = true;
            
            // إظهار مؤشر الكتابة
            showTyping();
            
            try {
                const formData = new FormData();
                formData.append('action', 'send_message');
                formData.append('message', message);
                
                const response = await fetch('chat.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                hideTyping();
                
                if (result.success) {
                    addMessage(result.response, false);
                } else {
                    addMessage('عذراً، حدث خطأ في معالجة رسالتك.', false);
                }
            } catch (error) {
                hideTyping();
                addMessage('عذراً، حدث خطأ في الاتصال.', false);
            } finally {
                sendBtn.disabled = false;
                messageInput.focus();
            }
        }
        
        function sendQuickMessage(message) {
            messageInput.value = message;
            sendMessage();
        }
        
        function useSuggestion(element) {
            const text = element.querySelector('.text').textContent;
            messageInput.value = text;
            messageInput.focus();
        }
        
        // تحميل الاقتراحات
        async function loadSuggestions() {
            try {
                const formData = new FormData();
                formData.append('action', 'get_suggestions');
                
                const response = await fetch('chat.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success && result.suggestions.length > 0) {
                    const list = document.getElementById('suggestions-list');
                    list.innerHTML = '';
                    
                    result.suggestions.forEach(suggestion => {
                        const item = document.createElement('div');
                        item.className = 'suggestion-item';
                        item.onclick = function() { useSuggestion(this); };
                        item.innerHTML = `
                            <div class="text">${suggestion.suggestion}</div>
                            <div class="meta">${suggestion.category || 'عام'} • ثقة: ${suggestion.confidence}%</div>
                        `;
                        list.appendChild(item);
                    });
                }
            } catch (error) {
                console.error('خطأ في تحميل الاقتراحات:', error);
            }
        }
        
        // تحميل الاقتراحات عند فتح الصفحة
        loadSuggestions();
        
        // التركيز على حقل الإدخال
        messageInput.focus();
    </script>
</body>
</html>
