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
        Schema::create('commands', function (Blueprint $table) {
            $table->id();
            $table->string('command_name', 100);
            $table->text('command_text');
            $table->text('description')->nullable();
            $table->enum('category', ['github', 'hostinger', 'database', 'backup', 'general'])->default('general');
            $table->timestamps();
            
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commands');
    }
};
