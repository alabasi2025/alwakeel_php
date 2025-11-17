<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    /**
     * تصدير جميع المحادثات
     */
    public function exportAll(Request $request)
    {
        try {
            $format = $request->input('format', 'txt');
            
            $conversations = Conversation::with('messages')->orderBy('created_at', 'desc')->get();
            
            if ($format === 'html') {
                return $this->exportAllToHTML($conversations);
            }
            
            return $this->exportAllToTXT($conversations);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }
    
    /**
     * تصدير محادثة واحدة
     */
    public function exportConversation(Request $request, $id)
    {
        try {
            $format = $request->input('format', 'txt');
            
            $conversation = Conversation::with('messages')->findOrFail($id);
            
            if ($format === 'html') {
                return $this->exportToHTML($conversation);
            }
            
            return $this->exportToTXT($conversation);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
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
                $role = $message->role === 'user' ? 'أنت' : 'الوكيل';
                $provider = $message->ai_provider ? " ({$message->ai_provider})" : '';
                
                $content .= "{$role}{$provider}:\n";
                $content .= $message->content . "\n";
                $content .= "-------------------------------------------\n\n";
            }
        }
        
        $filename = "all_conversations_" . date('Y-m-d_H-i-s') . ".txt";
        
        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
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
            $role = $message->role === 'user' ? 'أنت' : 'الوكيل';
            $provider = $message->ai_provider ? " ({$message->ai_provider})" : '';
            
            $content .= "{$role}{$provider}:\n";
            $content .= $message->content . "\n";
            $content .= "-------------------------------------------\n\n";
        }
        
        $filename = "conversation_" . $conversation->id . "_" . date('Y-m-d_H-i-s') . ".txt";
        
        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
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
    <title>جميع المحادثات</title>
    <style>
        body { font-family: Arial, sans-serif; direction: rtl; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; }
        h1 { color: #667eea; text-align: center; }
        .conversation { margin-bottom: 40px; padding: 20px; background: #fafafa; }
        .message { margin-bottom: 15px; padding: 12px; }
        .message.user { background: #e3f2fd; }
        .message.assistant { background: #f3e5f5; }
    </style>
</head>
<body>
    <div class="container">
        <h1>جميع المحادثات</h1>
        <p>تاريخ التصدير: ' . date('Y-m-d H:i:s') . '</p>
        <p>عدد المحادثات: ' . $conversations->count() . '</p>';
        
        foreach ($conversations as $conversation) {
            $html .= '<div class="conversation">';
            $html .= '<h2>' . htmlspecialchars($conversation->title) . '</h2>';
            $html .= '<p>التاريخ: ' . $conversation->created_at->format('Y-m-d H:i:s') . '</p>';
            
            foreach ($conversation->messages as $message) {
                $role = $message->role === 'user' ? 'أنت' : 'الوكيل';
                $class = $message->role;
                
                $html .= '<div class="message ' . $class . '">';
                $html .= '<strong>' . $role . ':</strong><br>';
                $html .= nl2br(htmlspecialchars($message->content));
                $html .= '</div>';
            }
            
            $html .= '</div>';
        }
        
        $html .= '</div></body></html>';
        
        $filename = "all_conversations_" . date('Y-m-d_H-i-s') . ".html";
        
        return response($html, 200, [
            'Content-Type' => 'text/html; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
    
    /**
     * تصدير محادثة إلى HTML
     */
    private function exportToHTML($conversation)
    {
        $html = '<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>' . htmlspecialchars($conversation->title) . '</title>
    <style>
        body { font-family: Arial, sans-serif; direction: rtl; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; }
        h1 { color: #667eea; }
        .message { margin-bottom: 15px; padding: 12px; }
        .message.user { background: #e3f2fd; }
        .message.assistant { background: #f3e5f5; }
    </style>
</head>
<body>
    <div class="container">
        <h1>' . htmlspecialchars($conversation->title) . '</h1>
        <p>التاريخ: ' . $conversation->created_at->format('Y-m-d H:i:s') . '</p>';
        
        foreach ($conversation->messages as $message) {
            $role = $message->role === 'user' ? 'أنت' : 'الوكيل';
            $class = $message->role;
            
            $html .= '<div class="message ' . $class . '">';
            $html .= '<strong>' . $role . ':</strong><br>';
            $html .= nl2br(htmlspecialchars($message->content));
            $html .= '</div>';
        }
        
        $html .= '</div></body></html>';
        
        $filename = "conversation_" . $conversation->id . "_" . date('Y-m-d_H-i-s') . ".html";
        
        return response($html, 200, [
            'Content-Type' => 'text/html; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
