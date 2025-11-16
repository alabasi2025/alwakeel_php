<?php
/**
 * سكريبت اختبار الاتصال بـ Ollama
 * يجب تشغيل Ollama محلياً قبل تشغيل هذا السكريبت
 */

require_once 'config.php';
require_once 'ai_engine.php';

echo "🧪 اختبار الاتصال بـ Ollama...\n\n";

// الحصول على اتصال قاعدة البيانات
$conn = getDBConnection();

// إنشاء محرك AI
$ai_engine = new AIEngine($conn);

// رسائل اختبار
$test_messages = [
    "مرحباً، كيف حالك؟",
    "ما هو GitHub؟",
    "اشرح لي كيفية استخدام Hostinger"
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
}

echo "✅ انتهى الاختبار!\n\n";

// عرض الإعدادات الحالية
echo "⚙️ الإعدادات الحالية:\n";
try {
    $stmt = $conn->query("SELECT * FROM integrations WHERE service_name = 'ollama'");
    $ollama_config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($ollama_config) {
        echo "✅ Ollama مفعّل: " . ($ollama_config['is_enabled'] === 'true' ? 'نعم' : 'لا') . "\n";
        $config = json_decode($ollama_config['config'], true);
        echo "🌐 URL: " . ($config['url'] ?? 'غير محدد') . "\n";
        echo "📦 النموذج الافتراضي: " . ($config['default_model'] ?? 'غير محدد') . "\n";
        echo "📁 مسار النماذج: " . ($config['models_path'] ?? 'غير محدد') . "\n";
        
        if (isset($config['available_models'])) {
            echo "📋 النماذج المتاحة:\n";
            foreach ($config['available_models'] as $model) {
                echo "   • $model\n";
            }
        }
    } else {
        echo "⚠️ لم يتم العثور على إعدادات Ollama\n";
        echo "💡 قم بتشغيل setup_ollama.php أولاً\n";
    }
    
} catch (PDOException $e) {
    echo "❌ خطأ في قراءة الإعدادات: " . $e->getMessage() . "\n";
}

echo "\n";
echo "📝 ملاحظات:\n";
echo "• تأكد من تشغيل Ollama محلياً: ollama serve\n";
echo "• تأكد من تحميل النموذج: ollama pull deepseek-r1:8b\n";
echo "• تأكد من تفعيل 'Expose to network' في إعدادات Ollama\n";
?>
