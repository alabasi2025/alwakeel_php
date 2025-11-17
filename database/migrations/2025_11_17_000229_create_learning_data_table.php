<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_data', function (Blueprint $table) {
            $table->id();
            $table->text('user_input');
            $table->text('system_response');
            $table->integer('success_score')->default(0);
            $table->timestamps();
            
            $table->index('success_score');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_data');
    }
};
