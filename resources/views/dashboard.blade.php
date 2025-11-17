@extends('layouts.app')

@section('title', 'لوحة التحكم - الوكيل')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-800">🏠 لوحة التحكم</h1>
        <p class="text-gray-600 mt-2">مرحباً بك في نظام الوكيل - المساعد الذكي</p>
    </div>
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- إجمالي المحادثات -->
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 p-6 rounded-xl shadow-lg text-white transform hover:scale-105 transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium">إجمالي المحادثات</p>
                    <p class="text-4xl font-bold mt-2">{{ $totalConversations }}</p>
                    <p class="text-blue-100 text-xs mt-2">+{{ $todayConversations }} اليوم</p>
                </div>
                <div class="text-5xl opacity-20">💬</div>
            </div>
        </div>

        <!-- إجمالي الرسائل -->
        <div class="bg-gradient-to-br from-green-500 to-green-600 p-6 rounded-xl shadow-lg text-white transform hover:scale-105 transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium">إجمالي الرسائل</p>
                    <p class="text-4xl font-bold mt-2">{{ $totalMessages }}</p>
                    <p class="text-green-100 text-xs mt-2">+{{ $todayMessages }} اليوم</p>
                </div>
                <div class="text-5xl opacity-20">📨</div>
            </div>
        </div>

        <!-- متوسط الرسائل -->
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 p-6 rounded-xl shadow-lg text-white transform hover:scale-105 transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm font-medium">متوسط الرسائل</p>
                    <p class="text-4xl font-bold mt-2">{{ $avgMessagesPerConversation }}</p>
                    <p class="text-purple-100 text-xs mt-2">لكل محادثة</p>
                </div>
                <div class="text-5xl opacity-20">📊</div>
            </div>
        </div>

        <!-- أكثر الأيام نشاطاً -->
        <div class="bg-gradient-to-br from-orange-500 to-orange-600 p-6 rounded-xl shadow-lg text-white transform hover:scale-105 transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-100 text-sm font-medium">أكثر الأيام نشاطاً</p>
                    <p class="text-2xl font-bold mt-2">
                        @if($busiestDay)
                            {{ \Carbon\Carbon::parse($busiestDay->date)->locale('ar')->isoFormat('D MMM') }}
                        @else
                            -
                        @endif
                    </p>
                    <p class="text-orange-100 text-xs mt-2">
                        @if($busiestDay)
                            {{ $busiestDay->count }} رسالة
                        @else
                            لا توجد بيانات
                        @endif
                    </p>
                </div>
                <div class="text-5xl opacity-20">🔥</div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- الرسم البياني الأسبوعي -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-2xl font-bold mb-6 flex items-center">
                <span class="text-3xl ml-3">📈</span>
                النشاط الأسبوعي
            </h2>
            <div class="h-64">
                <canvas id="weeklyChart"></canvas>
            </div>
        </div>

        <!-- إحصائيات AI Providers -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-2xl font-bold mb-6 flex items-center">
                <span class="text-3xl ml-3">🤖</span>
                استخدام AI
            </h2>
            <div class="space-y-4">
                @forelse($aiStats as $stat)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center">
                        <span class="text-2xl ml-3">
                            @if($stat->ai_provider === 'gemini')
                                🌟
                            @elseif($stat->ai_provider === 'manus')
                                🤖
                            @elseif($stat->ai_provider === 'openai')
                                ⚡
                            @else
                                🎯
                            @endif
                        </span>
                        <span class="font-medium">{{ ucfirst($stat->ai_provider) }}</span>
                    </div>
                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-bold">
                        {{ $stat->count }}
                    </span>
                </div>
                @empty
                <p class="text-gray-500 text-center py-8">لا توجد بيانات</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- آخر المحادثات -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h2 class="text-2xl font-bold mb-6 flex items-center justify-between">
            <span class="flex items-center">
                <span class="text-3xl ml-3">💬</span>
                آخر المحادثات
            </span>
            <a href="/chat" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                عرض الكل ←
            </a>
        </h2>
        <div class="space-y-3">
            @forelse($recentConversations as $conversation)
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                <div class="flex-1">
                    <div class="flex items-center">
                        <span class="text-2xl ml-3">💬</span>
                        <div>
                            <h3 class="font-semibold text-gray-800">{{ $conversation->title }}</h3>
                            <p class="text-sm text-gray-600">
                                {{ $conversation->messages->count() }} رسالة
                                • 
                                {{ $conversation->created_at->locale('ar')->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                </div>
                <a href="/chat?conversation={{ $conversation->id }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm">
                    فتح
                </a>
            </div>
            @empty
            <div class="text-center py-12">
                <div class="text-6xl mb-4">💬</div>
                <p class="text-gray-500 text-lg mb-4">لا توجد محادثات حتى الآن</p>
                <a href="/chat" class="inline-block px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    ابدأ محادثة جديدة
                </a>
            </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// الرسم البياني الأسبوعي
const weeklyData = @json($weeklyStats);
const ctx = document.getElementById('weeklyChart').getContext('2d');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: weeklyData.map(d => d.label),
        datasets: [
            {
                label: 'المحادثات',
                data: weeklyData.map(d => d.conversations),
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            },
            {
                label: 'الرسائل',
                data: weeklyData.map(d => d.messages),
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                tension: 0.4,
                fill: true
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                position: 'top',
                rtl: true
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0
                }
            }
        }
    }
});
</script>
@endsection
