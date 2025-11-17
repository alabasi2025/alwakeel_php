<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('terminal_commands', function (Blueprint $table) {
            $table->id();
            $table->string('shell_type')->default('powershell'); // powershell, cmd, bash
            $table->text('command');
            $table->longText('output')->nullable();
            $table->integer('exit_code')->nullable();
            $table->float('execution_time')->nullable();
            $table->string('working_directory')->nullable();
            $table->string('status')->default('pending'); // pending, success, error
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('terminal_commands');
    }
};
