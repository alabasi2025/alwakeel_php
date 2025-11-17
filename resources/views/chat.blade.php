@extends('layouts.app')
@section('title', 'الدردشة الذكية')
@section('content')
<div class="flex gap-4 max-w-7xl mx-auto">
    <!-- Sidebar: قائمة المحادثات -->
    <div class="w-64 bg-white rounded-xl shadow-lg p-4 flex flex-col" style="height: calc(100vh - 150px);">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold">💬 المحادثات</h3>
            <button id="new-chat-btn" class="px-3 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                + جديد
            </button>
        </div>
        
        <div id="conversations-list" class="flex-1 overflow-y-auto space-y-2">
            <!-- قائمة المحادثات ستظهر هنا -->
        </div>
        
        <div class="mt-4 space-y-2">
            <button id="export-all-txt-btn" class="w-full px-3 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 text-sm">
                📄 تصدير الكل (TXT)
            </button>
            <button id="export-all-html-btn" class="w-full px-3 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 text-sm">
                📕 تصدير الكل (HTML)
            </button>
            <button id="delete-all-btn" class="w-full px-3 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 text-sm">
                🗑️ حذف الكل
            </button>
        </div>
    </div>

    <!-- Main Chat Area -->
    <div class="flex-1">
        <h1 class="text-4xl font-bold mb-6">💬 الدردشة الذكية</h1>
        
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
                        <input type="radio" name="ai-mode" value="gemini" class="text-green-600">
                        <span class="text-sm font-medium">🌟 Gemini</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer bg-white px-4 py-2 rounded-lg shadow hover:shadow-md transition">
                        <input type="radio" name="ai-mode" value="openai" class="text-blue-600">
                        <span class="text-sm font-medium">⚡ OpenAI</span>
                    </label>
                </div>
            </div>
            <div id="mode-description" class="mt-4 text-sm text-gray-700 bg-white rounded-lg p-3">
                <strong>التلقائي:</strong> النظام يختار الأنسب بناءً على نوع السؤال (مهام معقدة → Manus، أسئلة سريعة → Gemini المجاني)
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
</div>

<script>
let currentConversationId = null;

// تحميل قائمة المحادثات
async function loadConversations() {
    try {
        const response = await fetch('/conversations');
        const conversations = await response.json();
        
        const list = document.getElementById('conversations-list');
        list.innerHTML = '';
        
        if (conversations.length === 0) {
            list.innerHTML = '<p class="text-gray-500 text-sm text-center">لا توجد محادثات</p>';
            return;
        }
        
        conversations.forEach(conv => {
            const div = document.createElement('div');
            div.className = `conversation-item p-3 rounded-lg cursor-pointer hover:bg-gray-100 ${conv.id === currentConversationId ? 'bg-blue-100' : ''}`;
            div.innerHTML = `
                <div class="flex items-start justify-between">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate">${conv.title || 'محادثة جديدة'}</p>
                        <p class="text-xs text-gray-500">${new Date(conv.last_message_at).toLocaleDateString('ar')}</p>
                    </div>
                    <button class="delete-conv-btn text-red-500 hover:text-red-700 ml-2" data-id="${conv.id}">
                        🗑️
                    </button>
                </div>
            `;
            
            div.addEventListener('click', (e) => {
                if (!e.target.classList.contains('delete-conv-btn')) {
                    loadConversation(conv.id);
                }
            });
            
            const deleteBtn = div.querySelector('.delete-conv-btn');
            deleteBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                deleteConversation(conv.id);
            });
            
            list.appendChild(div);
        });
    } catch (error) {
        console.error('Error loading conversations:', error);
    }
}

// تحميل محادثة محددة
async function loadConversation(id) {
    try {
        const response = await fetch(`/conversations/${id}`);
        const conversation = await response.json();
        
        currentConversationId = id;
        
        const messagesDiv = document.getElementById('chat-messages');
        messagesDiv.innerHTML = '';
        
        conversation.messages.forEach(msg => {
            addMessageToUI(msg.content, msg.role === 'user' ? 'user' : 'bot', msg.ai_source);
        });
        
        loadConversations(); // تحديث القائمة لتحديد المحادثة الحالية
    } catch (error) {
        console.error('Error loading conversation:', error);
    }
}

// إنشاء محادثة جديدة
document.getElementById('new-chat-btn').addEventListener('click', async () => {
    try {
        const response = await fetch('/conversations', { method: 'POST' });
        const conversation = await response.json();
        
        currentConversationId = conversation.id;
        document.getElementById('chat-messages').innerHTML = '';
        
        loadConversations();
    } catch (error) {
        console.error('Error creating conversation:', error);
    }
});

