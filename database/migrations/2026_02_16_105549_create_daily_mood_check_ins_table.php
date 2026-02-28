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
        Schema::create('daily_mood_check_ins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('couple_id')->constrained('couples')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('mood', ['happy', 'sad', 'angry', 'loved', 'tired', 'anxious', 'excited']);
            $table->text('note')->nullable();
            $table->date('check_in_date');
            $table->time('check_in_time')->useCurrent();
            $table->boolean('is_updated')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'check_in_date']);
            $table->index(['couple_id', 'check_in_date']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_mood_check_ins');
    }
};
