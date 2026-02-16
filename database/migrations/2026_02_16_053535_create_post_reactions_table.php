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
        Schema::create('post_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('timeline_posts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // Emoji reactions: heart, laugh, wow, sad, angry
            $table->enum('emoji', ['heart', 'laugh', 'wow', 'sad', 'angry']);

            $table->timestamps();

            // One reaction per user per post - unique constraint
            $table->unique(['post_id', 'user_id']);

            // Indexes
            $table->index('post_id');
            $table->index('user_id');
            $table->index('emoji');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_reactions');
    }
};
