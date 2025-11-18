<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $item->title }} - تفاصيل الميزة</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }
        .detail-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .detail-header {
            border-bottom: 3px solid #667eea;
            padding-bottom: 1rem;
            margin-bottom: 2rem;
        }
        .detail-title {
            font-size: 2rem;
            font-weight: bold;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
        }
        .status-completed { background: #48bb78; color: white; }
        .status-in_progress { background: #ed8936; color: white; }
        .status-pending { background: #cbd5e0; color: #2d3748; }
        .section-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #667eea;
            margin-top: 2rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .comparison-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-top: 1rem;
        }
        .comparison-box {
            background: #f7fafc;
            border-radius: 10px;
            padding: 1.5rem;
            border: 2px solid #e2e8f0;
        }
        .comparison-box h4 {
            color: #667eea;
            margin-bottom: 1rem;
        }
        .comparison-image {
            width: 100%;
            border-radius: 10px;
            margin-bottom: 1rem;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .demo-gif {
            width: 100%;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .files-list {
            background: #f7fafc;
            border-radius: 10px;
            padding: 1rem;
        }
        .file-item {
            padding: 0.5rem;
            border-bottom: 1px solid #e2e8f0;
        }
        .file-item:last-child {
            border-bottom: none;
        }
        .guide-steps {
            counter-reset: step-counter;
        }
        .guide-step {
            counter-increment: step-counter;
            background: #f7fafc;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-right: 4px solid #667eea;
            position: relative;
        }
        .guide-step::before {
            content: counter(step-counter);
            position: absolute;
            right: -20px;
            top: 50%;
            transform: translateY(-50%);
            background: #667eea;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2rem;
        }
        .back-button {
            background: #667eea;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 2rem;
        }
        .back-button:hover {
            background: #5568d3;
            color: white;
        }
        @media (max-width: 768px) {
            .comparison-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="{{ url('/roadmap') }}" class="back-button">
            <i class="bi bi-arrow-right"></i>
            العودة إلى خارطة الطريق
        </a>

        <div class="detail-card">
            <div class="detail-header">
                <div class="detail-title">{{ $item->title }}</div>
                <div class="mt-2">
                    <span class="status-badge status-{{ $item->status }}">
                        @if($item->status === 'completed') ✅ مكتمل
                        @elseif($item->status === 'in_progress') 🔄 قيد التنفيذ
                        @else ⏳ قيد الانتظار
                        @endif
                    </span>
                    <span class="badge bg-primary ms-2">{{ $item->progress }}%</span>
                </div>
            </div>

            @if($item->description)
            <div class="mb-4">
                <p class="lead">{{ $item->description }}</p>
            </div>
            @endif

            @if($item->featureDetail)
                @php $detail = $item->featureDetail; @endphp

                {{-- الوصف التفصيلي --}}
                @if($detail->detailed_description)
                <div class="section-title">
                    <i class="bi bi-info-circle"></i>
                    الوصف التفصيلي
                </div>
                <div class="mb-4">
                    {!! nl2br(e($detail->detailed_description)) !!}
                </div>
                @endif

                {{-- الفوائد --}}
                @if($detail->benefits)
                <div class="section-title">
                    <i class="bi bi-star"></i>
                    فوائد الميزة
                </div>
                <div class="mb-4">
                    {!! nl2br(e($detail->benefits)) !!}
                </div>
                @endif

                {{-- حالات الاستخدام --}}
                @if($detail->use_cases)
                <div class="section-title">
                    <i class="bi bi-lightbulb"></i>
                    حالات الاستخدام
                </div>
                <div class="mb-4">
                    {!! nl2br(e($detail->use_cases)) !!}
                </div>
                @endif

                {{-- ما تم بناؤه --}}
                @if($detail->what_built)
                <div class="section-title">
                    <i class="bi bi-check-circle"></i>
                    ما تم بناؤه
                </div>
                <div class="mb-4">
                    {!! nl2br(e($detail->what_built)) !!}
                </div>
                @endif

                {{-- الملفات المضافة --}}
                @if($detail->files_added && count($detail->files_added) > 0)
                <div class="section-title">
                    <i class="bi bi-file-earmark-code"></i>
                    الملفات المضافة
                </div>
                <div class="files-list mb-4">
                    @foreach($detail->files_added as $file)
                    <div class="file-item">
                        <i class="bi bi-file-code"></i>
                        <code>{{ $file }}</code>
                    </div>
                    @endforeach
                </div>
                @endif

                {{-- التفاصيل التقنية --}}
                @if($detail->technical_details && count($detail->technical_details) > 0)
                <div class="section-title">
                    <i class="bi bi-gear"></i>
                    التفاصيل التقنية
                </div>
                <div class="mb-4">
                    <ul>
                        @foreach($detail->technical_details as $tech)
                        <li>{{ $tech }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- قبل وبعد --}}
                @if($detail->before_description || $detail->after_description)
                <div class="section-title">
                    <i class="bi bi-arrow-left-right"></i>
                    قبل وبعد
                </div>
                <div class="comparison-container">
                    @if($detail->before_description)
                    <div class="comparison-box">
                        <h4>❌ قبل</h4>
                        @if($detail->before_image)
                        <img src="{{ asset('storage/' . $detail->before_image) }}" alt="قبل" class="comparison-image">
                        @endif
                        <p>{{ $detail->before_description }}</p>
                    </div>
                    @endif

                    @if($detail->after_description)
                    <div class="comparison-box">
                        <h4>✅ بعد</h4>
                        @if($detail->after_image)
                        <img src="{{ asset('storage/' . $detail->after_image) }}" alt="بعد" class="comparison-image">
                        @endif
                        <p>{{ $detail->after_description }}</p>
                    </div>
                    @endif
                </div>
                @endif

                {{-- GIF توضيحي --}}
                @if($detail->demo_gif)
                <div class="section-title">
                    <i class="bi bi-film"></i>
                    عرض توضيحي
                </div>
                <div class="mb-4">
                    <img src="{{ asset('storage/' . $detail->demo_gif) }}" alt="عرض توضيحي" class="demo-gif">
                </div>
                @endif

                {{-- دليل الاستخدام --}}
                @if($detail->user_guide)
                <div class="section-title">
                    <i class="bi bi-book"></i>
                    دليل الاستخدام
                </div>
                <div class="mb-4">
                    {!! nl2br(e($detail->user_guide)) !!}
                </div>
                @endif

                {{-- خطوات الدليل --}}
                @if($detail->guide_steps && count($detail->guide_steps) > 0)
                <div class="guide-steps mb-4">
                    @foreach($detail->guide_steps as $step)
                    <div class="guide-step">
                        <h5>{{ $step['title'] ?? 'خطوة' }}</h5>
                        <p>{{ $step['description'] ?? '' }}</p>
                    </div>
                    @endforeach
                </div>
                @endif

                {{-- ما المتبقي --}}
                @if($detail->what_remaining)
                <div class="section-title">
                    <i class="bi bi-hourglass-split"></i>
                    ما المتبقي
                </div>
                <div class="mb-4">
                    {!! nl2br(e($detail->what_remaining)) !!}
                </div>
                @endif

            @else
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    لا توجد تفاصيل متاحة لهذه الميزة حالياً.
                </div>
            @endif

            {{-- سجل التحديثات --}}
            @if($item->updateLogs && $item->updateLogs->count() > 0)
            <div class="section-title">
                <i class="bi bi-clock-history"></i>
                سجل التحديثات
            </div>
            <div class="mb-4">
                @foreach($item->updateLogs as $log)
                <div class="alert alert-secondary">
                    <strong>{{ $log->title }}</strong>
                    <small class="text-muted d-block">{{ $log->committed_at?->format('Y-m-d H:i') }}</small>
                    <p class="mb-0 mt-2">{{ $log->description }}</p>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
