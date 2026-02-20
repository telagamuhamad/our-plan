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
            // Check if column doesn't exist before adding
            if (!Schema::hasColumn('savings', 'is_completed')) {
                $table->boolean('is_completed')->default(false)->after('is_shared');
            }
            if (!Schema::hasColumn('savings', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('is_completed');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('savings', function (Blueprint $table) {
            if (Schema::hasColumn('savings', 'completed_at')) {
                $table->dropColumn('completed_at');
            }
            if (Schema::hasColumn('savings', 'is_completed')) {
                $table->dropColumn('is_completed');
            }
        });
    }
};
