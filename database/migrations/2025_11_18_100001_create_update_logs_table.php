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
        Schema::create('update_logs', function (Blueprint $table) {
            $table->id();
            $table->string('project'); // 'alabasi', 'wakeel', 'integration'
            $table->foreignId('roadmap_item_id')->nullable()->constrained()->onDelete('set null');
            
            // معلومات التحديث
            $table->string('version')->nullable(); // رقم الإصدار
            $table->string('title'); // عنوان التحديث
            $table->text('description'); // وصف التحديث
            $table->enum('type', ['feature', 'enhancement', 'bugfix', 'security', 'performance'])->default('feature');
            
            // التفاصيل
            $table->text('what_added')->nullable(); // ما تم إضافته
            $table->text('what_changed')->nullable(); // ما تم تغييره
            $table->text('what_fixed')->nullable(); // ما تم إصلاحه
            $table->text('how_it_changed')->nullable(); // كيف تغيرت طريقة العمل
            
            // معلومات Git
            $table->string('commit_hash')->nullable(); // hash الـ commit
            $table->string('commit_message')->nullable(); // رسالة الـ commit
            $table->string('author')->nullable(); // المطور
            $table->timestamp('committed_at')->nullable(); // تاريخ الـ commit
            
            // الملفات
            $table->json('files_modified')->nullable(); // الملفات المعدلة
            $table->json('files_added')->nullable(); // الملفات المضافة
            $table->json('files_deleted')->nullable(); // الملفات المحذوفة
            
            // الوسائط
            $table->json('screenshots')->nullable(); // صور توضيحية
            $table->json('videos')->nullable(); // فيديوهات
            
            $table->timestamps();
            
            // Indexes
            $table->index('project');
            $table->index('type');
            $table->index('committed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('update_logs');
    }
};
