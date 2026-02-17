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
        Schema::create('couple_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('couple_id')->constrained('couples')->cascadeOnDelete();

            // Goal details
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category')->nullable(); // travel, financial, relationship, personal, etc
            $table->date('target_date')->nullable();

            // Status tracking
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('completed_at')->nullable();

            // Progress
            $table->integer('total_tasks')->default(0);
            $table->integer('completed_tasks')->default(0);
            $table->decimal('progress_percentage', 5, 2)->default(0);

            $table->timestamps();

            // Indexes
            $table->index('couple_id');
            $table->index('status');
            $table->index('target_date');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('couple_goals');
    }
};
