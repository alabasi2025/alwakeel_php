<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    private $apiKey;
    private $apiEndpoint = 'https://generativelanguage.googleapis.com';
    private $model = 'gemini-2.5-flash';
    
    public function __construct($apiKey = null)
    {
        // إذا لم يتم تمرير مفتاح، نستخدم مفتاح مجاني افتراضي
        $this->apiKey = $apiKey ?: $this->getDefaultApiKey();
    }
    
    /**
     * الحصول على مفتاح API افتراضي (مجاني)
     * يمكن للمستخدم إضافة مفتاحه الخاص من صفحة التكاملات
     */
    private function getDefaultApiKey()
    {
        // هذا مفتاح تجريبي - يجب على المستخدم الحصول على مفتاحه من:
        // https://makersuite.google.com/app/apikey
        return env('GEMINI_API_KEY', '');
    }
    
    /**
     * إرسال رسالة والحصول على رد مباشر
     * 
     * @param string $systemPrompt رسالة النظام
     * @param string $userMessage رسالة المستخدم
     * @return array
     */
    public function chat($systemPrompt, $userMessage)
    {
        try {
            // دمج رسالة النظام مع رسالة المستخدم
            $fullPrompt = $systemPrompt . "\n\n" . $userMessage;
            
            $response = Http::timeout(60)->post(
                $this->apiEndpoint . '/v1beta/models/' . $this->model . ':generateContent?key=' . $this->apiKey,
                [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $fullPrompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'maxOutputTokens' => 2048,
                    ]
                ]
            );
            
            if ($response->successful()) {
                $data = $response->json();
                
                // استخراج الرد من الاستجابة
                $reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'لم يتم الحصول على رد';
                
                return [
                    'success' => true,
                    'response' => $reply,
                    'data' => $data
                ];
            } else {
                Log::error('Gemini API Error: ' . $response->body());
                return [
                    'success' => false,
                    'error' => 'خطأ في الاتصال بـ Gemini: ' . $response->status(),
                    'details' => $response->body()
                ];
            }
        } catch (\Exception $e) {
            Log::error('Gemini API Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * اختبار الاتصال بـ Gemini API
     * 
     * @return array
     */
    public function testConnection()
    {
        return $this->chat('أنت مساعد ذكي', 'مرحباً، هل أنت Google Gemini؟');
    }
    
    /**
     * التحقق من تفعيل الخدمة
     * 
     * @return bool
     */
    public function isEnabled()
    {
        return !empty($this->apiKey);
    }
}
