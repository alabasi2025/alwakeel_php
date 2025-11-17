<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'الوكيل - Alwakeel Agent')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar { width: 250px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .sidebar a { transition: all 0.3s; }
        .sidebar a:hover { background: rgba(255,255,255,0.1); transform: translateX(-5px); }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="sidebar text-white p-6 shadow-2xl">
            <h1 class="text-2xl font-bold mb-8">🤖 الوكيل</h1>
            <nav class="space-y-2">
                <a href="{{ route('dashboard') }}" class="block px-4 py-3 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-white/20' : '' }}">
                    🏠 الرئيسية
                </a>
                <a href="{{ route('chat') }}" class="block px-4 py-3 rounded-lg {{ request()->routeIs('chat') ? 'bg-white/20' : '' }}">
                    💬 الدردشة الذكية
                </a>
                <a href="{{ route('integrations') }}" class="block px-4 py-3 rounded-lg {{ request()->routeIs('integrations') ? 'bg-white/20' : '' }}">
                    🔗 التكاملات
                </a>
                <a href="{{ route('sync') }}" class="block px-4 py-3 rounded-lg {{ request()->routeIs('sync') ? 'bg-white/20' : '' }}">
                    🔄 محرك المزامنة
                </a>
                <a href="{{ route('backup') }}" class="block px-4 py-3 rounded-lg {{ request()->routeIs('backup') ? 'bg-white/20' : '' }}">
                    💾 النسخ الاحتياطي
                </a>
                <a href="{{ route('changelog') }}" class="block px-4 py-3 rounded-lg {{ request()->routeIs('changelog') ? 'bg-white/20' : '' }}">
                    📋 سجل التحديثات
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8">
            @yield('content')
        </main>
    </div>
</body>
</html>
