<?php
/**
 * سكريبت اختبار الاتصال بـ OpenAI API
 */

require_once 'config.php';
require_once 'ai_engine.php';

echo "🧪 اختبار الاتصال بـ OpenAI API...\n\n";

// الحصول على اتصال قاعدة البيانات
$conn = getDBConnection();

// إنشاء محرك AI
$ai_engine = new AIEngine($conn);

// رسائل اختبار
$test_messages = [
    "مرحباً، كيف حالك؟",
    "ما هو GitHub وكيف أستخدمه؟",
    "اشرح لي كيفية نشر موقع على Hostinger"
];

echo "📋 رسائل الاختبار:\n";
foreach ($test_messages as $i => $message) {
    echo ($i + 1) . ". $message\n";
}
echo "\n" . str_repeat("=", 60) . "\n\n";

// اختبار كل رسالة
foreach ($test_messages as $i => $message) {
    echo "🔹 اختبار " . ($i + 1) . ":\n";
    echo "📨 الرسالة: $message\n\n";
    
    try {
        $start_time = microtime(true);
        $response = $ai_engine->routeRequest($message);
        $end_time = microtime(true);
        $duration = round(($end_time - $start_time) * 1000, 2);
        
        if ($response['success']) {
            echo "✅ نجح!\n";
            echo "🤖 المحرك: {$response['engine']}\n";
            echo "📦 النموذج: {$response['model']}\n";
            if (isset($response['tokens'])) {
                echo "🎫 Tokens: {$response['tokens']}\n";
            }
            echo "⏱️ الوقت: {$duration}ms\n";
            echo "💬 الرد:\n";
            echo str_repeat("-", 60) . "\n";
            echo $response['message'] . "\n";
            echo str_repeat("-", 60) . "\n";
        } else {
            echo "❌ فشل!\n";
            echo "⚠️ الخطأ: {$response['message']}\n";
            if (isset($response['error'])) {
                echo "🔍 التفاصيل: {$response['error']}\n";
            }
        }
        
    } catch (Exception $e) {
        echo "❌ استثناء: " . $e->getMessage() . "\n";
    }
    
    echo "\n" . str_repeat("=", 60) . "\n\n";
    
    // انتظار قصير بين الطلبات
    if ($i < count($test_messages) - 1) {
        sleep(1);
    }
}

echo "✅ انتهى الاختبار!\n\n";

// عرض الإعدادات الحالية
echo "⚙️ الإعدادات الحالية:\n";
try {
    $stmt = $conn->query("SELECT * FROM integrations WHERE service_name = 'openai'");
    $openai_config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($openai_config) {
        echo "✅ OpenAI مفعّل: " . ($openai_config['is_enabled'] === 'true' ? 'نعم' : 'لا') . "\n";
        $config = json_decode($openai_config['config'], true);
        echo "📦 النموذج: " . ($config['model'] ?? 'غير محدد') . "\n";
        echo "🎫 Max Tokens: " . ($config['max_tokens'] ?? 'غير محدد') . "\n";
        echo "🌡️ Temperature: " . ($config['temperature'] ?? 'غير محدد') . "\n";
        echo "🔑 API Key: " . substr($config['api_key'] ?? '', 0, 20) . "...\n";
    } else {
        echo "⚠️ لم يتم العثور على إعدادات OpenAI\n";
        echo "💡 قم بتشغيل setup_openai.php أولاً\n";
    }
    
} catch (PDOException $e) {
    echo "❌ خطأ في قراءة الإعدادات: " . $e->getMessage() . "\n";
}

echo "\n";
echo "📝 ملاحظات:\n";
echo "• تأكد من صحة API Key\n";
echo "• تأكد من وجود رصيد في حساب OpenAI\n";
echo "• النموذج الافتراضي: gpt-3.5-turbo\n";
echo "• يمكنك تغيير الإعدادات من integrations.php\n";
?>
