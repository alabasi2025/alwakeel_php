<?php
namespace App\Http\Controllers;
use App\Models\LearningData;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index()
    {
        return view('chat');
    }

    public function send(Request $request)
    {
        $message = $request->input('message');
        
        // Simple AI response (يمكن تطويره لاحقاً)
        $response = "تم استلام رسالتك: " . $message;
        
        // Save to learning data
        LearningData::create([
            'user_input' => $message,
            'system_response' => $response,
            'success_score' => 1
        ]);
        
        return response()->json(['response' => $response]);
    }
}
