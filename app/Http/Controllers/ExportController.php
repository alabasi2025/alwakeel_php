<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ExportController extends Controller
{
    /**
     * تصدير محادثة واحدة
     */
    public function exportConversation(Request $request, $id)
    {
        $format = $request->input('format', 'txt');
        
        $conversation = Conversation::with('messages')->findOrFail($id);
        
        if ($format === 'html') {
            return $this->exportToHTML($conversation);
        }
        
        return $this->exportToTXT($conversation);
    }
    
    /**
     * تصدير جميع المحادثات
     */
    public function exportAll(Request $request)
    {
        $format = $request->input('format', 'txt');
        
        $conversations = Conversation::with('messages')->orderBy('created_at', 'desc')->get();
        
        if ($format === 'html') {
            return $this->exportAllToHTML($conversations);
        }
        
        return $this->exportAllToTXT($conversations);
    }
    
    /**
     * تصدير محادثة إلى TXT
     */
    private function exportToTXT($conversation)
    {
        $content = "===========================================\n";
        $content .= "محادثة: {$conversation->title}\n";
        $content .= "التاريخ: " . $conversation->created_at->format('Y-m-d H:i:s') . "\n";
        $content .= "===========================================\n\n";
        
        foreach ($conversation->messages as $message) {
            $role = $message->role === 'user' ? '👤 أنت' : '🤖 الوكيل';
            $provider = $message->ai_provider ? " ({$message->ai_provider})" : '';
            
            $content .= "{$role}{$provider}:\n";
            $content .= $message->content . "\n";
            $content .= "-------------------------------------------\n\n";
        }
        
        $filename = "conversation_" . $conversation->id . "_" . date('Y-m-d_H-i-s') . ".txt";
        
        return Response::make($content, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
    
    /**
     * تصدير جميع المحادثات إلى TXT
     */
    private function exportAllToTXT($conversations)
    {
        $content = "===========================================\n";
        $content .= "جميع المحادثات\n";
        $content .= "تاريخ التصدير: " . date('Y-m-d H:i:s') . "\n";
        $content .= "عدد المحادثات: " . $conversations->count() . "\n";
        $content .= "===========================================\n\n";
        
        foreach ($conversations as $conversation) {
            $content .= "\n\n";
            $content .= "###########################################################\n";
            $content .= "# محادثة: {$conversation->title}\n";
            $content .= "# التاريخ: " . $conversation->created_at->format('Y-m-d H:i:s') . "\n";
            $content .= "###########################################################\n\n";
            
            foreach ($conversation->messages as $message) {
                $role = $message->role === 'user' ? '👤 أنت' : '🤖 الوكيل';
                $provider = $message->ai_provider ? " ({$message->ai_provider})" : '';
                
                $content .= "{$role}{$provider}:\n";
                $content .= $message->content . "\n";
                $content .= "-------------------------------------------\n\n";
            }
        }
        
        $filename = "all_conversations_" . date('Y-m-d_H-i-s') . ".txt";
        
        return Response::make($content, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
    
    /**
     * تصدير محادثة إلى HTML (يمكن طباعتها كـ PDF)
     */
    private function exportToHTML($conversation)
    {
        $html = '<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>محادثة - ' . htmlspecialchars($conversation->title) . '</title>
    <style>
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            direction: rtl;
            text-align: right;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #667eea;
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
        }
        .meta {
            color: #666;
            margin-bottom: 30px;
        }
        .message {
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 8px;
        }
        .message.user {
            background: #e3f2fd;
            border-right: 4px solid #2196f3;
        }
        .message.assistant {
            background: #f3e5f5;
            border-right: 4px solid #9c27b0;
        }
        .message-role {
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }
        .message-content {
            line-height: 1.6;
            white-space: pre-wrap;
        }
        @media print {
            body { background: white; }
            .container { box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>💬 ' . htmlspecialchars($conversation->title) . '</h1>
        <div class="meta">
            📅 التاريخ: ' . $conversation->created_at->format('Y-m-d H:i:s') . '<br>
            📊 عدد الرسائل: ' . $conversation->messages->count() . '
        </div>';
        
        foreach ($conversation->messages as $message) {
            $role = $message->role === 'user' ? '👤 أنت' : '🤖 الوكيل';
            $provider = $message->ai_provider ? " ({$message->ai_provider})" : '';
            $class = $message->role;
            
            $html .= '
        <div class="message ' . $class . '">
            <div class="message-role">' . $role . $provider . '</div>
            <div class="message-content">' . nl2br(htmlspecialchars($message->content)) . '</div>
        </div>';
        }
        
        $html .= '
    </div>
</body>
</html>';
        
        $filename = "conversation_" . $conversation->id . "_" . date('Y-m-d_H-i-s') . ".html";
        
        return Response::make($html, 200, [
            'Content-Type' => 'text/html; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
    
    /**
     * تصدير جميع المحادثات إلى HTML
     */
    private function exportAllToHTML($conversations)
    {
        $html = '<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>جميع المحادثات</title>
    <style>
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            direction: rtl;
            text-align: right;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #667eea;
            text-align: center;
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
        }
        .meta {
            color: #666;
            text-align: center;
            margin-bottom: 30px;
        }
        .conversation {
            margin-bottom: 40px;
            padding: 20px;
            background: #fafafa;
            border-radius: 8px;
            page-break-after: always;
        }
        .conversation-title {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        .conversation-date {
            color: #666;
            font-size: 14px;
            margin-bottom: 15px;
        }
        .message {
            margin-bottom: 15px;
            padding: 12px;
            border-radius: 6px;
        }
        .message.user {
            background: #e3f2fd;
            border-right: 3px solid #2196f3;
        }
        .message.assistant {
            background: #f3e5f5;
            border-right: 3px solid #9c27b0;
        }
        .message-role {
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
            font-size: 14px;
        }
        .message-content {
            line-height: 1.6;
            white-space: pre-wrap;
            font-size: 14px;
        }
        @media print {
            body { background: white; }
            .container { box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📚 جميع المحادثات</h1>
        <div class="meta">
            📅 تاريخ التصدير: ' . date('Y-m-d H:i:s') . '<br>
            📊 عدد المحادثات: ' . $conversations->count() . '
        </div>';
        
        foreach ($conversations as $conversation) {
            $html .= '
        <div class="conversation">
            <div class="conversation-title">💬 ' . htmlspecialchars($conversation->title) . '</div>
            <div class="conversation-date">📅 ' . $conversation->created_at->format('Y-m-d H:i:s') . '</div>';
            
            foreach ($conversation->messages as $message) {
                $role = $message->role === 'user' ? '👤 أنت' : '🤖 الوكيل';
                $provider = $message->ai_provider ? " ({$message->ai_provider})" : '';
                $class = $message->role;
                
                $html .= '
            <div class="message ' . $class . '">
                <div class="message-role">' . $role . $provider . '</div>
                <div class="message-content">' . nl2br(htmlspecialchars($message->content)) . '</div>
            </div>';
            }
            
            $html .= '
        </div>';
        }
        
        $html .= '
    </div>
</body>
</html>';
        
        $filename = "all_conversations_" . date('Y-m-d_H-i-s') . ".html";
        
        return Response::make($html, 200, [
            'Content-Type' => 'text/html; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
