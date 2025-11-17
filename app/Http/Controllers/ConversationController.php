<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\ConversationMessage;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    // عرض جميع المحادثات
    public function index()
    {
        $conversations = Conversation::with('latestMessage')
            ->orderBy('last_message_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json($conversations);
    }
    
    // إنشاء محادثة جديدة
    public function store()
    {
        $conversation = Conversation::create([
            'last_message_at' => now(),
        ]);
        
        return response()->json($conversation);
    }
    
    // عرض محادثة محددة مع جميع رسائلها
    public function show($id)
    {
        $conversation = Conversation::with('messages')->findOrFail($id);
        return response()->json($conversation);
    }
    
    // حذف محادثة
    public function destroy($id)
    {
        $conversation = Conversation::findOrFail($id);
        $conversation->delete();
        
        return response()->json(['message' => 'تم حذف المحادثة بنجاح']);
    }
    
    // حذف جميع المحادثات
    public function destroyAll()
    {
        Conversation::truncate();
        ConversationMessage::truncate();
        
        return response()->json(['message' => 'تم حذف جميع المحادثات بنجاح']);
    }
}
