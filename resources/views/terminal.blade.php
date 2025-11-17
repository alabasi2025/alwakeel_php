@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-900 text-green-400 p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold mb-2">⚡ الطرفية (Terminal)</h1>
                <p class="text-gray-400">تنفيذ أوامر PowerShell, CMD, Bash</p>
            </div>
            <div class="flex gap-2">
                <button onclick="clearHistory()" class="px-4 py-2 bg-red-600 hover:bg-red-700 rounded text-white">
                    🗑️ مسح السجل
                </button>
                <button onclick="clearTerminal()" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded text-white">
                    🔄 مسح الشاشة
                </button>
            </div>
        </div>

        <!-- Shell Type Selector -->
        <div class="mb-4 flex gap-4 items-center bg-gray-800 p-4 rounded-lg">
            <label class="text-white font-semibold">نوع Shell:</label>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="radio" name="shell_type" value="powershell" checked class="form-radio text-blue-500">
                <span>💙 PowerShell</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="radio" name="shell_type" value="cmd" class="form-radio text-yellow-500">
                <span>⚡ CMD</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="radio" name="shell_type" value="bash" class="form-radio text-green-500">
                <span>🐧 Bash</span>
            </label>
        </div>

        <!-- Working Directory -->
        <div class="mb-4 bg-gray-800 p-4 rounded-lg">
            <label class="text-white font-semibold mb-2 block">📁 مجلد العمل:</label>
            <input type="text" id="working_directory" value="C:\xampp\htdocs\alwakeel_php" 
                   class="w-full bg-gray-700 text-white px-4 py-2 rounded border border-gray-600 focus:border-blue-500 focus:outline-none">
        </div>

        <!-- Terminal Output -->
        <div id="terminal-output" class="bg-black rounded-lg p-6 mb-4 h-96 overflow-y-auto font-mono text-sm">
            <div class="text-green-400">
                <span class="text-blue-400">الوكيل Terminal v1.0</span><br>
                <span class="text-gray-500">اكتب أمرك أدناه...</span><br><br>
            </div>
        </div>

        <!-- Command Input -->
        <div class="bg-gray-800 rounded-lg p-4">
            <div class="flex gap-2">
                <span class="text-blue-400 font-bold">PS&gt;</span>
                <input type="text" id="command-input" 
                       placeholder="اكتب الأمر هنا... (مثال: Get-ChildItem, dir, ls)"
                       class="flex-1 bg-gray-900 text-green-400 px-4 py-2 rounded border border-gray-700 focus:border-green-500 focus:outline-none font-mono"
                       onkeypress="handleKeyPress(event)">
                <button onclick="executeCommand()" 
                        class="px-6 py-2 bg-green-600 hover:bg-green-700 rounded text-white font-semibold">
                    ▶️ تنفيذ
                </button>
            </div>
            <div class="mt-2 text-xs text-gray-500">
                اضغط <kbd class="bg-gray-700 px-2 py-1 rounded">Enter</kbd> للتنفيذ
            </div>
        </div>

        <!-- Quick Commands -->
        <div class="mt-6 bg-gray-800 rounded-lg p-4">
            <h3 class="text-white font-semibold mb-3">⚡ أوامر سريعة:</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                <button onclick="quickCommand('Get-ChildItem')" class="bg-gray-700 hover:bg-gray-600 px-3 py-2 rounded text-sm">
                    📁 عرض الملفات (PS)
                </button>
                <button onclick="quickCommand('dir')" class="bg-gray-700 hover:bg-gray-600 px-3 py-2 rounded text-sm">
                    📁 عرض الملفات (CMD)
                </button>
                <button onclick="quickCommand('php artisan migrate')" class="bg-gray-700 hover:bg-gray-600 px-3 py-2 rounded text-sm">
                    🗄️ تشغيل Migrations
                </button>
                <button onclick="quickCommand('php artisan serve')" class="bg-gray-700 hover:bg-gray-600 px-3 py-2 rounded text-sm">
                    🚀 تشغيل المشروع
                </button>
                <button onclick="quickCommand('composer install')" class="bg-gray-700 hover:bg-gray-600 px-3 py-2 rounded text-sm">
                    📦 تثبيت المكتبات
                </button>
                <button onclick="quickCommand('git status')" class="bg-gray-700 hover:bg-gray-600 px-3 py-2 rounded text-sm">
                    🔀 حالة Git
                </button>
                <button onclick="quickCommand('php -v')" class="bg-gray-700 hover:bg-gray-600 px-3 py-2 rounded text-sm">
                    🐘 إصدار PHP
                </button>
                <button onclick="quickCommand('node -v')" class="bg-gray-700 hover:bg-gray-600 px-3 py-2 rounded text-sm">
                    📗 إصدار Node
                </button>
            </div>
        </div>

        <!-- Command History -->
        <div class="mt-6 bg-gray-800 rounded-lg p-4">
            <h3 class="text-white font-semibold mb-3">📜 سجل الأوامر:</h3>
            <div id="command-history" class="space-y-2 max-h-64 overflow-y-auto">
                @forelse($recentCommands as $cmd)
                <div class="bg-gray-700 p-3 rounded text-sm">
                    <div class="flex justify-between items-start mb-1">
                        <span class="text-blue-400 font-mono">{{ $cmd->command }}</span>
                        <span class="text-xs text-gray-400">{{ $cmd->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="flex gap-2 text-xs">
                        <span class="px-2 py-1 rounded {{ $cmd->status === 'success' ? 'bg-green-600' : 'bg-red-600' }}">
                            {{ $cmd->status }}
                        </span>
                        <span class="px-2 py-1 bg-gray-600 rounded">{{ $cmd->shell_type }}</span>
                        <span class="px-2 py-1 bg-gray-600 rounded">{{ $cmd->execution_time }}s</span>
                    </div>
                </div>
                @empty
                <p class="text-gray-500 text-center">لا توجد أوامر محفوظة</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script>
let commandHistory = [];
let historyIndex = -1;

function executeCommand() {
    const command = document.getElementById('command-input').value.trim();
    if (!command) return;

    const shellType = document.querySelector('input[name="shell_type"]:checked').value;
    const workingDir = document.getElementById('working_directory').value;

    // عرض الأمر في Terminal
    appendToTerminal(`<span class="text-blue-400">${shellType}></span> ${escapeHtml(command)}`);
    appendToTerminal('<span class="text-yellow-400">⏳ جاري التنفيذ...</span>');

    // إرسال الأمر للخادم
    fetch('/terminal/execute', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            command: command,
            shell_type: shellType,
            working_directory: workingDir
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            appendToTerminal(`<span class="text-green-400">${escapeHtml(data.output)}</span>`);
            appendToTerminal(`<span class="text-gray-500">✅ Exit Code: ${data.exit_code} | Time: ${data.execution_time}s</span>`);
        } else {
            appendToTerminal(`<span class="text-red-400">❌ Error: ${escapeHtml(data.error)}</span>`);
        }
        appendToTerminal('');
    })
    .catch(error => {
        appendToTerminal(`<span class="text-red-400">❌ Network Error: ${error.message}</span>`);
        appendToTerminal('');
    });

    // حفظ في السجل
    commandHistory.push(command);
    historyIndex = commandHistory.length;

    // مسح حقل الإدخال
    document.getElementById('command-input').value = '';
}

