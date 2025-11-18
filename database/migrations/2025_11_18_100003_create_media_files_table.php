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
        Schema::create('media_files', function (Blueprint $table) {
            $table->id();
            $table->string('project'); // 'alabasi', 'wakeel', 'integration'
            $table->morphs('mediable'); // علاقة polymorphic (feature_details, update_logs, etc.)
            
            // معلومات الملف
            $table->string('title')->nullable(); // عنوان الملف
            $table->text('description')->nullable(); // وصف الملف
            $table->enum('type', ['image', 'gif', 'video', 'document', 'other'])->default('image');
            $table->string('category')->nullable(); // before, after, demo, screenshot, etc.
            
            // المسارات
            $table->string('file_path'); // المسار الكامل للملف
            $table->string('thumbnail_path')->nullable(); // مسار الصورة المصغرة
            $table->string('url')->nullable(); // رابط عام للملف
            
            // معلومات تقنية
            $table->string('mime_type')->nullable(); // نوع MIME
            $table->integer('file_size')->nullable(); // حجم الملف بالبايت
            $table->integer('width')->nullable(); // العرض (للصور والفيديو)
            $table->integer('height')->nullable(); // الارتفاع (للصور والفيديو)
            $table->integer('duration')->nullable(); // المدة (للفيديو والصوت)
            
            // الترتيب والعرض
            $table->integer('order')->default(0); // ترتيب العرض
            $table->boolean('is_featured')->default(false); // مميز
            
            // معلومات إضافية
            $table->json('metadata')->nullable(); // بيانات وصفية إضافية
            
            $table->timestamps();
            
            // Indexes
            $table->index('project');
            $table->index('type');
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_files');
    }
};
