<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE notifications MODIFY COLUMN type ENUM('reaction', 'comment', 'mention', 'mood_check_in', 'mood_update', 'missing_you') DEFAULT 'reaction'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE notifications MODIFY COLUMN type ENUM('reaction', 'comment', 'mention', 'mood_check_in', 'mood_update') DEFAULT 'reaction'");
    }
};
