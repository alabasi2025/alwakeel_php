<?php
require_once 'config.php';
require_once 'sidebar.php';

// معلومات النسخة الحالية
$current_version = '2.0.0';
$last_update = '2025-11-17';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سجل التحديثات - Changelog</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .timeline-item {
            position: relative;
            padding-right: 40px;
            border-right: 3px solid rgba(255, 255, 255, 0.3);
        }
        
        .timeline-item:last-child {
            border-right: none;
        }
        
        .timeline-dot {
            position: absolute;
            right: -9px;
            top: 0;
            width: 16px;
            height: 16px;
            background: white;
            border-radius: 50%;
            box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.3);
        }
        
        .timeline-dot.new {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 0 4px rgba(245, 87, 108, 0.3); }
            50% { box-shadow: 0 0 0 8px rgba(245, 87, 108, 0.1); }
        }
        
        .version-badge {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: bold;
            color: white;
        }
        
        .feature-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .feature-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 12px rgba(0, 0, 0, 0.15);
        }
        
        .feature-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-left: 15px;
        }
        
        .icon-new { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .icon-update { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .icon-fix { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
        .icon-security { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
    </style>
</head>
<body class="min-h-screen">
    <div class="flex">
        <!-- Sidebar -->
        <div class="w-64 flex-shrink-0">
            <!-- sidebar.php محمّل بالفعل -->
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-8">
            <!-- Header -->
            <div class="bg-white rounded-2xl shadow-2xl p-8 mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-4xl font-bold text-gray-800 mb-2">📋 سجل التحديثات</h1>
                        <p class="text-gray-600">تتبع جميع التحديثات والميزات الجديدة في النظام</p>
                    </div>
                    <div class="text-left">
                        <div class="version-badge mb-2">النسخة <?php echo $current_version; ?></div>
                        <div class="text-sm text-gray-600">آخر تحديث: <?php echo $last_update; ?></div>
                    </div>
                </div>
            </div>

            <!-- Timeline -->
            <div class="space-y-8">
                
                <!-- Version 2.0.0 - OpenAI Integration -->
                <div class="timeline-item">
                    <div class="timeline-dot new"></div>
                    <div class="feature-card">
                        <div class="flex items-start">
                            <div class="feature-icon icon-new">
                                🤖
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="text-2xl font-bold text-gray-800">النسخة 2.0.0</h3>
                                    <span class="text-sm text-gray-500">17 نوفمبر 2025</span>
                                </div>
                                
                                <h4 class="text-lg font-semibold text-purple-600 mb-3">🌐 ربط OpenAI API - الدردشة الذكية</h4>
                                
                                <div class="space-y-3 text-gray-700">
                                    <div class="flex items-start">
                                        <span class="text-green-500 ml-2">✅</span>
                                        <div>
                                            <strong>محرك OpenAI:</strong> دعم كامل لـ GPT-3.5-turbo للدردشة الذكية على السيرفر السحابي
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-start">
                                        <span class="text-green-500 ml-2">✅</span>
                                        <div>
                                            <strong>محرك Ollama:</strong> دعم النماذج المحلية (deepseek-r1:8b, llama3:8b, gemma3:1b) للتطوير المحلي
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-start">
                                        <span class="text-green-500 ml-2">✅</span>
                                        <div>
                                            <strong>نظام Fallback ذكي:</strong> أولوية تلقائية بين المحركات (OpenAI → Copilot → Ollama → Local)
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-start">
                                        <span class="text-green-500 ml-2">✅</span>
                                        <div>
                                            <strong>واجهة دردشة محسّنة:</strong> عرض اسم المحرك والنموذج المستخدم في كل رد
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-start">
                                        <span class="text-green-500 ml-2">✅</span>
                                        <div>
                                            <strong>أدوات اختبار:</strong> test_openai.php و test_ollama.php لاختبار الاتصال
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Version 1.5.0 - Sidebar -->
                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="feature-card">
                        <div class="flex items-start">
                            <div class="feature-icon icon-update">
                                🎨
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="text-2xl font-bold text-gray-800">النسخة 1.5.0</h3>
                                    <span class="text-sm text-gray-500">16 نوفمبر 2025</span>
                                </div>
                                
                                <h4 class="text-lg font-semibold text-blue-600 mb-3">🎨 القائمة الجانبية الموحدة</h4>
                                
                                <div class="space-y-3 text-gray-700">
                                    <div class="flex items-start">
                                        <span class="text-blue-500 ml-2">🔹</span>
                                        <div>
                                            <strong>Sidebar موحد:</strong> قائمة جانبية جميلة لجميع الصفحات
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-start">
                                        <span class="text-blue-500 ml-2">🔹</span>
                                        <div>
                                            <strong>تصميم عصري:</strong> تدرجات بنفسجية وأيقونات واضحة
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-start">
                                        <span class="text-blue-500 ml-2">🔹</span>
                                        <div>
                                            <strong>Responsive:</strong> يعمل بشكل ممتاز على جميع الأجهزة
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Version 1.0.0 - Initial Release -->
                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="feature-card">
                        <div class="flex items-start">
                            <div class="feature-icon icon-new">
                                🚀
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="text-2xl font-bold text-gray-800">النسخة 1.0.0</h3>
                                    <span class="text-sm text-gray-500">8 نوفمبر 2025</span>
                                </div>
                                
                                <h4 class="text-lg font-semibold text-purple-600 mb-3">🎉 الإطلاق الأول</h4>
                                
                                <div class="space-y-3 text-gray-700">
                                    <div class="flex items-start">
                                        <span class="text-purple-500 ml-2">⭐</span>
                                        <div>
                                            <strong>قاعدة البيانات:</strong> 6 جداول (commands, results, integrations, command_history, learning_data, sync_logs)
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-start">
                                        <span class="text-purple-500 ml-2">⭐</span>
                                        <div>
                                            <strong>الواجهات:</strong> alwakeel.php, chat.php, integrations.php, sync_engine.php
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-start">
                                        <span class="text-purple-500 ml-2">⭐</span>
                                        <div>
                                            <strong>محرك المزامنة:</strong> GitHub Pull/Push, Hostinger Deploy, Backup
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-start">
                                        <span class="text-purple-500 ml-2">⭐</span>
                                        <div>
                                            <strong>واجهة الدردشة:</strong> دردشة ذكية بلغة طبيعية مع اقتراحات تلقائية
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-start">
                                        <span class="text-purple-500 ml-2">⭐</span>
                                        <div>
                                            <strong>إدارة الربط:</strong> GitHub, Hostinger, Ollama, Copilot, Database
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-start">
                                        <span class="text-purple-500 ml-2">⭐</span>
                                        <div>
                                            <strong>النسخ الاحتياطي:</strong> نظام backup تلقائي مع تصدير/استيراد JSON
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Footer Stats -->
            <div class="mt-12 bg-white rounded-2xl shadow-2xl p-8">
                <h3 class="text-2xl font-bold text-gray-800 mb-6">📊 إحصائيات النظام</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="text-center">
                        <div class="text-4xl font-bold text-purple-600">2.0.0</div>
                        <div class="text-gray-600 mt-2">النسخة الحالية</div>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl font-bold text-blue-600">3</div>
                        <div class="text-gray-600 mt-2">إصدارات رئيسية</div>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl font-bold text-green-600">15+</div>
                        <div class="text-gray-600 mt-2">ميزة جديدة</div>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl font-bold text-pink-600">4</div>
                        <div class="text-gray-600 mt-2">محركات AI</div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</body>
</html>
