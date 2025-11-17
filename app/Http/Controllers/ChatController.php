<?php

namespace App\Http\Controllers;

use App\Models\LearningData;
use App\Models\Integration;
use App\Services\OpenAIService;
use App\Services\ManusService;
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
        $aiMode = $request->input('ai_mode', 'auto'); // auto, manus, openai
        $response = null;
        $source = 'unknown';
        
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
                
                if ($aiMode === 'manus') {
                    // استخدام Manus AI
                    $result = $this->tryManus($message);
                    if ($result['success']) {
                        $response = $result['response'];
                        $source = 'manus';
                    } else {
                        // فشل Manus، نحاول OpenAI كبديل
                        $response = $this->tryOpenAI($message);
                        $source = 'openai';
                    }
                } else {
                    // استخدام OpenAI
                    $response = $this->tryOpenAI($message);
                    $source = 'openai';
                }
            }
            
            return response()->json([
                'success' => true,
                'response' => $response,
                'source' => $source
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
        
        // أسئلة بسيطة (OpenAI)
        return 'openai';
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
        
        $result = $manusService->createTask($message, 'speed');
        
        if ($result['success']) {
            $taskUrl = $result['data']['task_url'] ?? '';
            $taskId = $result['data']['task_id'] ?? '';
            
            $response = "✅ تم إنشاء مهمة في Manus AI بنجاح!\n\n";
            $response .= "🔗 رابط المهمة: {$taskUrl}\n";
            $response .= "🆔 معرف المهمة: {$taskId}\n\n";
            $response .= "يمكنك متابعة تقدم المهمة من خلال الرابط أعلاه.";
            
            // حفظ في قاعدة التعلم
            $this->saveToLearningDatabase($message, $response, 'manus', json_encode($result['data']));
            
            return ['success' => true, 'response' => $response];
        }
        
        return ['success' => false, 'error' => $result['error'] ?? 'فشل الاتصال بـ Manus'];
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
