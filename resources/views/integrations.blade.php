@extends('layouts.app')
@section('title', 'التكاملات')
@section('content')
<div class="max-w-4xl mx-auto">
    <h1 class="text-4xl font-bold mb-8">🔗 التكاملات</h1>
    <div class="grid gap-6">
        @foreach($integrations as $integration)
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-2xl font-bold">{{ $integration->service_name }}</h3>
                <span class="px-3 py-1 rounded-full {{ $integration->is_enabled === 'true' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                    {{ $integration->is_enabled === 'true' ? 'مفعّل' : 'معطّل' }}
                </span>
            </div>
            <form action="{{ route('integrations.save') }}" method="POST">
                @csrf
                <input type="hidden" name="service_name" value="{{ $integration->service_name }}">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    {{ $integration->is_enabled === 'true' ? 'تعطيل' : 'تفعيل' }}
                </button>
            </form>
        </div>
        @endforeach
    </div>
</div>
@endsection
