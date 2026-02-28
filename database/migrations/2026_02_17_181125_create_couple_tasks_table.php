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
        Schema::create('couple_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('couple_id')->constrained('couples')->cascadeOnDelete();
            $table->foreignId('goal_id')->nullable()->constrained('couple_goals')->cascadeOnDelete();

            // Task details
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->date('due_date')->nullable();

            // Assignment
            $table->enum('assigned_to', ['user_one', 'user_two', 'both'])->default('both');

            // Status
            $table->boolean('is_completed')->default(false);
            $table->unsignedBigInteger('completed_by')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();

            // Reminder
            $table->boolean('reminder_enabled')->default(false);
            $table->timestamp('reminder_sent_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('couple_id');
            $table->index('goal_id');
            $table->index('is_completed');
            $table->index('due_date');
            $table->index('priority');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('couple_tasks');
    }
};
