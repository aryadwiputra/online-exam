<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->unsignedInteger('duration');
            $table->text('description')->nullable();
            $table->boolean('random_question')->default(true);
            $table->boolean('random_answer')->default(true);
            $table->boolean('show_answer')->default(false);
            $table->timestamps();

            $table->index(['classroom_id', 'lesson_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
