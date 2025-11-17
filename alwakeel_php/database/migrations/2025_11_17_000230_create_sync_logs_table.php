<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_logs', function (Blueprint $table) {
            $table->id();
            $table->enum('sync_type', ['github_pull', 'github_push', 'hostinger_deploy', 'backup'])->default('github_pull');
            $table->enum('status', ['success', 'error', 'pending'])->default('pending');
            $table->text('message')->nullable();
            $table->text('details')->nullable();
            $table->timestamps();
            
            $table->index(['sync_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_logs');
    }
};
