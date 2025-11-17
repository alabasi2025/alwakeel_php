<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integrations', function (Blueprint $table) {
            $table->id();
            $table->string('service_name', 50)->unique();
            $table->enum('is_enabled', ['true', 'false'])->default('false');
            $table->json('config')->nullable();
            $table->timestamps();
            
            $table->index('is_enabled');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integrations');
    }
};
