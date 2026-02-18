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
        Schema::table('daily_questions', function (Blueprint $table) {
            // Answer mode preference: 'app' or 'call'
            $table->string('answer_mode_one')->default('app')->after('answered_one_at');
            $table->string('answer_mode_two')->default('app')->after('answered_two_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_questions', function (Blueprint $table) {
            $table->dropColumn(['answer_mode_one', 'answer_mode_two']);
        });
    }
};
