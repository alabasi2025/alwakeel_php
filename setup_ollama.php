<?php
require_once 'config.php';

// الحصول على اتصال قاعدة البيانات
$conn = getDBConnection();

// إعدادات Ollama
$config = json_encode([
    'url' => 'http://localhost:11434',
    'models_path' => 'D:\\AAAAAA\\ollama\\models',
    'default_model' => 'deepseek-r1:8b',
    'available_models' => [
        'deepseek-r1:8b',
        'gemma3:1b',
        'llama3:8b',
        'gpt-oss:120b-cloud'
    ]
], JSON_UNESCAPED_UNICODE);

try {
    $stmt = $conn->prepare("
        INSERT INTO integrations (service_name, is_enabled, config) 
        VALUES ('ollama', 'true', :config)
        ON DUPLICATE KEY UPDATE 
            is_enabled = 'true',
            config = :config,
            updated_at = CURRENT_TIMESTAMP
    ");
    
    $stmt->execute([':config' => $config]);
    
    echo "✅ تم حفظ إعدادات Ollama بنجاح!\n";
    echo "URL: http://localhost:11434\n";
    echo "Models Path: D:\\AAAAAA\\ollama\\models\n";
    echo "Default Model: deepseek-r1:8b\n";
    echo "\nالنماذج المتاحة:\n";
    echo "- deepseek-r1:8b\n";
    echo "- gemma3:1b\n";
    echo "- llama3:8b\n";
    echo "- gpt-oss:120b-cloud\n";
    
} catch (PDOException $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
}
?>
