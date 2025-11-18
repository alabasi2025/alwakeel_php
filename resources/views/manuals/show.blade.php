<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $manual->title }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f7fafc;
            padding: 2rem 0;
        }
        .manual-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            padding: 3rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .manual-header {
            border-bottom: 3px solid #667eea;
            padding-bottom: 1.5rem;
            margin-bottom: 2rem;
        }
        .manual-title {
            font-size: 2.5rem;
            font-weight: bold;
            color: #2d3748;
            margin-bottom: 1rem;
        }
        .manual-meta {
            color: #718096;
            font-size: 1rem;
        }
        .manual-content {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #2d3748;
        }
        .manual-content h1 {
            color: #2d3748;
            border-bottom: 2px solid #667eea;
            padding-bottom: 0.5rem;
            margin-top: 2rem;
            margin-bottom: 1rem;
        }
        .manual-content h2 {
            color: #4a5568;
            margin-top: 1.5rem;
            margin-bottom: 1rem;
        }
        .manual-content h3 {
            color: #718096;
            margin-top: 1rem;
            margin-bottom: 0.5rem;
        }
        .manual-content code {
            background: #f7fafc;
            padding: 0.2rem 0.5rem;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        .manual-content pre {
            background: #2d3748;
            color: #e2e8f0;
            padding: 1rem;
            border-radius: 5px;
            overflow-x: auto;
        }
        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }
        .btn-back {
            background: #667eea;
            color: white;
        }
        .btn-back:hover {
            background: #5568d3;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="action-buttons">
            <a href="{{ route('manuals.index') }}" class="btn btn-back">
                <i class="bi bi-arrow-right"></i> العودة
            </a>
            <button class="btn btn-primary" onclick="window.print()">
                <i class="bi bi-printer"></i> طباعة
            </button>
            <a href="{{ route('manuals.export-pdf', $manual) }}" class="btn btn-danger">
                <i class="bi bi-file-pdf"></i> تصدير PDF
            </a>
            <a href="{{ route('manuals.export-word', $manual) }}" class="btn btn-info">
                <i class="bi bi-file-word"></i> تصدير Word
            </a>
        </div>

        <div class="manual-container">
            <div class="manual-header">
                <div class="manual-title">{{ $manual->title }}</div>
                <div class="manual-meta">
                    @if($manual->version)
                    <i class="bi bi-tag"></i> الإصدار {{ $manual->version }} |
                    @endif
                    <i class="bi bi-calendar"></i> {{ $manual->created_at->format('Y-m-d') }}
                    @if($manual->word_count)
                    | <i class="bi bi-file-text"></i> {{ number_format($manual->word_count) }} كلمة
                    @endif
                    @if($manual->is_published)
                    | <span class="badge bg-success">منشور</span>
                    @else
                    | <span class="badge bg-secondary">مسودة</span>
                    @endif
                </div>
            </div>

            <div class="manual-content">
                {!! $manual->content_html ?? \Illuminate\Support\Str::markdown($manual->content_markdown) !!}
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
