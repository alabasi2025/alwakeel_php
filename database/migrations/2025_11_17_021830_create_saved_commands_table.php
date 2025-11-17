<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_commands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('command');
            $table->text('description')->nullable();
            $table->string('category')->default('general'); // laravel, git, composer, system, database
            $table->string('shell_type')->default('powershell'); // powershell, cmd, bash
            $table->string('icon')->nullable();
            $table->integer('usage_count')->default(0);
            $table->boolean('is_favorite')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_commands');
    }
};
