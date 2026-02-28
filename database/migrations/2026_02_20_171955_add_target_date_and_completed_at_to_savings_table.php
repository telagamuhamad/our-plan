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
        Schema::table('savings', function (Blueprint $table) {
            $table->date('target_date')->nullable()->after('is_shared');
            $table->timestamp('completed_at')->nullable()->after('target_date');
            $table->unsignedInteger('last_notified_milestone')->default(0)->after('completed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('savings', function (Blueprint $table) {
            $table->dropColumn(['target_date', 'completed_at', 'last_notified_milestone']);
        });
    }
};
