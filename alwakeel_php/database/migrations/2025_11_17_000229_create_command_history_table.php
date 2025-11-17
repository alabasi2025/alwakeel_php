<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('command_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('command_id')->constrained()->onDelete('cascade');
            $table->timestamp('executed_at');
            $table->enum('status', ['success', 'error'])->default('success');
            $table->text('output')->nullable();
            $table->timestamps();
            
            $table->index(['command_id', 'executed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('command_history');
    }
};
