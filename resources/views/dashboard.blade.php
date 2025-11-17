@extends('layouts.app')

@section('title', 'لوحة التحكم - الوكيل')

@section('content')
<div class="max-w-7xl mx-auto">
    <h1 class="text-4xl font-bold text-gray-800 mb-8">🏠 لوحة التحكم</h1>
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-xl shadow-lg">
            <div class="text-3xl mb-2">📝</div>
            <h3 class="text-gray-600 text-sm">الأوامر</h3>
            <p class="text-3xl font-bold text-blue-600">{{ $commandsCount }}</p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-lg">
            <div class="text-3xl mb-2">✅</div>
            <h3 class="text-gray-600 text-sm">النتائج</h3>
            <p class="text-3xl font-bold text-green-600">{{ $resultsCount }}</p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-lg">
            <div class="text-3xl mb-2">🔗</div>
            <h3 class="text-gray-600 text-sm">التكاملات</h3>
            <p class="text-3xl font-bold text-purple-600">{{ $integrationsCount }}</p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-lg">
            <div class="text-3xl mb-2">🔄</div>
            <h3 class="text-gray-600 text-sm">المزامنات</h3>
            <p class="text-3xl font-bold text-orange-600">{{ $syncLogsCount }}</p>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h2 class="text-2xl font-bold mb-4">📊 النشاط الأخير</h2>
        <div class="space-y-3">
            @forelse($recentLogs as $log)
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                <div>
                    <span class="font-semibold">{{ $log->sync_type }}</span>
                    <p class="text-sm text-gray-600">{{ $log->message }}</p>
                </div>
                <span class="px-3 py-1 rounded-full text-sm {{ $log->status === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    {{ $log->status }}
                </span>
            </div>
            @empty
            <p class="text-gray-500 text-center py-8">لا يوجد نشاط حتى الآن</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
