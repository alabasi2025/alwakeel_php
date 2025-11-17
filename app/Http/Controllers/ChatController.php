<?php
namespace App\Http\Controllers;

use App\Models\LearningData;
use App\Services\OpenAIService;
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
                // لم نجد سؤال مشابه - نستخدم OpenAI
                if ($this->openAI->isEnabled()) {
                    $systemPrompt = "أنت وكيل ذكي مساعد باللغة العربية. مهمتك مساعدة المستخدمين بشكل دقيق ومفيد. أجب بطريقة واضحة ومختصرة.";
                    
                    $result = $this->openAI->chat($message, $systemPrompt);
                    
                    if ($result['success']) {
                        $response = $result['response'];
                        $source = 'openai';
                        
                        // حفظ السؤال والجواب في قاعدة البيانات للتعلم
                        $this->saveToLearningDatabase($message, $response, $source);
                        
                        Log::info('استخدام OpenAI للرد', [
                            'question' => $message,
                            'tokens_used' => $result['usage']['total_tokens'] ?? 0
                        ]);
                    } else {
                        $response = "عذراً، حدث خطأ في الاتصال بالذكاء الاصطناعي: " . $result['error'];
                        $source = 'error';
                    }
                } else {
                    // OpenAI غير مفعّل - رد بسيط
                    $response = "مرحباً! أنا الوكيل الذكي. حالياً لم يتم ربطي بأي خدمة ذكاء اصطناعي. يرجى تفعيل OpenAI من صفحة التكاملات لأتمكن من مساعدتك بشكل أفضل.";
                    $source = 'fallback';
                }
            }
            
            return response()->json([
                'response' => $response,
                'source' => $source,
                'learned' => $source === 'learning_database'
            ]);
            
        } catch (\Exception $e) {
            Log::error('خطأ في ChatController', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'response' => "عذراً، حدث خطأ غير متوقع. يرجى المحاولة مرة أخرى.",
                'source' => 'error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * البحث عن سؤال مشابه في قاعدة البيانات
     */
    private function findSimilarQuestion($question)
    {
        // البحث عن أسئلة مشابهة باستخدام LIKE
        $questions = LearningData::where('success_score', '>=', 0.7)
            ->orderBy('usage_count', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
        
        $bestMatch = null;
        $bestSimilarity = 0;
        
        foreach ($questions as $q) {
            $similarity = $this->calculateSimilarity($question, $q->user_input);
            
            if ($similarity > $bestSimilarity && $similarity >= 0.75) {
                $bestSimilarity = $similarity;
                $bestMatch = $q;
            }
        }
        
        return $bestMatch;
    }
    
    /**
     * حساب نسبة التشابه بين سؤالين
     */
    private function calculateSimilarity($str1, $str2)
    {
        // تنظيف النصوص
        $str1 = mb_strtolower(trim($str1));
        $str2 = mb_strtolower(trim($str2));
        
        // إذا كانت النصوص متطابقة تماماً
        if ($str1 === $str2) {
            return 1.0;
        }
        
        // حساب نسبة التشابه باستخدام similar_text
        similar_text($str1, $str2, $percent);
        
        return $percent / 100;
    }
    
    /**
     * حفظ السؤال والجواب في قاعدة البيانات للتعلم
     */
    private function saveToLearningDatabase($question, $answer, $source)
    {
        try {
            LearningData::create([
                'user_input' => $question,
                'system_response' => $answer,
                'success_score' => 1.0, // يمكن تحسينها لاحقاً بناءً على تقييم المستخدم
                'source' => $source,
                'usage_count' => 1
            ]);
        } catch (\Exception $e) {
            Log::error('فشل حفظ البيانات للتعلم', [
                'error' => $e->getMessage()
            ]);
        }
    }
}
