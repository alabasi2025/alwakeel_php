<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ManusService
{
    private $apiKey;
    private $apiEndpoint;
    
    public function __construct($apiKey, $apiEndpoint = 'https://api.manus.ai')
    {
        $this->apiKey = $apiKey;
        $this->apiEndpoint = rtrim($apiEndpoint, '/');
    }
    
    /**
     * إرسال رسالة والحصول على رد مباشر (Chat Completion)
     * 
     * @param string $systemPrompt رسالة النظام
     * @param string $userMessage رسالة المستخدم
     * @param string $model اسم النموذج (افتراضي: gpt-4.1-nano)
     * @return array
     */
    public function chat($systemPrompt, $userMessage, $model = 'gpt-4.1-nano')
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json'
            ])->timeout(120)->post($this->apiEndpoint . '/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $systemPrompt
                    ],
                    [
                        'role' => 'user',
                        'content' => $userMessage
                    ]
                ],
                'temperature' => 0.7,
                'max_tokens' => 2000
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                // استخراج الرد من الاستجابة
                $reply = $data['choices'][0]['message']['content'] ?? 'لم يتم الحصول على رد';
                
                return [
                    'success' => true,
                    'response' => $reply,
                    'data' => $data
                ];
            } else {
                Log::error('Manus Chat API Error: ' . $response->body());
                return [
                    'success' => false,
                    'error' => 'خطأ في الاتصال بـ Manus: ' . $response->status(),
                    'details' => $response->body()
                ];
            }
        } catch (\Exception $e) {
            Log::error('Manus Chat API Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * إرسال مهمة إلى Manus AI (للمهام المعقدة)
     * 
     * @param string $prompt النص المطلوب معالجته
     * @param string $mode وضع التنفيذ (speed أو quality)
     * @param array $attachments مرفقات اختيارية
     * @return array
     */
    public function createTask($prompt, $mode = 'speed', $attachments = [])
    {
        try {
            $response = Http::withHeaders([
                'API_KEY' => $this->apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])->timeout(60)->post($this->apiEndpoint . '/v1/tasks', [
                'prompt' => $prompt,
                'mode' => $mode,
                'attachments' => $attachments
            ]);
            
            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            } else {
                Log::error('Manus API Error: ' . $response->body());
                return [
                    'success' => false,
                    'error' => 'خطأ في الاتصال بـ Manus: ' . $response->status()
                ];
            }
        } catch (\Exception $e) {
            Log::error('Manus API Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * الحصول على حالة مهمة
     * 
     * @param string $taskId معرف المهمة
     * @return array
     */
    public function getTask($taskId)
    {
        try {
            $response = Http::withHeaders([
                'API_KEY' => $this->apiKey,
                'Accept' => 'application/json'
            ])->timeout(30)->get($this->apiEndpoint . '/v1/tasks/' . $taskId);
            
            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'خطأ في الحصول على المهمة: ' . $response->status()
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * اختبار الاتصال بـ Manus API
     * 
     * @return array
     */
    public function testConnection()
    {
        return $this->chat('أنت مساعد ذكي', 'مرحباً، هل أنت Manus AI؟');
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
