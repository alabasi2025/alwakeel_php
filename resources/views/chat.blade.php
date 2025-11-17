@extends('layouts.app')
@section('title', 'الدردشة الذكية')
@section('content')
<div class="max-w-4xl mx-auto">
    <h1 class="text-4xl font-bold mb-8">💬 الدردشة الذكية</h1>
    
    <!-- AI Integration Selector -->
    <div class="bg-gradient-to-r from-purple-50 to-blue-50 rounded-xl shadow-lg p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">🤖 اختر نوع الذكاء الاصطناعي</h3>
                <p class="text-sm text-gray-600">حدد أي تكامل تريد استخدامه للرد على رسائلك</p>
            </div>
            <div class="flex gap-3">
                <label class="flex items-center gap-2 cursor-pointer bg-white px-4 py-2 rounded-lg shadow hover:shadow-md transition">
                    <input type="radio" name="ai-mode" value="auto" checked class="text-purple-600">
                    <span class="text-sm font-medium">🎯 تلقائي</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer bg-white px-4 py-2 rounded-lg shadow hover:shadow-md transition">
                    <input type="radio" name="ai-mode" value="manus" class="text-purple-600">
                    <span class="text-sm font-medium">🤖 Manus AI</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer bg-white px-4 py-2 rounded-lg shadow hover:shadow-md transition">
                    <input type="radio" name="ai-mode" value="openai" class="text-blue-600">
                    <span class="text-sm font-medium">⚡ OpenAI</span>
                </label>
            </div>
        </div>
        <div id="mode-description" class="mt-4 text-sm text-gray-700 bg-white rounded-lg p-3">
            <strong>التلقائي:</strong> النظام يختار الأنسب بناءً على نوع السؤال (مهام معقدة → Manus، أسئلة سريعة → OpenAI)
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-6">
        <div id="chat-messages" class="h-96 overflow-y-auto mb-4 space-y-4">
            <!-- Messages will appear here -->
        </div>
        <form id="chat-form" class="flex gap-2">
            <input type="text" id="message-input" class="flex-1 px-4 py-3 border rounded-lg" placeholder="اكتب رسالتك...">
            <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">إرسال</button>
        </form>
    </div>
</div>
<script>
// Update description based on selected mode
document.querySelectorAll('input[name="ai-mode"]').forEach(radio => {
    radio.addEventListener('change', (e) => {
        const descriptions = {
            'auto': '<strong>التلقائي:</strong> النظام يختار الأنسب بناءً على نوع السؤال (مهام معقدة → Manus، أسئلة سريعة → OpenAI)',
            'manus': '<strong>Manus AI:</strong> للمهام المعقدة - البحث المتقدم، إنشاء المستندات، تطوير الويب، والتحليل العميق',
            'openai': '<strong>OpenAI:</strong> للدردشة السريعة - إجابات فورية على الأسئلة العامة والمحادثات البسيطة'
        };
        document.getElementById('mode-description').innerHTML = descriptions[e.target.value];
    });
});

document.getElementById('chat-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const input = document.getElementById('message-input');
    const message = input.value.trim();
    if (!message) return;
    
    // Get selected AI mode
    const aiMode = document.querySelector('input[name="ai-mode"]:checked').value;
    
    // Add user message
    const messagesDiv = document.getElementById('chat-messages');
    messagesDiv.innerHTML += `<div class="text-right"><span class="inline-block bg-blue-100 px-4 py-2 rounded-lg">${message}</span></div>`;
    input.value = '';
    
    // Add loading indicator
    const loadingId = 'loading-' + Date.now();
    messagesDiv.innerHTML += `<div id="${loadingId}" class="text-left"><span class="inline-block bg-gray-100 px-4 py-2 rounded-lg">⏳ جاري التفكير...</span></div>`;
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
    
    // Send to server
    try {
        const response = await fetch('{{ route("chat.send") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ message, ai_mode: aiMode })
        });
        
        const data = await response.json();
        
        // Remove loading indicator
        document.getElementById(loadingId).remove();
        
        // Add AI response with source indicator
        const sourceEmoji = data.source === 'manus' ? '🤖' : (data.source === 'openai' ? '⚡' : '🎯');
        messagesDiv.innerHTML += `<div class="text-left"><span class="inline-block bg-gray-100 px-4 py-2 rounded-lg">${sourceEmoji} ${data.response}</span></div>`;
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    } catch (error) {
        document.getElementById(loadingId).remove();
        messagesDiv.innerHTML += `<div class="text-left"><span class="inline-block bg-red-100 px-4 py-2 rounded-lg">❌ حدث خطأ في الاتصال</span></div>`;
    }
});
</script>
@endsection
