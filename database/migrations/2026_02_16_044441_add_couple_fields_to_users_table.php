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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('couple_id')->nullable()->after('id')->constrained('couples')->nullOnDelete();
            $table->string('avatar_url')->nullable()->after('email');
            $table->string('timezone')->default('Asia/Jakarta')->after('avatar_url');

            $table->index('couple_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['couple_id']);
            $table->dropIndex(['couple_id']);
            $table->dropColumn(['couple_id', 'avatar_url', 'timezone']);
        });
    }
};
