@extends('layouts.app')
@section('title', 'الدردشة الذكية')
@section('content')
<div class="max-w-4xl mx-auto">
    <h1 class="text-4xl font-bold mb-8">💬 الدردشة الذكية</h1>
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
document.getElementById('chat-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const input = document.getElementById('message-input');
    const message = input.value.trim();
    if (!message) return;
    
    // Add user message
    const messagesDiv = document.getElementById('chat-messages');
    messagesDiv.innerHTML += `<div class="text-right"><span class="inline-block bg-blue-100 px-4 py-2 rounded-lg">${message}</span></div>`;
    input.value = '';
    
    // Send to server
    const response = await fetch('{{ route("chat.send") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ message })
    });
    
    const data = await response.json();
    messagesDiv.innerHTML += `<div class="text-left"><span class="inline-block bg-gray-100 px-4 py-2 rounded-lg">${data.response}</span></div>`;
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
});
</script>
@endsection
