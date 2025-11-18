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
        Schema::create('feature_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('roadmap_item_id')->constrained()->onDelete('cascade');
            
            // معلومات الميزة
            $table->text('detailed_description')->nullable(); // شرح تفصيلي للميزة
            $table->text('benefits')->nullable(); // فوائد الميزة
            $table->text('use_cases')->nullable(); // حالات الاستخدام
            
            // ما تم بناؤه
            $table->text('what_built')->nullable(); // ما تم بناؤه بالتفصيل
            $table->json('files_added')->nullable(); // الملفات المضافة
            $table->json('technical_details')->nullable(); // التفاصيل التقنية
            
            // ما المتبقي
            $table->text('what_remaining')->nullable(); // ما المتبقي
            $table->json('next_steps')->nullable(); // الخطوات القادمة
            
            // قبل وبعد
            $table->text('before_description')->nullable(); // وصف النظام قبل
            $table->text('after_description')->nullable(); // وصف النظام بعد
            $table->string('before_image')->nullable(); // صورة قبل
            $table->string('after_image')->nullable(); // صورة بعد
            $table->string('demo_gif')->nullable(); // GIF توضيحي
            
            // دليل الاستخدام
            $table->text('user_guide')->nullable(); // دليل استخدام تفاعلي
            $table->json('guide_steps')->nullable(); // خطوات الدليل
            
            // معلومات إضافية
            $table->json('screenshots')->nullable(); // صور إضافية
            $table->json('videos')->nullable(); // فيديوهات
            $table->json('related_features')->nullable(); // ميزات مرتبطة
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feature_details');
    }
};