// حذف محادثة
async function deleteConversation(id) {
    if (!confirm('هل أنت متأكد من حذف هذه المحادثة؟')) return;
    
    try {
        await fetch(`/conversations/${id}`, { method: 'DELETE' });
        
        if (currentConversationId === id) {
            currentConversationId = null;
            document.getElementById('chat-messages').innerHTML = '';
        }
        
        loadConversations();
    } catch (error) {
        console.error('Error deleting conversation:', error);
    }
}

// حذف جميع المحادثات
document.getElementById('delete-all-btn').addEventListener('click', async () => {
    if (!confirm('هل أنت متأكد من حذف جميع المحادثات؟')) return;
    
    try {
        await fetch('/conversations-all', { method: 'DELETE' });
        
        currentConversationId = null;
        document.getElementById('chat-messages').innerHTML = '';
        loadConversations();
        
        alert('تم حذف جميع المحادثات بنجاح');
    } catch (error) {
        console.error('Error deleting all conversations:', error);
    }
});

// Update description based on selected mode
document.querySelectorAll('input[name="ai-mode"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const descriptions = {
            'auto': '<strong>التلقائي:</strong> النظام يختار الأنسب بناءً على نوع السؤال (مهام معقدة → Manus، أسئلة سريعة → Gemini المجاني)',
            'manus': '<strong>Manus AI:</strong> أسلوب مهام متقدمة - للبحث، إنشاء المستندات، تطوير الويب، والتحليل العميق',
            'gemini': '<strong>Gemini (مجاني):</strong> من Google - للدردشة السريعة والأسئلة العامة، مجاني بالكامل!',
            'openai': '<strong>OpenAI:</strong> للدردشة السريعة والأسئلة العامة'
        };
        document.getElementById('mode-description').innerHTML = descriptions[this.value];
    });
});

// Handle form submission
document.getElementById('chat-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const input = document.getElementById('message-input');
    const message = input.value.trim();
    
    if (!message) return;
    
    // Add user message to UI
    addMessageToUI(message, 'user');
    input.value = '';
    
    // Show loading indicator
    const loadingId = addMessageToUI('⏳ جاري التفكير...', 'bot');
    
    try {
        const aiMode = document.querySelector('input[name="ai-mode"]:checked').value;
        
        const response = await fetch('/chat/send', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ 
                message: message,
                ai_mode: aiMode,
                conversation_id: currentConversationId
            })
        });
        
        const data = await response.json();
        
        // Remove loading indicator
        document.getElementById(loadingId).remove();
        
        if (data.success) {
            // Update current conversation ID
            if (data.conversation_id) {
                currentConversationId = data.conversation_id;
            }
            
            // Add bot response
            const sourceEmoji = {
                'learning_database': '📚',
                'gemini': '🌟',
                'manus': '🤖',
                'openai': '⚡'
            };
            
            const emoji = sourceEmoji[data.source] || '🎯';
            addMessageToUI(`${emoji} ${data.response}`, 'bot', data.source);
            
            // تحديث قائمة المحادثات
            loadConversations();
        } else {
            addMessageToUI(`🎯 ${data.response}`, 'bot', 'error');
        }
    } catch (error) {
        document.getElementById(loadingId).remove();
        addMessageToUI('عذراً، حدث خطأ في الاتصال بالخادم.', 'bot', 'error');
    }
});

function addMessageToUI(message, sender, source = null) {
    const messagesDiv = document.getElementById('chat-messages');
    const messageId = 'msg-' + Date.now();
    
    const messageDiv = document.createElement('div');
    messageDiv.id = messageId;
    messageDiv.className = `flex ${sender === 'user' ? 'justify-end' : 'justify-start'}`;
    
    const bgColor = sender === 'user' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-800';
    
    messageDiv.innerHTML = `
        <div class="${bgColor} rounded-lg px-4 py-3 max-w-xl">
            ${message}
        </div>
    `;
    
    messagesDiv.appendChild(messageDiv);
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
    
    return messageId;
}

// تحميل المحادثات عند تحميل الصفحة
loadConversations();

// تصدير جميع المحادثات إلى TXT
document.getElementById('export-all-txt-btn').addEventListener('click', function() {
    window.location.href = '/conversations/export-all?format=txt';
});

// تصدير جميع المحادثات إلى HTML
document.getElementById('export-all-html-btn').addEventListener('click', function() {
    window.location.href = '/conversations/export-all?format=html';
});
</script>
@endsection
