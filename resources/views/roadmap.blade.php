<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>خارطة الطريق - الوكيل</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }
        .container-main {
            padding: 2rem 0;
        }
        .project-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .project-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f0f0f0;
        }
        .project-title {
            font-size: 1.8rem;
            font-weight: bold;
            color: #2d3748;
        }
        .project-icon {
            font-size: 2.5rem;
        }
        .stats-row {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        .stat-card {
            flex: 1;
            min-width: 150px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem;
            border-radius: 10px;
            text-align: center;
        }
        .stat-card.success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }
        .stat-card.warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        .stat-card.info {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
        }
        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        .phase-section {
            margin-bottom: 2rem;
        }
        .phase-title {
            font-size: 1.3rem;
            font-weight: bold;
            color: #4a5568;
            margin-bottom: 1rem;
            padding: 0.5rem 1rem;
            background: #f7fafc;
            border-radius: 8px;
            border-right: 4px solid #667eea;
        }
        .roadmap-item {
            background: #f9fafb;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 0.8rem;
            border-right: 4px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        .roadmap-item:hover {
            transform: translateX(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .roadmap-item.completed {
            border-right-color: #48bb78;
            background: #f0fff4;
        }
        .roadmap-item.in-progress {
            border-right-color: #ed8936;
            background: #fffaf0;
        }
        .roadmap-item.pending {
            border-right-color: #cbd5e0;
        }
        .item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        .item-title {
            font-weight: 600;
            color: #2d3748;
            font-size: 1.1rem;
        }
        .item-status {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .status-completed {
            background: #c6f6d5;
            color: #22543d;
        }
        .status-in-progress {
            background: #fed7d7;
            color: #742a2a;
        }
        .status-pending {
            background: #e2e8f0;
            color: #4a5568;
        }
        .progress-bar-container {
            background: #e2e8f0;
            height: 8px;
            border-radius: 10px;
            overflow: hidden;
            margin-top: 0.5rem;
        }
        .progress-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            transition: width 0.3s ease;
        }
        .progress-bar-fill.completed {
            background: linear-gradient(90deg, #48bb78 0%, #38a169 100%);
        }
        .progress-bar-fill.in-progress {
            background: linear-gradient(90deg, #ed8936 0%, #dd6b20 100%);
        }
        .item-description {
            color: #718096;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
        .item-meta {
            display: flex;
            gap: 1rem;
            margin-top: 0.5rem;
            font-size: 0.85rem;
            color: #a0aec0;
        }
        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="/">
                <i class="bi bi-robot"></i> الوكيل
            </a>
            <div class="navbar-nav me-auto">
                <a class="nav-link" href="/">الرئيسية</a>
                <a class="nav-link" href="/chat">الدردشة</a>
                <a class="nav-link" href="/terminal">الطرفية</a>
                <a class="nav-link active" href="/roadmap">خارطة الطريق</a>
            </div>
        </div>
    </nav>

    <div class="container container-main">
        <h1 class="text-white text-center mb-4">
            <i class="bi bi-map"></i> خارطة الطريق الشاملة
        </h1>

        <!-- نظام الأباسي المحاسبي -->
        <div class="project-card">
            <div class="project-header">
                <div>
                    <div class="project-title">
                        <i class="bi bi-calculator project-icon text-primary"></i>
                        نظام الأباسي المحاسبي
                    </div>
                    <small class="text-muted">Laravel 10 + PHP 8.2 | https://alabasi.es</small>
                </div>
            </div>

            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-value">{{ $stats['alabasi']['total'] }}</div>
                    <div class="stat-label">إجمالي المهام</div>
                </div>
                <div class="stat-card success">
                    <div class="stat-value">{{ $stats['alabasi']['completed'] }}</div>
                    <div class="stat-label">مكتمل</div>
                </div>
                <div class="stat-card warning">
                    <div class="stat-value">{{ $stats['alabasi']['in_progress'] }}</div>
                    <div class="stat-label">قيد التنفيذ</div>
                </div>
                <div class="stat-card info">
                    <div class="stat-value">{{ $stats['alabasi']['completion_rate'] }}%</div>
                    <div class="stat-label">نسبة الإنجاز</div>
                </div>
            </div>

            @foreach($alabasiItems->groupBy('phase') as $phase => $items)
            <div class="phase-section">
                <div class="phase-title">{{ $phase }}</div>
                @foreach($items as $item)
                <div class="roadmap-item {{ $item->status }}">
                    <div class="item-header">
                        <div class="item-title">{{ $item->title }}</div>
                        <span class="item-status status-{{ $item->status }}">
                            @if($item->status === 'completed') ✓ مكتمل
                            @elseif($item->status === 'in_progress') ⏳ قيد التنفيذ
                            @else ⏸️ قيد الانتظار
                            @endif
                        </span>
                    </div>
                    @if($item->description)
                    <div class="item-description">{{ $item->description }}</div>
                    @endif
                    <div class="progress-bar-container">
                        <div class="progress-bar-fill {{ $item->status }}" style="width: {{ $item->progress }}%"></div>
                    </div>
                    <div class="item-meta">
                        <div class="meta-item">
                            <i class="bi bi-percent"></i> {{ $item->progress }}%
                        </div>
                        @if($item->estimated_days)
                        <div class="meta-item">
                            <i class="bi bi-calendar-event"></i> {{ $item->estimated_days }} يوم
                        </div>
                        @endif
                        @if($item->start_date)
                        <div class="meta-item">
                            <i class="bi bi-clock-history"></i> {{ $item->start_date->format('Y-m-d') }}
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @endforeach
        </div>

        <!-- الربط والتكامل -->
        <div class="project-card">
            <div class="project-header">
                <div>
                    <div class="project-title">
                        <i class="bi bi-link-45deg project-icon text-success"></i>
                        الربط والتكامل
                    </div>
                    <small class="text-muted">API Integration | الوكيل ↔ الأباسي</small>
                </div>
            </div>

            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-value">{{ $stats['integration']['total'] }}</div>
                    <div class="stat-label">إجمالي المهام</div>
                </div>
                <div class="stat-card success">
                    <div class="stat-value">{{ $stats['integration']['completed'] }}</div>
                    <div class="stat-label">مكتمل</div>
                </div>
                <div class="stat-card warning">
                    <div class="stat-value">{{ $stats['integration']['in_progress'] }}</div>
                    <div class="stat-label">قيد التنفيذ</div>
                </div>
                <div class="stat-card info">
                    <div class="stat-value">{{ $stats['integration']['completion_rate'] }}%</div>
                    <div class="stat-label">نسبة الإنجاز</div>
                </div>
            </div>

            @foreach($integrationItems->groupBy('phase') as $phase => $items)
            <div class="phase-section">
                <div class="phase-title">{{ $phase }}</div>
                @foreach($items as $item)
                <div class="roadmap-item {{ $item->status }}">
                    <div class="item-header">
                        <div class="item-title">{{ $item->title }}</div>
                        <span class="item-status status-{{ $item->status }}">
                            @if($item->status === 'completed') ✓ مكتمل
                            @elseif($item->status === 'in_progress') ⏳ قيد التنفيذ
                            @else ⏸️ قيد الانتظار
                            @endif
                        </span>
                    </div>
                    @if($item->description)
                    <div class="item-description">{{ $item->description }}</div>
                    @endif
                    <div class="progress-bar-container">
                        <div class="progress-bar-fill {{ $item->status }}" style="width: {{ $item->progress }}%"></div>
                    </div>
                    <div class="item-meta">
                        <div class="meta-item">
                            <i class="bi bi-percent"></i> {{ $item->progress }}%
                        </div>
                        @if($item->estimated_days)
                        <div class="meta-item">
                            <i class="bi bi-calendar-event"></i> {{ $item->estimated_days }} يوم
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @endforeach
        </div>

        <!-- نظام الوكيل -->
        <div class="project-card">
            <div class="project-header">
                <div>
                    <div class="project-title">
                        <i class="bi bi-robot project-icon text-danger"></i>
                        نظام الوكيل الذكي
                    </div>
                    <small class="text-muted">AI-Powered Assistant | التعلم الذاتي</small>
                </div>
            </div>

            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-value">{{ $stats['wakeel']['total'] }}</div>
                    <div class="stat-label">إجمالي المهام</div>
                </div>
                <div class="stat-card success">
                    <div class="stat-value">{{ $stats['wakeel']['completed'] }}</div>
                    <div class="stat-label">مكتمل</div>
                </div>
                <div class="stat-card warning">
                    <div class="stat-value">{{ $stats['wakeel']['in_progress'] }}</div>
                    <div class="stat-label">قيد التنفيذ</div>
                </div>
                <div class="stat-card info">
                    <div class="stat-value">{{ $stats['wakeel']['completion_rate'] }}%</div>
                    <div class="stat-label">نسبة الإنجاز</div>
                </div>
            </div>

            @foreach($wakeelItems->groupBy('phase') as $phase => $items)
            <div class="phase-section">
                <div class="phase-title">{{ $phase }}</div>
                @foreach($items as $item)
                <div class="roadmap-item {{ $item->status }}">
                    <div class="item-header">
                        <div class="item-title">{{ $item->title }}</div>
                        <span class="item-status status-{{ $item->status }}">
                            @if($item->status === 'completed') ✓ مكتمل
                            @elseif($item->status === 'in_progress') ⏳ قيد التنفيذ
                            @else ⏸️ قيد الانتظار
                            @endif
                        </span>
                    </div>
                    @if($item->description)
                    <div class="item-description">{{ $item->description }}</div>
                    @endif
                    <div class="progress-bar-container">
                        <div class="progress-bar-fill {{ $item->status }}" style="width: {{ $item->progress }}%"></div>
                    </div>
                    <div class="item-meta">
                        <div class="meta-item">
                            <i class="bi bi-percent"></i> {{ $item->progress }}%
                        </div>
                        @if($item->estimated_days)
                        <div class="meta-item">
                            <i class="bi bi-calendar-event"></i> {{ $item->estimated_days }} يوم
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @endforeach
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
