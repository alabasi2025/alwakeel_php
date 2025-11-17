<?php

namespace App\Http\Controllers;

use App\Models\LearningData;
use App\Models\Integration;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Services\OpenAIService;
use App\Services\ManusService;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    protected $openAI;
    
    public function __construct(OpenAIService $openAI)
    {
        $this->openAI = $openAI;
    }
    
    public function index()
    {
        return view('chat');
    }

    public function send(Request $request)
    {
        $message = $request->input('message');
        $aiMode = $request->input('ai_mode', 'auto'); // auto, manus, openai, gemini
        $conversationId = $request->input('conversation_id');
        $response = null;
        $source = 'unknown';
        
        // إنشاء محادثة جديدة إذا لم تكن موجودة
        if (!$conversationId) {
            $conversation = Conversation::create(['last_message_at' => now()]);
            $conversationId = $conversation->id;
        } else {
            $conversation = Conversation::findOrFail($conversationId);
        }
        
        // حفظ رسالة المستخدم
        ConversationMessage::create([
            'conversation_id' => $conversationId,
            'role' => 'user',
            'content' => $message,
        ]);
        
        try {
            // المرحلة 1: البحث في قاعدة البيانات عن أسئلة مشابهة
            $similarQuestion = $this->findSimilarQuestion($message);
            
            if ($similarQuestion && $similarQuestion->success_score >= 0.8) {
                // وجدنا سؤال مشابه بنسبة نجاح عالية - نستخدم الجواب المحفوظ
                $response = $similarQuestion->system_response;
                $source = 'learning_database';
                
                // تحديث عدد مرات الاستخدام
                $similarQuestion->increment('usage_count');
                
                Log::info('استخدام جواب من قاعدة البيانات', [
                    'original_question' => $similarQuestion->user_input,
                    'current_question' => $message,
                    'similarity' => $this->calculateSimilarity($message, $similarQuestion->user_input)
                ]);
            } else {
                // لم نجد سؤال مشابه - نستخدم AI حسب الاختيار
                
                if ($aiMode === 'auto') {
                    // الوضع التلقائي: نحدد الأنسب بناءً على نوع السؤال
                    $aiMode = $this->detectBestAI($message);
                }
                
                // اختيار الـ AI المناسب
                switch ($aiMode) {
                    case 'gemini':
                        $result = $this->tryGemini($message);
                        if ($result['success']) {
                            $response = $result['response'];
                            $source = 'gemini';
                        } else {
                            throw new \Exception($result['error']);
                        }
                        break;
                        
                    case 'manus':
                        $result = $this->tryManus($message);
                        if ($result['success']) {
                            $response = $result['response'];
                            $source = 'manus';
                        } else {
                            // فشل Manus، نحاول Gemini كبديل
                            $result = $this->tryGemini($message);
                            if ($result['success']) {
                                $response = $result['response'];
                                $source = 'gemini';
                            } else {
                                throw new \Exception($result['error']);
                            }
                        }
                        break;
                        
                    case 'openai':
                    default:
                        $response = $this->tryOpenAI($message);
                        $source = 'openai';
                        break;
                }
            }
            
            // حفظ رد الـ AI
            ConversationMessage::create([
                'conversation_id' => $conversationId,
                'role' => 'assistant',
                'content' => $response,
                'ai_source' => $source,
            ]);
            
            // تحديث عنوان المحادثة تلقائياً
            $conversation->updateTitle();
            $conversation->update(['last_message_at' => now()]);
            
            return response()->json([
                'success' => true,
                'response' => $response,
                'source' => $source,
                'conversation_id' => $conversationId
            ]);
            
        } catch (\Exception $e) {
            Log::error('خطأ في معالجة الرسالة: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'response' => 'عذراً، حدث خطأ في معالجة رسالتك: ' . $e->getMessage(),
                'source' => 'error'
            ], 500);
        }
    }
    
    /**
     * تحديد أفضل AI بناءً على نوع السؤال
     */
    private function detectBestAI($message)
    {
        // كلمات مفتاحية تدل على مهام معقدة (Manus)
        $complexKeywords = [
            'ابحث', 'اكتب', 'أنشئ', 'طور', 'صمم', 'حلل', 'قارن', 'اشرح بالتفصيل',
            'مستند', 'تقرير', 'عرض تقديمي', 'موقع', 'برنامج', 'كود'
        ];
        
        foreach ($complexKeywords as $keyword) {
            if (mb_stripos($message, $keyword) !== false) {
                return 'manus';
            }
        }
        
        // أسئلة بسيطة - نستخدم Gemini المجاني
        return 'gemini';
    }
    
    /**
     * محاولة استخدام Google Gemini
     */
    private function tryGemini($message)
    {
        // محاولة الحصول على إعدادات Gemini من قاعدة البيانات
        $geminiIntegration = Integration::where('service_name', 'gemini')
            ->where('is_enabled', true)
            ->first();
        
        $apiKey = null;
        if ($geminiIntegration) {
            $config = json_decode($geminiIntegration->config, true);
            $apiKey = $config['api_key'] ?? null;
        }
        
        $geminiService = new GeminiService($apiKey);
        
        if (!$geminiService->isEnabled()) {
            return ['success' => false, 'error' => 'Gemini غير مفعل. يرجى إضافة مفتاح API من صفحة التكاملات.'];
        }
        
        $systemPrompt = "أنت وكيل ذكي مساعد باللغة العربية. مهمتك مساعدة المستخدمين بشكل دقيق ومفيد. أجب بطريقة واضحة ومختصرة.";
        
        $result = $geminiService->chat($systemPrompt, $message);
        
        if ($result['success']) {
            // حفظ في قاعدة التعلم
            $this->saveToLearningDatabase($message, $result['response'], 'gemini', json_encode($result['data']));
        }
        
        return $result;
    }
    
    /**
     * محاولة استخدام Manus AI
     */
    private function tryManus($message)
    {
        $manusIntegration = Integration::where('service_name', 'manus')
            ->where('is_enabled', true)
            ->first();
        
        if (!$manusIntegration) {
            return ['success' => false, 'error' => 'Manus AI غير مفعل'];
        }
        
        $config = json_decode($manusIntegration->config, true);
        $manusService = new ManusService(
            $config['api_key'], 
            $config['api_endpoint'] ?? 'https://api.manus.ai'
        );
        
        $systemPrompt = "أنت وكيل ذكي مساعد باللغة العربية. مهمتك مساعدة المستخدمين بشكل دقيق ومفيد. أجب بطريقة واضحة ومختصرة.";
        
        $result = $manusService->chat($systemPrompt, $message);
        
        if ($result['success']) {
            // حفظ في قاعدة التعلم
            $this->saveToLearningDatabase($message, $result['response'], 'manus', json_encode($result['data']));
        }
        
        return $result;
    }
    
    /**
     * محاولة استخدام OpenAI
     */
    private function tryOpenAI($message)
    {
        if ($this->openAI->isEnabled()) {
            $systemPrompt = "أنت وكيل ذكي مساعد باللغة العربية. مهمتك مساعدة المستخدمين بشكل دقيق ومفيد. أجب بطريقة واضحة ومختصرة.";
            
            $result = $this->openAI->chat($systemPrompt, $message);
            
            if ($result['success']) {
                $response = $result['response'];
                
                // حفظ في قاعدة التعلم
                $this->saveToLearningDatabase($message, $response, 'openai', json_encode($result));
                
                return $response;
            } else {
                throw new \Exception('خطأ في الاتصال بـ OpenAI: ' . $result['error']);
            }
        } else {
            throw new \Exception('OpenAI غير مفعل. يرجى تفعيله من صفحة التكاملات.');
        }
    }
    
    /**
     * حفظ السؤال والجواب في قاعدة التعلم
     */
    private function saveToLearningDatabase($question, $answer, $source, $metadata = null)
    {
        try {
            LearningData::create([
                'user_input' => $question,
                'system_response' => $answer,
                'context' => $source,
                'success_score' => 1.0,
                'metadata' => $metadata,
                'usage_count' => 1
            ]);
            
            Log::info('تم حفظ السؤال والجواب في قاعدة التعلم', [
                'question' => $question,
                'source' => $source
            ]);
        } catch (\Exception $e) {
            Log::error('فشل حفظ البيانات في قاعدة التعلم: ' . $e->getMessage());
        }
    }
    
    /**
     * البحث عن سؤال مشابه في قاعدة البيانات
     */
    private function findSimilarQuestion($question)
    {
        // البحث البسيط - يمكن تحسينه لاحقاً باستخدام خوارزميات أكثر تقدماً
        $allQuestions = LearningData::where('success_score', '>=', 0.7)->get();
        
        $bestMatch = null;
        $bestSimilarity = 0;
        
        foreach ($allQuestions as $learningData) {
            $similarity = $this->calculateSimilarity($question, $learningData->user_input);
            
            if ($similarity > $bestSimilarity) {
                $bestSimilarity = $similarity;
                $bestMatch = $learningData;
            }
        }
        
        // نعتبر التشابه جيد إذا كان أكثر من 80%
        if ($bestMatch && $bestSimilarity >= 0.8) {
            $bestMatch->success_score = $bestSimilarity;
            return $bestMatch;
        }
        
        return null;
    }
    
    /**
     * حساب نسبة التشابه بين نصين
     */
    private function calculateSimilarity($text1, $text2)
    {
        // تنظيف النصوص
        $text1 = mb_strtolower(trim($text1));
        $text2 = mb_strtolower(trim($text2));
        
        // إذا كانا متطابقين تماماً
        if ($text1 === $text2) {
            return 1.0;
        }
        
        // حساب Levenshtein distance
        $distance = levenshtein($text1, $text2);
        $maxLength = max(mb_strlen($text1), mb_strlen($text2));
        
        if ($maxLength == 0) {
            return 1.0;
        }
        
        $similarity = 1 - ($distance / $maxLength);
        
        return max(0, $similarity);
    }
}
