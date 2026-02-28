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
        Schema::create('daily_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('couple_id')->constrained('couples')->cascadeOnDelete();
            $table->date('question_date')->unique();

            // Question
            $table->text('question');
            $table->string('category')->nullable(); // romantic, fun, deep, future, etc

            // Answers
            $table->text('answer_one')->nullable();
            $table->unsignedBigInteger('answered_by_one')->nullable();
            $table->timestamp('answered_one_at')->nullable();

            $table->text('answer_two')->nullable();
            $table->unsignedBigInteger('answered_by_two')->nullable();
            $table->timestamp('answered_two_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->unique(['couple_id', 'question_date']);
            $table->index('couple_id');
            $table->index('question_date');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_questions');
    }
};
