@extends('layouts.app')
@section('title', 'التكاملات')
@section('content')
<div class="max-w-6xl mx-auto">
    <h1 class="text-4xl font-bold mb-8">🔗 التكاملات</h1>
    
    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
        {{ session('success') }}
    </div>
    @endif
    
    @if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
        {{ session('error') }}
    </div>
    @endif
    
    <!-- OpenAI Integration Card -->
    <div class="bg-gradient-to-r from-green-50 to-blue-50 rounded-xl shadow-lg p-8 mb-6 border-2 border-blue-200">
        <div class="flex items-start justify-between mb-6">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 bg-white rounded-xl flex items-center justify-center shadow-md">
                    <span class="text-4xl">🤖</span>
                </div>
                <div>
                    <h3 class="text-3xl font-bold text-gray-800">OpenAI</h3>
                    <p class="text-gray-600 mt-1">محرك الذكاء الاصطناعي الرئيسي للوكيل</p>
                </div>
            </div>
            <span id="openai-status" class="px-4 py-2 rounded-full font-bold {{ $openai_enabled ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                {{ $openai_enabled ? '✅ مفعّل' : '⚠️ معطّل' }}
            </span>
        </div>
        
        <form action="{{ route('integrations.openai.save') }}" method="POST" class="space-y-4">
            @csrf
            
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">
                    🔑 مفتاح API
                </label>
                <input 
                    type="password" 
                    name="api_key" 
                    value="{{ $openai_key ?? '' }}"
                    class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
                    placeholder="sk-..."
                    required
                >
                <p class="text-sm text-gray-500 mt-1">
                    احصل على مفتاح API من <a href="https://platform.openai.com/api-keys" target="_blank" class="text-blue-600 hover:underline">platform.openai.com</a>
                </p>
            </div>
            
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">
                    🎯 النموذج (Model)
                </label>
                <select 
                    name="model" 
                    class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
                >
                    <option value="gpt-4" {{ ($openai_model ?? 'gpt-4') == 'gpt-4' ? 'selected' : '' }}>GPT-4 (الأقوى)</option>
                    <option value="gpt-4-turbo" {{ ($openai_model ?? '') == 'gpt-4-turbo' ? 'selected' : '' }}>GPT-4 Turbo (أسرع)</option>
                    <option value="gpt-3.5-turbo" {{ ($openai_model ?? '') == 'gpt-3.5-turbo' ? 'selected' : '' }}>GPT-3.5 Turbo (اقتصادي)</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">
                    🌡️ درجة الإبداع (Temperature)
                </label>
                <input 
                    type="range" 
                    name="temperature" 
                    min="0" 
                    max="2" 
                    step="0.1" 
                    value="{{ $openai_temperature ?? 0.7 }}"
                    class="w-full"
                    oninput="document.getElementById('temp-value').textContent = this.value"
                >
                <div class="flex justify-between text-sm text-gray-600 mt-1">
                    <span>دقيق (0)</span>
                    <span id="temp-value" class="font-bold">{{ $openai_temperature ?? 0.7 }}</span>
                    <span>إبداعي (2)</span>
                </div>
            </div>
            
            <div class="flex items-center gap-4 pt-4">
                <button 
                    type="submit" 
                    class="px-6 py-3 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition"
                >
                    💾 حفظ الإعدادات
                </button>
                
                <button 
                    type="button" 
                    onclick="testOpenAI()"
                    class="px-6 py-3 bg-green-600 text-white font-bold rounded-lg hover:bg-green-700 transition"
                >
                    🧪 اختبار الاتصال
                </button>
            </div>
        </form>
        
        <div id="test-result" class="mt-4 hidden"></div>
    </div>
    
    <!-- Other Integrations -->
    <h2 class="text-2xl font-bold mb-4 text-gray-700">تكاملات أخرى</h2>
    <div class="grid gap-6 md:grid-cols-2">
        @foreach($integrations as $integration)
        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-800">{{ $integration->service_name }}</h3>
                <span class="px-3 py-1 rounded-full text-sm {{ $integration->is_enabled === 'true' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                    {{ $integration->is_enabled === 'true' ? 'مفعّل' : 'معطّل' }}
                </span>
            </div>
            <form action="{{ route('integrations.save') }}" method="POST">
                @csrf
                <input type="hidden" name="service_name" value="{{ $integration->service_name }}">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm">
                    {{ $integration->is_enabled === 'true' ? 'تعطيل' : 'تفعيل' }}
                </button>
            </form>
        </div>
        @endforeach
    </div>
</div>

<script>
async function testOpenAI() {
    const resultDiv = document.getElementById('test-result');
    resultDiv.className = 'mt-4 p-4 rounded-lg bg-blue-50 border border-blue-200';
    resultDiv.innerHTML = '⏳ جاري الاختبار...';
    resultDiv.classList.remove('hidden');
    
    try {
        const response = await fetch('{{ route("integrations.openai.test") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            resultDiv.className = 'mt-4 p-4 rounded-lg bg-green-50 border border-green-200';
            resultDiv.innerHTML = `
                <div class="flex items-center gap-2 text-green-800 font-bold mb-2">
                    ✅ الاتصال ناجح!
                </div>
                <div class="text-sm text-gray-700">
                    <strong>الرد:</strong> ${data.response}
                </div>
            `;
        } else {
            throw new Error(data.error || 'فشل الاختبار');
        }
    } catch (error) {
        resultDiv.className = 'mt-4 p-4 rounded-lg bg-red-50 border border-red-200';
        resultDiv.innerHTML = `
            <div class="flex items-center gap-2 text-red-800 font-bold mb-2">
                ❌ فشل الاتصال
            </div>
            <div class="text-sm text-gray-700">${error.message}</div>
        `;
    }
}
</script>
@endsection
