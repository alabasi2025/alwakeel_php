<?php
namespace App\Http\Controllers;
use App\Models\Integration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class IntegrationController extends Controller
{
    public function index()
    {
        $integrations = Integration::where('service_name', '!=', 'OpenAI')->get();
        
        // Get OpenAI settings
        $openai = Integration::where('service_name', 'OpenAI')->first();
        $openai_enabled = $openai && $openai->is_enabled === 'true';
        
        $openai_config = $openai ? json_decode($openai->config, true) : [];
        $openai_key = $openai_config['api_key'] ?? '';
        $openai_model = $openai_config['model'] ?? 'gpt-4';
        $openai_temperature = $openai_config['temperature'] ?? 0.7;
        
        return view('integrations', compact('integrations', 'openai_enabled', 'openai_key', 'openai_model', 'openai_temperature'));
    }

    public function save(Request $request)
    {
        $serviceName = $request->input('service_name');
        $integration = Integration::where('service_name', $serviceName)->first();
        
        if ($integration) {
            $integration->is_enabled = $integration->is_enabled === 'true' ? 'false' : 'true';
            $integration->save();
        }
        
        return redirect()->route('integrations')->with('success', 'تم تحديث التكامل بنجاح');
    }
    
    public function saveOpenAI(Request $request)
    {
        $request->validate([
            'api_key' => 'required|string',
            'model' => 'required|string',
            'temperature' => 'required|numeric|min:0|max:2'
        ]);
        
        $config = [
            'api_key' => $request->api_key,
            'model' => $request->model,
            'temperature' => (float)$request->temperature
        ];
        
        $integration = Integration::updateOrCreate(
            ['service_name' => 'OpenAI'],
            [
                'is_enabled' => 'true',
                'config' => json_encode($config)
            ]
        );
        
        return redirect()->route('integrations')->with('success', '✅ تم حفظ إعدادات OpenAI بنجاح');
    }
    
    public function testOpenAI(Request $request)
    {
        $integration = Integration::where('service_name', 'OpenAI')->first();
        
        if (!$integration || $integration->is_enabled !== 'true') {
            return response()->json([
                'success' => false,
                'error' => 'OpenAI غير مفعّل'
            ], 400);
        }
        
        $config = json_decode($integration->config, true);
        $api_key = $config['api_key'] ?? '';
        $model = $config['model'] ?? 'gpt-4';
        $temperature = $config['temperature'] ?? 0.7;
        
        if (empty($api_key)) {
            return response()->json([
                'success' => false,
                'error' => 'مفتاح API غير موجود'
            ], 400);
        }
        
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ])->timeout(30)->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => 'قل مرحباً بالعربية'
                    ]
                ],
                'temperature' => $temperature,
                'max_tokens' => 100
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                return response()->json([
                    'success' => true,
                    'response' => $data['choices'][0]['message']['content'] ?? 'تم الاتصال بنجاح'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'خطأ في الاتصال: ' . $response->body()
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
