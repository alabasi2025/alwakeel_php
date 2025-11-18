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
        Schema::create('roadmap_items', function (Blueprint $table) {
            $table->id();
            $table->string('project'); // 'alabasi', 'integration', 'wakeel'
            $table->string('phase'); // 'المرحلة 1', 'المرحلة 2', etc.
            $table->integer('phase_number');
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->integer('progress')->default(0); // 0-100
            $table->integer('estimated_days')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roadmap_items');
    }
};
