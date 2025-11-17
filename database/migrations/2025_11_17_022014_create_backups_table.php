<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // system, database, full
            $table->string('file_path');
            $table->bigInteger('file_size')->nullable();
            $table->string('storage_location'); // local, onedrive, external
            $table->text('external_path')->nullable();
            $table->string('status')->default('pending'); // pending, completed, failed
            $table->text('error_message')->nullable();
            $table->boolean('is_scheduled')->default(false);
            $table->string('schedule_frequency')->nullable(); // daily, weekly, monthly
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backups');
    }
};
