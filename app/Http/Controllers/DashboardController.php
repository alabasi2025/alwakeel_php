<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\ConversationMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // إحصائيات المحادثات
        $totalConversations = Conversation::count();
        $totalMessages = ConversationMessage::count();
        $todayConversations = Conversation::whereDate('created_at', today())->count();
        $todayMessages = ConversationMessage::whereDate('created_at', today())->count();
        
        // آخر المحادثات
        $recentConversations = Conversation::with('messages')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // إحصائيات AI Providers
        $aiStats = ConversationMessage::select('ai_provider', DB::raw('count(*) as count'))
            ->whereNotNull('ai_provider')
            ->groupBy('ai_provider')
            ->get();
        
        // إحصائيات الأسبوع الماضي (للرسم البياني)
        $weeklyStats = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $weeklyStats[] = [
                'date' => $date->format('Y-m-d'),
                'label' => $date->locale('ar')->isoFormat('ddd'),
                'conversations' => Conversation::whereDate('created_at', $date)->count(),
                'messages' => ConversationMessage::whereDate('created_at', $date)->count(),
            ];
        }
        
        // متوسط الرسائل لكل محادثة
        $avgMessagesPerConversation = $totalConversations > 0 
            ? round($totalMessages / $totalConversations, 1) 
            : 0;
        
        // أكثر الأيام نشاطاً
        $busiestDay = ConversationMessage::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as count')
            )
            ->groupBy('date')
            ->orderBy('count', 'desc')
            ->first();

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
