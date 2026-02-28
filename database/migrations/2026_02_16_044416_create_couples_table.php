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
        Schema::create('couples', function (Blueprint $table) {
            $table->id();
            $table->string('invite_code', 6)->unique();
            $table->foreignId('user_one_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('user_two_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['pending', 'active'])->default('pending');
            $table->timestamp('user_one_confirmed_at')->nullable();
            $table->timestamp('user_two_confirmed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('invite_code');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('couples');
    }
};
