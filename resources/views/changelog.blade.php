@extends('layouts.app')
@section('title', 'سجل التحديثات')
@section('content')
<div class="max-w-4xl mx-auto">
    <h1 class="text-4xl font-bold mb-8">📋 سجل التحديثات</h1>
    <div class="space-y-6">
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-2xl font-bold mb-2">النسخة 3.0.0 - Laravel</h3>
            <p class="text-gray-600 mb-4">17 نوفمبر 2025</p>
            <ul class="list-disc list-inside space-y-2 text-gray-700">
                <li>تحويل كامل إلى Laravel Framework</li>
                <li>Eloquent ORM للتعامل مع قاعدة البيانات</li>
                <li>Blade Templates للواجهات</li>
                <li>RESTful API</li>
                <li>أداء محسّن</li>
            </ul>
        </div>
    </div>
</div>
@endsection
