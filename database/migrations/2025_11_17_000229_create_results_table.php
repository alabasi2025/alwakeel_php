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
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('command_id')->constrained()->onDelete('cascade');
            $table->text('result_text');
            $table->enum('status', ['success', 'error', 'warning'])->default('success');
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            $table->index(['command_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};
