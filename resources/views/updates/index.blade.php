<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سجل التحديثات - الوكيل</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }
        .main-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .page-header {
            border-bottom: 3px solid #667eea;
            padding-bottom: 1rem;
            margin-bottom: 2rem;
        }
        .page-title {
            font-size: 2rem;
            font-weight: bold;
            color: #2d3748;
        }
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 10px;
            text-align: center;
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
        }
        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        .filters {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }
        .timeline {
            position: relative;
            padding-right: 2rem;
        }
        .timeline::before {
            content: '';
            position: absolute;
            right: 0;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e2e8f0;
        }
        .timeline-item {
            position: relative;
            padding-right: 2rem;
            padding-bottom: 2rem;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            right: -8px;
            top: 0;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: white;
            border: 3px solid #667eea;
        }
        .update-card {
            background: #f7fafc;
            border-radius: 10px;
            padding: 1.5rem;
            border-right: 4px solid #667eea;
        }
        .update-card.feature { border-right-color: #667eea; }
        .update-card.enhancement { border-right-color: #48bb78; }
        .update-card.bugfix { border-right-color: #ed8936; }
        .update-card.security { border-right-color: #e53e3e; }
        .update-card.performance { border-right-color: #38b2ac; }
        .update-header {
            display: flex;
            justify-content-between;
            align-items-start;
            margin-bottom: 1rem;
        }
        .update-title {
            font-size: 1.3rem;
            font-weight: bold;
            color: #2d3748;
        }
        .update-meta {
            color: #718096;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        .type-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: bold;
        }
        .type-feature { background: #667eea; color: white; }
        .type-enhancement { background: #48bb78; color: white; }
        .type-bugfix { background: #ed8936; color: white; }
        .type-security { background: #e53e3e; color: white; }
        .type-performance { background: #38b2ac; color: white; }
        .btn-sync {
            background: linear-gradient(135deg, #48bb78 0%, #38b2ac 100%);
            color: white;
            border: none;
        }
        .btn-sync:hover {
            opacity: 0.9;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="{{ url('/') }}" class="btn btn-light mb-3">
            <i class="bi bi-arrow-right"></i> العودة للرئيسية
        </a>

        <div class="main-card">
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="page-title">
                            <i class="bi bi-clock-history"></i>
                            سجل التحديثات
                        </div>
                        <p class="text-muted mb-0">تتبع جميع التحديثات والتطويرات في المشاريع</p>
                    </div>
                    <button class="btn btn-sync" id="syncBtn">
                        <i class="bi bi-arrow-repeat"></i>
                        مزامنة مع Git
                    </button>
                </div>
            </div>

            <!-- الإحصائيات -->
            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-number">{{ $stats['total'] }}</div>
                    <div class="stat-label">إجمالي التحديثات</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">{{ $stats['features'] }}</div>
                    <div class="stat-label">✨ ميزات جديدة</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">{{ $stats['enhancements'] }}</div>
                    <div class="stat-label">⚡ تحسينات</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">{{ $stats['bugfixes'] }}</div>
                    <div class="stat-label">🐛 إصلاحات</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">{{ $stats['recent'] }}</div>
                    <div class="stat-label">📅 آخر 7 أيام</div>
                </div>
            </div>

            <!-- الفلاتر -->
            <div class="filters">
                <select class="form-select" id="projectFilter" style="width: auto;">
                    <option value="all" {{ $project === 'all' ? 'selected' : '' }}>جميع المشاريع</option>
                    <option value="alabasi" {{ $project === 'alabasi' ? 'selected' : '' }}>النظام المحاسبي</option>
                    <option value="wakeel" {{ $project === 'wakeel' ? 'selected' : '' }}>نظام الوكيل</option>
                    <option value="integration" {{ $project === 'integration' ? 'selected' : '' }}>التكامل</option>
                </select>

                <select class="form-select" id="typeFilter" style="width: auto;">
                    <option value="" {{ !$type ? 'selected' : '' }}>جميع الأنواع</option>
                    <option value="feature" {{ $type === 'feature' ? 'selected' : '' }}>✨ ميزات</option>
                    <option value="enhancement" {{ $type === 'enhancement' ? 'selected' : '' }}>⚡ تحسينات</option>
                    <option value="bugfix" {{ $type === 'bugfix' ? 'selected' : '' }}>🐛 إصلاحات</option>
                    <option value="security" {{ $type === 'security' ? 'selected' : '' }}>🔒 أمان</option>
                    <option value="performance" {{ $type === 'performance' ? 'selected' : '' }}>🚀 أداء</option>
                </select>

                <a href="{{ route('updates.export', ['project' => $project]) }}" class="btn btn-outline-primary">
                    <i class="bi bi-download"></i> تصدير
                </a>
            </div>

            <!-- Timeline التحديثات -->
            <div class="timeline">
                @forelse($updates as $update)
                <div class="timeline-item">
                    <div class="update-card {{ $update->type }}">
                        <div class="update-header">
                            <div>
                                <div class="update-title">
                                    {{ $update->getTypeIcon() }} {{ $update->title }}
                                </div>
                                <div class="update-meta">
                                    <i class="bi bi-calendar"></i> {{ $update->committed_at?->format('Y-m-d H:i') ?? $update->created_at->format('Y-m-d H:i') }}
                                    @if($update->author)
                                    | <i class="bi bi-person"></i> {{ $update->author }}
                                    @endif
                                    @if($update->roadmapItem)
                                    | <i class="bi bi-link"></i> <a href="{{ route('roadmap.show', $update->roadmapItem) }}">{{ $update->roadmapItem->title }}</a>
                                    @endif
                                </div>
                            </div>
                            <span class="type-badge type-{{ $update->type }}">
                                {{ $update->getTypeNameAr() }}
                            </span>
                        </div>

                        <p class="mb-2">{{ $update->description }}</p>

                        @if($update->what_added || $update->what_changed || $update->what_fixed)
                        <div class="mt-3">
                            @if($update->what_added)
                            <div class="mb-2">
                                <strong>➕ ما تم إضافته:</strong>
                                <p class="mb-0">{{ $update->what_added }}</p>
                            </div>
                            @endif

                            @if($update->what_changed)
                            <div class="mb-2">
                                <strong>🔄 ما تم تغييره:</strong>
                                <p class="mb-0">{{ $update->what_changed }}</p>
                            </div>
                            @endif

                            @if($update->what_fixed)
                            <div class="mb-2">
                                <strong>🔧 ما تم إصلاحه:</strong>
                                <p class="mb-0">{{ $update->what_fixed }}</p>
                            </div>
                            @endif
                        </div>
                        @endif

                        @if($update->commit_hash)
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="bi bi-git"></i> Commit: <code>{{ substr($update->commit_hash, 0, 7) }}</code>
                            </small>
                        </div>
                        @endif
                    </div>
                </div>
                @empty
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    لا توجد تحديثات متاحة حالياً.
                </div>
                @endforelse
            </div>

            @if($updates->hasPages())
            <div class="mt-4">
                {{ $updates->links() }}
            </div>
            @endif
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // تطبيق الفلاتر
        document.getElementById('projectFilter').addEventListener('change', function() {
            updateFilters();
        });

        document.getElementById('typeFilter').addEventListener('change', function() {
            updateFilters();
        });

        function updateFilters() {
            const project = document.getElementById('projectFilter').value;
            const type = document.getElementById('typeFilter').value;
            
            let url = '{{ route("updates.index") }}?project=' + project;
            if (type) url += '&type=' + type;
            
            window.location.href = url;
        }

        // مزامنة مع Git
        document.getElementById('syncBtn').addEventListener('click', async function() {
            const project = document.getElementById('projectFilter').value === 'all' 
                ? 'wakeel' 
                : document.getElementById('projectFilter').value;

            this.disabled = true;
            this.innerHTML = '<i class="bi bi-hourglass-split"></i> جاري المزامنة...';

            try {
                const response = await fetch('{{ route("updates.sync-git") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        project: project,
                        limit: 20
                    })
                });

                const data = await response.json();

                if (data.success) {
                    alert(`✅ تمت المزامنة بنجاح! تم إضافة ${data.synced_count} تحديث جديد.`);
                    location.reload();
                } else {
                    alert('❌ فشلت المزامنة: ' + data.message);
                }
            } catch (error) {
                alert('❌ حدث خطأ: ' + error.message);
            }

            this.disabled = false;
            this.innerHTML = '<i class="bi bi-arrow-repeat"></i> مزامنة مع Git';
        });
    </script>
</body>
</html>