function appendToTerminal(text) {
    const output = document.getElementById('terminal-output');
    output.innerHTML += text + '<br>';
    output.scrollTop = output.scrollHeight;
}

function clearTerminal() {
    document.getElementById('terminal-output').innerHTML = `
        <div class="text-green-400">
            <span class="text-blue-400">الوكيل Terminal v1.0</span><br>
            <span class="text-gray-500">تم مسح الشاشة...</span><br><br>
        </div>
    `;
}

function clearHistory() {
    if (!confirm('هل تريد مسح سجل الأوامر؟')) return;

    fetch('/terminal/clear', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('command-history').innerHTML = '<p class="text-gray-500 text-center">لا توجد أوامر محفوظة</p>';
            appendToTerminal('<span class="text-green-400">✅ تم مسح السجل</span>');
        }
    });
}

function quickCommand(cmd) {
    document.getElementById('command-input').value = cmd;
    document.getElementById('command-input').focus();
}

function handleKeyPress(event) {
    if (event.key === 'Enter') {
        executeCommand();
    } else if (event.key === 'ArrowUp') {
        event.preventDefault();
        if (historyIndex > 0) {
            historyIndex--;
            document.getElementById('command-input').value = commandHistory[historyIndex];
        }
    } else if (event.key === 'ArrowDown') {
        event.preventDefault();
        if (historyIndex < commandHistory.length - 1) {
            historyIndex++;
            document.getElementById('command-input').value = commandHistory[historyIndex];
        } else {
            historyIndex = commandHistory.length;
            document.getElementById('command-input').value = '';
        }
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
@endsection
