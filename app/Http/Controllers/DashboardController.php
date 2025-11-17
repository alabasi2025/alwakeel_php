<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // تحقق من وجود Models قبل استخدامها
        $totalConversations = 0;
        $totalMessages = 0;
        $todayConversations = 0;
        $todayMessages = 0;
        $recentConversations = collect();
        $aiStats = collect();
        $weeklyStats = [];
        $avgMessagesPerConversation = 0;
        $busiestDay = null;
        
        try {
            if (class_exists('\App\Models\Conversation')) {
                $Conversation = '\App\Models\Conversation';
                $ConversationMessage = '\App\Models\ConversationMessage';
                
                $totalConversations = $Conversation::count();
                $totalMessages = $ConversationMessage::count();
                $todayConversations = $Conversation::whereDate('created_at', today())->count();
                $todayMessages = $ConversationMessage::whereDate('created_at', today())->count();
                
                $recentConversations = $Conversation::with('messages')
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();
                
                $aiStats = $ConversationMessage::select('ai_provider', \DB::raw('count(*) as count'))
                    ->whereNotNull('ai_provider')
                    ->groupBy('ai_provider')
                    ->get();
                
                for ($i = 6; $i >= 0; $i--) {
                    $date = today()->subDays($i);
                    $weeklyStats[] = [
                        'date' => $date->format('Y-m-d'),
                        'label' => $date->locale('ar')->isoFormat('ddd'),
                        'conversations' => $Conversation::whereDate('created_at', $date)->count(),
                        'messages' => $ConversationMessage::whereDate('created_at', $date)->count(),
                    ];
                }
                
                $avgMessagesPerConversation = $totalConversations > 0 
                    ? round($totalMessages / $totalConversations, 1) 
                    : 0;
                
                $busiestDay = $ConversationMessage::select(
                        \DB::raw('DATE(created_at) as date'),
                        \DB::raw('count(*) as count')
                    )
                    ->groupBy('date')
                    ->orderBy('count', 'desc')
                    ->first();
            }
        } catch (\Exception $e) {
            // في حالة حدوث خطأ، استخدم القيم الافتراضية
        }

        return view('dashboard', compact(
            'totalConversations',
            'totalMessages',
            'todayConversations',
            'todayMessages',
            'recentConversations',
            'aiStats',
            'weeklyStats',
            'avgMessagesPerConversation',
            'busiestDay'
        ));
    }
}
