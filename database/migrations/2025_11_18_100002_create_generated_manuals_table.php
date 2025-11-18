<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('generated_manuals', function (Blueprint $table) {
            $table->id();
            $table->string('project'); // 'alabasi', 'wakeel', 'integration'
            $table->string('title'); // عنوان الدليل
            $table->string('version')->nullable(); // رقم الإصدار
            
            // المحتوى
            $table->longText('content_markdown'); // المحتوى بصيغة Markdown
            $table->longText('content_html')->nullable(); // المحتوى بصيغة HTML
            
            // معلومات التوليد
            $table->enum('generation_type', ['manual', 'auto'])->default('auto'); // يدوي أم تلقائي
            $table->json('included_features')->nullable(); // الميزات المضمنة
            $table->json('sections')->nullable(); // الأقسام
            
            // الملفات
            $table->string('pdf_path')->nullable(); // مسار ملف PDF
            $table->string('docx_path')->nullable(); // مسار ملف Word
            
            // الحالة
            $table->boolean('is_published')->default(false); // منشور أم لا
            $table->boolean('is_latest')->default(false); // آخر إصدار
            $table->timestamp('published_at')->nullable(); // تاريخ النشر
            
            // معلومات إضافية
            $table->integer('page_count')->nullable(); // عدد الصفحات
            $table->integer('word_count')->nullable(); // عدد الكلمات
            $table->json('metadata')->nullable(); // بيانات وصفية إضافية
            
            $table->timestamps();
            
            // Indexes
            $table->index('project');
            $table->index('is_published');
            $table->index('is_latest');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generated_manuals');
    }
};
