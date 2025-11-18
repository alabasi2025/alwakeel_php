@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 p-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">
                @if($project === 'alabasi')
                    💼 نظام الأباسي المحاسبي
                @elseif($project === 'wakeel')
                    🤖 نظام الوكيل الذكي
                @else
                    🔗 الربط والتكامل
                @endif
            </h1>
            <p class="text-gray-600">
                @if($project === 'alabasi')
                    Laravel 10 + PHP 8.2 | https://alabasi.es
                @elseif($project === 'wakeel')
                    AI-Powered Assistant | التعلم الذاتي
                @else
                    API Integration | الوكيل ↔ الأباسي
                @endif
            </p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl p-6 text-white shadow-lg">
                <div class="text-3xl font-bold">{{ $stats['total'] }}</div>
                <div class="text-sm opacity-90">إجمالي المهام</div>
            </div>
            <div class="bg-gradient-to-br from-pink-500 to-rose-600 rounded-xl p-6 text-white shadow-lg">
                <div class="text-3xl font-bold">{{ $stats['completed'] }}</div>
                <div class="text-sm opacity-90">مكتمل</div>
            </div>
            <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl p-6 text-white shadow-lg">
                <div class="text-3xl font-bold">{{ $stats['in_progress'] }}</div>
                <div class="text-sm opacity-90">قيد التنفيذ</div>
            </div>
            <div class="bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl p-6 text-white shadow-lg">
                <div class="text-3xl font-bold">{{ number_format($stats['percentage'], 0) }}%</div>
                <div class="text-sm opacity-90">نسبة الإنجاز</div>
            </div>
        </div>

        <!-- Roadmap Items by Phase -->
        @php
            $phases = $items->groupBy('phase');
        @endphp

        @foreach($phases as $phase => $phaseItems)
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 border-r-4 border-blue-500 pr-4">
                {{ $phase }}
            </h2>

            <div class="space-y-4">
                @foreach($phaseItems as $item)
                <div class="bg-gray-50 rounded-lg p-4 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="text-lg font-bold text-gray-800">{{ $item->title }}</h3>
                                <span class="px-3 py-1 rounded-full text-sm font-medium
                                    @if($item->status === 'completed') bg-green-100 text-green-800
                                    @elseif($item->status === 'in_progress') bg-blue-100 text-blue-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    @if($item->status === 'completed')
                                        ✅ مكتمل
                                    @elseif($item->status === 'in_progress')
                                        ⚡ قيد التنفيذ
                                    @else
                                        ⏸️ قيد الانتظار
                                    @endif
                                </span>
                                @if($item->hasDetails())
                                <span class="px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                                    📖 يحتوي على تفاصيل
                                </span>
                                @endif
                            </div>
                            <p class="text-gray-600 mb-3">{{ $item->description }}</p>
                            <div class="flex items-center gap-4 text-sm text-gray-500">
                                <span>{{ $item->progress }}%</span>
                                @if($item->estimated_days)
                                <span>{{ $item->estimated_days }} يوم</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex flex-col gap-2">
                            <a href="{{ route('roadmap.show', $item) }}" 
                               class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors text-center">
                                عرض التفاصيل
                            </a>
                            @if(!$item->hasDetails())
                            <a href="{{ route('features.edit', $item) }}" 
                               class="px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition-colors text-center">
                                إضافة تفاصيل
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach

        <!-- Recent Updates -->
        @if($recentUpdates->count() > 0)
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 border-r-4 border-green-500 pr-4">
                🕒 آخر التحديثات
            </h2>
            <div class="space-y-3">
                @foreach($recentUpdates as $update)
                <div class="flex items-center gap-4 p-3 bg-gray-50 rounded-lg">
                    <span class="text-2xl">
                        @if($update->type === 'feature') ✨
                        @elseif($update->type === 'improvement') ⚡
                        @elseif($update->type === 'fix') 🐛
                        @elseif($update->type === 'security') 🔒
                        @else 🚀
                        @endif
                    </span>
                    <div class="flex-1">
                        <div class="font-medium text-gray-800">{{ $update->title }}</div>
                        <div class="text-sm text-gray-500">{{ $update->committed_at->diffForHumans() }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
