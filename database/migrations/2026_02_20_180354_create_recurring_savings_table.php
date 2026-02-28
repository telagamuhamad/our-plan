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
        Schema::create('recurring_savings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('saving_id')->constrained('savings')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('frequency', ['daily', 'weekly', 'biweekly', 'monthly'])->default('monthly');
            $table->decimal('amount', 15, 2);
            $table->string('name')->nullable();
            $table->date('start_date');
            $table->date('next_run_date');
            $table->date('last_run_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('paused_at')->nullable();
            $table->unsignedInteger('skip_count')->default(0);
            $table->unsignedInteger('total_deposits')->default(0);
            $table->decimal('total_deposited_amount', 15, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['saving_id', 'is_active']);
            $table->index('next_run_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_savings');
    }
};
