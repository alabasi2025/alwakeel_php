<?php

namespace App\Services;

use App\Models\Integration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIService
{
    protected $integration;
    protected $apiKey;
    protected $model;
    protected $temperature;
    
    public function __construct()
    {
        $this->integration = Integration::where('service_name', 'OpenAI')
            ->where('is_enabled', 'true')
            ->first();
            
        if ($this->integration) {
            $this->apiKey = $this->integration->api_key;
            $this->model = $this->integration->api_endpoint ?? 'gpt-4';
            $config = json_decode($this->integration->config, true);
            $this->temperature = $config['temperature'] ?? 0.7;
        }
    }
    
    public function isEnabled()
    {
        return $this->integration !== null;
    }
    
    public function chat($message, $systemPrompt = null, $maxTokens = 500)
    {
        if (!$this->isEnabled()) {
            throw new \Exception('OpenAI غير مفعّل. يرجى تفعيله من صفحة التكاملات.');
        }
        
        $messages = [];
        
        if ($systemPrompt) {
            $messages[] = [
                'role' => 'system',
                'content' => $systemPrompt
            ];
        }
        
        $messages[] = [
            'role' => 'user',
            'content' => $message
        ];
        
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json'
            ])->timeout(60)->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'messages' => $messages,
                'temperature' => $this->temperature,
                'max_tokens' => $maxTokens
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'response' => $data['choices'][0]['message']['content'] ?? '',
                    'usage' => $data['usage'] ?? null
                ];
            } else {
                Log::error('OpenAI API Error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                
                return [
                    'success' => false,
                    'error' => 'خطأ في الاتصال بـ OpenAI: ' . $response->status()
                ];
            }
        } catch (\Exception $e) {
            Log::error('OpenAI Exception', [
                'message' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    public function chatWithContext($message, $conversationHistory = [], $systemPrompt = null)
    {
        if (!$this->isEnabled()) {
            throw new \Exception('OpenAI غير مفعّل. يرجى تفعيله من صفحة التكاملات.');
        }
        
        $messages = [];
        
        if ($systemPrompt) {
            $messages[] = [
                'role' => 'system',
                'content' => $systemPrompt
            ];
        }
        
        // Add conversation history
        foreach ($conversationHistory as $msg) {
            $messages[] = $msg;
        }
        
        // Add current message
        $messages[] = [
            'role' => 'user',
            'content' => $message
        ];
        
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json'
            ])->timeout(60)->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'messages' => $messages,
                'temperature' => $this->temperature,
                'max_tokens' => 1000
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'response' => $data['choices'][0]['message']['content'] ?? '',
                    'usage' => $data['usage'] ?? null
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'خطأ في الاتصال بـ OpenAI'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
