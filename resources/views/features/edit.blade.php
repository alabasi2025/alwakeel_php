<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل تفاصيل: {{ $item->title }}</title>
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
        .section-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #667eea;
            margin-top: 2rem;
            margin-bottom: 1rem;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 0.5rem;
        }
        .upload-area {
            border: 2px dashed #cbd5e0;
            border-radius: 10px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        .upload-area:hover {
            border-color: #667eea;
            background: #f7fafc;
        }
        .upload-area.dragover {
            border-color: #667eea;
            background: #edf2f7;
        }
        .preview-image {
            max-width: 100%;
            border-radius: 10px;
            margin-top: 1rem;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .comparison-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }
        @media (max-width: 768px) {
            .comparison-grid {
                grid-template-columns: 1fr;
            }
        }
        .btn-save {
            background: linear-gradient(135deg, #48bb78 0%, #38b2ac 100%);
            color: white;
            border: none;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: bold;
        }
        .btn-save:hover {
            opacity: 0.9;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="{{ route('roadmap.show', $item) }}" class="btn btn-light mb-3">
            <i class="bi bi-arrow-right"></i> العودة
        </a>

        <div class="main-card">
            <h2 class="mb-3">
                <i class="bi bi-pencil"></i>
                تعديل تفاصيل: {{ $item->title }}
            </h2>

            <form id="detailsForm">
                @csrf

                <!-- الوصف التفصيلي -->
                <div class="mb-3">
                    <label class="form-label">الوصف التفصيلي</label>
                    <textarea class="form-control" name="detailed_description" rows="5">{{ $item->featureDetail->detailed_description ?? '' }}</textarea>
                </div>

                <!-- الفوائد -->
                <div class="mb-3">
                    <label class="form-label">فوائد الميزة</label>
                    <textarea class="form-control" name="benefits" rows="4">{{ $item->featureDetail->benefits ?? '' }}</textarea>
                </div>

                <!-- حالات الاستخدام -->
                <div class="mb-3">
                    <label class="form-label">حالات الاستخدام</label>
                    <textarea class="form-control" name="use_cases" rows="4">{{ $item->featureDetail->use_cases ?? '' }}</textarea>
                </div>

                <!-- ما تم بناؤه -->
                <div class="mb-3">
                    <label class="form-label">ما تم بناؤه</label>
                    <textarea class="form-control" name="what_built" rows="4">{{ $item->featureDetail->what_built ?? '' }}</textarea>
                </div>

                <!-- ما المتبقي -->
                <div class="mb-3">
                    <label class="form-label">ما المتبقي</label>
                    <textarea class="form-control" name="what_remaining" rows="3">{{ $item->featureDetail->what_remaining ?? '' }}</textarea>
                </div>

                <!-- دليل الاستخدام -->
                <div class="section-title">
                    <i class="bi bi-book"></i>
                    دليل الاستخدام
                </div>
                <div class="mb-3">
                    <textarea class="form-control" name="user_guide" rows="8">{{ $item->featureDetail->user_guide ?? '' }}</textarea>
                </div>

                <button type="submit" class="btn btn-save">
                    <i class="bi bi-save"></i>
                    حفظ التفاصيل
                </button>
            </form>

            <!-- رفع الصور -->
            <div class="section-title mt-5">
                <i class="bi bi-images"></i>
                الصور والوسائط
            </div>

            <div class="comparison-grid">
                <!-- صورة قبل -->
                <div>
                    <h5 class="mb-3">❌ صورة قبل</h5>
                    <div class="upload-area" id="beforeUploadArea">
                        <input type="file" id="beforeImageInput" accept="image/*" style="display: none;">
                        <i class="bi bi-cloud-upload" style="font-size: 3rem; color: #cbd5e0;"></i>
                        <p class="mb-0 mt-2">اسحب الصورة هنا أو انقر للاختيار</p>
                    </div>
                    @if($item->featureDetail && $item->featureDetail->before_image)
                    <img src="{{ asset('storage/' . $item->featureDetail->before_image) }}" class="preview-image" id="beforePreview">
                    @else
                    <img src="" class="preview-image" id="beforePreview" style="display: none;">
                    @endif
                </div>

                <!-- صورة بعد -->
                <div>
                    <h5 class="mb-3">✅ صورة بعد</h5>
                    <div class="upload-area" id="afterUploadArea">
                        <input type="file" id="afterImageInput" accept="image/*" style="display: none;">
                        <i class="bi bi-cloud-upload" style="font-size: 3rem; color: #cbd5e0;"></i>
                        <p class="mb-0 mt-2">اسحب الصورة هنا أو انقر للاختيار</p>
                    </div>
                    @if($item->featureDetail && $item->featureDetail->after_image)
                    <img src="{{ asset('storage/' . $item->featureDetail->after_image) }}" class="preview-image" id="afterPreview">
                    @else
                    <img src="" class="preview-image" id="afterPreview" style="display: none;">
                    @endif
                </div>
            </div>

            <!-- رفع GIF -->
            <div class="mt-4">
                <h5 class="mb-3">🎬 عرض توضيحي (GIF)</h5>
                <div class="upload-area" id="gifUploadArea">
                    <input type="file" id="gifInput" accept="image/gif" style="display: none;">
                    <i class="bi bi-film" style="font-size: 3rem; color: #cbd5e0;"></i>
                    <p class="mb-0 mt-2">اسحب الـ GIF هنا أو انقر للاختيار</p>
                </div>
                @if($item->featureDetail && $item->featureDetail->demo_gif)
                <img src="{{ asset('storage/' . $item->featureDetail->demo_gif) }}" class="preview-image" id="gifPreview">
                @else
                <img src="" class="preview-image" id="gifPreview" style="display: none;">
                @endif
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const itemId = {{ $item->id }};

        // حفظ التفاصيل
        document.getElementById('detailsForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const data = Object.fromEntries(formData);

            try {
                const response = await fetch(`/features/${itemId}/update`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    alert('✅ تم حفظ التفاصيل بنجاح!');
                } else {
                    alert('❌ فشل في حفظ التفاصيل');
                }
            } catch (error) {
                alert('❌ حدث خطأ: ' + error.message);
            }
        });

        // رفع صورة قبل
        setupUpload('beforeUploadArea', 'beforeImageInput', 'beforePreview', 'upload-before');

        // رفع صورة بعد
        setupUpload('afterUploadArea', 'afterImageInput', 'afterPreview', 'upload-after');

        // رفع GIF
        setupUpload('gifUploadArea', 'gifInput', 'gifPreview', 'upload-demo');

        function setupUpload(areaId, inputId, previewId, endpoint) {
            const area = document.getElementById(areaId);
            const input = document.getElementById(inputId);
            const preview = document.getElementById(previewId);

            // Click to upload
            area.addEventListener('click', () => input.click());

            // Drag and drop
            area.addEventListener('dragover', (e) => {
                e.preventDefault();
                area.classList.add('dragover');
            });

            area.addEventListener('dragleave', () => {
                area.classList.remove('dragover');
            });

            area.addEventListener('drop', (e) => {
                e.preventDefault();
                area.classList.remove('dragover');
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    handleUpload(files[0], endpoint, preview);
                }
            });

            // File input change
            input.addEventListener('change', (e) => {
                if (e.target.files.length > 0) {
                    handleUpload(e.target.files[0], endpoint, preview);
                }
            });
        }

        async function handleUpload(file, endpoint, preview) {
            const formData = new FormData();
            formData.append(endpoint === 'upload-demo' ? 'gif' : 'image', file);

            try {
                const response = await fetch(`/features/${itemId}/${endpoint}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    alert('✅ تم رفع الملف بنجاح!');
                    preview.src = result.url;
                    preview.style.display = 'block';
                } else {
                    alert('❌ فشل في رفع الملف: ' + result.message);
                }
            } catch (error) {
                alert('❌ حدث خطأ: ' + error.message);
            }
        }
    </script>
</body>
</html>
