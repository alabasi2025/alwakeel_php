<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>توليد الأدلة التلقائي - الوكيل</title>
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
        .project-selector {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .project-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 10px;
            cursor: pointer;
            transition: transform 0.3s;
            text-align: center;
        }
        .project-card:hover {
            transform: translateY(-5px);
        }
        .project-card.selected {
            box-shadow: 0 0 0 3px #ffd700;
        }
        .project-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        .manual-item {
            background: #f7fafc;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-right: 4px solid #667eea;
        }
        .manual-item h4 {
            color: #2d3748;
            margin-bottom: 0.5rem;
        }
        .manual-meta {
            color: #718096;
            font-size: 0.9rem;
        }
        .badge-published {
            background: #48bb78;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
        }
        .badge-draft {
            background: #cbd5e0;
            color: #2d3748;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
        }
        .btn-generate {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: bold;
        }
        .btn-generate:hover {
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
                <div class="page-title">
                    <i class="bi bi-book"></i>
                    توليد الأدلة التلقائي
                </div>
                <p class="text-muted mb-0">قم بتوليد دليل استخدام شامل تلقائياً من الميزات المكتملة</p>
            </div>

            <h5 class="mb-3">اختر المشروع:</h5>
            <div class="project-selector">
                <div class="project-card" data-project="alabasi">
                    <div class="project-icon">💼</div>
                    <h4>نظام الأباسي المحاسبي</h4>
                    <p class="mb-0">دليل استخدام النظام المحاسبي</p>
                </div>
                <div class="project-card" data-project="wakeel">
                    <div class="project-icon">🤖</div>
                    <h4>نظام الوكيل الذكي</h4>
                    <p class="mb-0">دليل استخدام الوكيل</p>
                </div>
                <div class="project-card" data-project="integration">
                    <div class="project-icon">🔗</div>
                    <h4>التكامل بين النظامين</h4>
                    <p class="mb-0">دليل التكامل والربط</p>
                </div>
            </div>

            <div class="text-center mt-4">
                <button class="btn btn-generate" id="generateBtn" disabled>
                    <i class="bi bi-magic"></i>
                    توليد الدليل التلقائي
                </button>
            </div>
        </div>

        @if($manuals->count() > 0)
        <div class="main-card">
            <h4 class="mb-3">
                <i class="bi bi-clock-history"></i>
                الأدلة المولدة سابقاً
            </h4>

            @foreach($manuals as $manual)
            <div class="manual-item">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h4>{{ $manual->title }}</h4>
                        <div class="manual-meta">
                            <i class="bi bi-calendar"></i> {{ $manual->created_at->format('Y-m-d H:i') }}
                            @if($manual->version)
                            | <i class="bi bi-tag"></i> الإصدار {{ $manual->version }}
                            @endif
                            @if($manual->word_count)
                            | <i class="bi bi-file-text"></i> {{ number_format($manual->word_count) }} كلمة
                            @endif
                        </div>
                    </div>
                    <div>
                        @if($manual->is_published)
                        <span class="badge-published">✅ منشور</span>
                        @else
                        <span class="badge-draft">📝 مسودة</span>
                        @endif
                    </div>
                </div>
                <div class="mt-3">
                    <a href="{{ route('manuals.show', $manual) }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-eye"></i> عرض
                    </a>
                    @if(!$manual->is_published)
                    <button class="btn btn-sm btn-success publish-btn" data-id="{{ $manual->id }}">
                        <i class="bi bi-check-circle"></i> نشر
                    </button>
                    @endif
                    @if($manual->project === 'alabasi')
                    <button class="btn btn-sm btn-info send-btn" data-id="{{ $manual->id }}">
                        <i class="bi bi-send"></i> إرسال للنظام المحاسبي
                    </button>
                    @endif
                    <button class="btn btn-sm btn-danger delete-btn" data-id="{{ $manual->id }}">
                        <i class="bi bi-trash"></i> حذف
                    </button>
                </div>
            </div>
            @endforeach

            <div class="mt-3">
                {{ $manuals->links() }}
            </div>
        </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let selectedProject = null;

        // اختيار المشروع
        document.querySelectorAll('.project-card').forEach(card => {
            card.addEventListener('click', function() {
                document.querySelectorAll('.project-card').forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');
                selectedProject = this.dataset.project;
                document.getElementById('generateBtn').disabled = false;
            });
        });

        // توليد الدليل
        document.getElementById('generateBtn').addEventListener('click', async function() {
            if (!selectedProject) return;

            this.disabled = true;
            this.innerHTML = '<i class="bi bi-hourglass-split"></i> جاري التوليد...';

            try {
                const response = await fetch('{{ route("manuals.generate") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        project: selectedProject
                    })
                });

                const data = await response.json();

                if (data.success) {
                    alert('✅ تم توليد الدليل بنجاح!');
                    location.reload();
                } else {
                    alert('❌ فشل في توليد الدليل');
                }
            } catch (error) {
                alert('❌ حدث خطأ: ' + error.message);
            }

            this.disabled = false;
            this.innerHTML = '<i class="bi bi-magic"></i> توليد الدليل التلقائي';
        });

        // نشر دليل
        document.querySelectorAll('.publish-btn').forEach(btn => {
            btn.addEventListener('click', async function() {
                const id = this.dataset.id;
                
                try {
                    const response = await fetch(`/manuals/${id}/publish`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        alert('✅ تم نشر الدليل بنجاح!');
                        location.reload();
                    }
                } catch (error) {
                    alert('❌ حدث خطأ: ' + error.message);
                }
            });
        });

        // إرسال للنظام المحاسبي
        document.querySelectorAll('.send-btn').forEach(btn => {
            btn.addEventListener('click', async function() {
                const id = this.dataset.id;
                
                if (!confirm('هل تريد إرسال هذا الدليل إلى النظام المحاسبي؟')) return;

                try {
                    const response = await fetch(`/manuals/${id}/send-to-alabasi`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        alert('✅ تم إرسال الدليل بنجاح!');
                    }
                } catch (error) {
                    alert('❌ حدث خطأ: ' + error.message);
                }
            });
        });

        // حذف دليل
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', async function() {
                const id = this.dataset.id;
                
                if (!confirm('هل أنت متأكد من حذف هذا الدليل؟')) return;

                try {
                    const response = await fetch(`/manuals/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        alert('✅ تم حذف الدليل بنجاح!');
                        location.reload();
                    }
                } catch (error) {
                    alert('❌ حدث خطأ: ' + error.message);
                }
            });
        });
    </script>
</body>
</html>
