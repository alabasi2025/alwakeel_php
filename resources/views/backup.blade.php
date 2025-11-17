@extends('layouts.app')
@section('title', 'النسخ الاحتياطي')
@section('content')
<div class="max-w-4xl mx-auto">
    <h1 class="text-4xl font-bold mb-8">💾 النسخ الاحتياطي</h1>
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <form action="{{ route('backup.create') }}" method="POST">
            @csrf
            <button class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">إنشاء نسخة احتياطية جديدة</button>
        </form>
    </div>
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h2 class="text-2xl font-bold mb-4">📦 النسخ المتاحة</h2>
        <div class="space-y-3">
            @forelse($backups as $backup)
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                <span>{{ $backup }}</span>
                <a href="{{ route('backup.download', $backup) }}" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">تحميل</a>
            </div>
            @empty
            <p class="text-gray-500 text-center py-8">لا توجد نسخ احتياطية</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
