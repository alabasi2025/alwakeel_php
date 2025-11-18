@extends('layouts.app')

@section('title', $project['name'] . ' - خارطة الطريق')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-2xl shadow-2xl p-8 mb-8 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-4xl font-bold mb-2">{{ $project['icon'] }} {{ $project['name'] }}</h1>
                <p class="text-purple-100">{{ $project['description'] }}</p>
            </div>
            <div class="text-left">
                <div class="text-5xl font-bold">{{ $stats['completion_rate'] }}%</div>
                <div class="text-sm text-purple-200">نسبة الإنجاز</div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-gradient-to-br from-purple-500 to-purple-700 rounded-xl shadow-lg p-6 text-white">
            <div class="text-4xl font-bold mb-2">{{ $stats['total'] }}</div>
            <div class="text-purple-100">إجمالي المهام</div>
        </div>
        <div class="bg-gradient-to-br from-green-500 to-green-700 rounded-xl shadow-lg p-6 text-white">
            <div class="text-4xl font-bold mb-2">{{ $stats['completed'] }}</div>
            <div class="text-green-100">مكتمل</div>
        </div>
        <div class="bg-gradient-to-br from-orange-500 to-orange-700 rounded-xl shadow-lg p-6 text-white">
            <div class="text-4xl font-bold mb-2">{{ $stats['in_progress'] }}</div>
            <div class="text-orange-100">قيد التنفيذ</div>
        </div>
        <div class="bg-gradient-to-br from-gray-500 to-gray-700 rounded-xl shadow-lg p-6 text-white">
            <div class="text-4xl font-bold mb-2">{{ $stats['pending'] }}</div>
            <div class="text-gray-100">قيد الانتظار</div>
        </div>
    </div>

    <!-- Roadmap Items by Phase -->
    @php
        $phases = $items->groupBy('phase');
    @endphp

    @foreach($phases as $phaseName => $phaseItems)
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b-2 border-purple-500 pb-3">
            {{ $phaseName }}
        </h2>

        <div class="space-y-4">
            @foreach($phaseItems as $item)
            <div class="border rounded-lg p-5 hover:shadow-md transition-all duration-200
                @if($item->status === 'completed') border-green-300 bg-green-50
                @elseif($item->status === 'in_progress') border-orange-300 bg-orange-50
                @else border-gray-200 bg-gray-50
                @endif">
                
                <div class="flex items-start justify-between mb-3">
                    <div class="flex-1">
                        <h3 class="text-xl font-bold text-gray-800 mb-2">{{ $item->title }}</h3>
                        <p class="text-gray-600 mb-3">{{ $item->description }}</p>
                        
                        <!-- Status Badge -->
                        <div class="flex items-center gap-3 mb-3">
                            @if($item->status === 'completed')
                                <span class="px-3 py-1 bg-green-500 text-white rounded-full text-sm font-semibold">
                                    ✅ مكتمل
                                </span>
                            @elseif($item->status === 'in_progress')
                                <span class="px-3 py-1 bg-orange-500 text-white rounded-full text-sm font-semibold">
                                    🔄 قيد التنفيذ
                                </span>
                            @else
                                <span class="px-3 py-1 bg-gray-400 text-white rounded-full text-sm font-semibold">
                                    ⏸️ قيد الانتظار
                                </span>
                            @endif

                            <span class="text-sm text-gray-500 flex items-center gap-1">
                                📅 {{ $item->estimated_days }} يوم
                            </span>
                        </div>

                        <!-- Progress Bar -->
                        <div class="mb-2">
                            <div class="flex justify-between text-sm text-gray-600 mb-1">
                                <span>التقدم</span>
                                <span class="font-bold">{{ $item->progress }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-500
                                    @if($item->status === 'completed') bg-green-500
                                    @elseif($item->status === 'in_progress') bg-orange-500
                                    @else bg-gray-400
                                    @endif"
                                    style="width: {{ $item->progress }}%">
                                </div>
                            </div>
                        </div>

                        <!-- Dates -->
                        @if($item->start_date || $item->end_date)
                        <div class="flex gap-4 text-sm text-gray-500 mt-2">
                            @if($item->start_date)
                                <span>🚀 البدء: {{ $item->start_date }}</span>
                            @endif
                            @if($item->end_date)
                                <span>🏁 الانتهاء: {{ $item->end_date }}</span>
                            @endif
                        </div>
                        @endif

                        <!-- Notes -->
                        @if($item->notes)
                        <div class="mt-3 p-3 bg-blue-50 border-r-4 border-blue-400 rounded">
                            <p class="text-sm text-gray-700">📝 {{ $item->notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach

    @if($items->isEmpty())
    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-8 text-center">
        <div class="text-6xl mb-4">📋</div>
        <h3 class="text-xl font-bold text-gray-700 mb-2">لا توجد مهام حالياً</h3>
        <p class="text-gray-600">سيتم إضافة المهام قريباً...</p>
    </div>
    @endif

    <!-- Summary -->
    <div class="bg-gradient-to-r from-indigo-100 to-purple-100 rounded-xl shadow-lg p-6 mt-8">
        <h3 class="text-xl font-bold text-gray-800 mb-4">📊 ملخص المشروع</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
            <div>
                <div class="text-3xl font-bold text-purple-600">{{ $stats['total'] }}</div>
                <div class="text-sm text-gray-600">إجمالي المهام</div>
            </div>
            <div>
                <div class="text-3xl font-bold text-green-600">{{ $stats['completed'] }}</div>
                <div class="text-sm text-gray-600">مكتمل</div>
            </div>
            <div>
                <div class="text-3xl font-bold text-orange-600">{{ $stats['in_progress'] }}</div>
                <div class="text-sm text-gray-600">قيد التنفيذ</div>
            </div>
            <div>
                <div class="text-3xl font-bold text-purple-600">{{ $stats['completion_rate'] }}%</div>
                <div class="text-sm text-gray-600">نسبة الإنجاز</div>
            </div>
        </div>
    </div>
</div>
@endsection
