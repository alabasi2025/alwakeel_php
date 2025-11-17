@extends('layouts.app')
@section('title', 'محرك المزامنة')
@section('content')
<div class="max-w-4xl mx-auto">
    <h1 class="text-4xl font-bold mb-8">🔄 محرك المزامنة</h1>
    <div class="grid gap-6">
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-2xl font-bold mb-4">🐙 GitHub</h3>
            <div class="flex gap-4">
                <form action="{{ route('sync.github-pull') }}" method="POST">
                    @csrf
                    <button class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Pull من GitHub</button>
                </form>
                <form action="{{ route('sync.github-push') }}" method="POST">
                    @csrf
                    <button class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700">Push إلى GitHub</button>
                </form>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-2xl font-bold mb-4">🌐 Hostinger</h3>
            <form action="{{ route('sync.hostinger-deploy') }}" method="POST">
                @csrf
                <button class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700">نشر على Hostinger</button>
            </form>
        </div>
    </div>
</div>
@endsection
