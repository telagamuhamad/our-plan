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
        Schema::create('timeline_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('couple_id')->constrained('couples')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // Post type: text, photo, voice_note
            $table->enum('post_type', ['text', 'photo', 'voice_note'])->default('text');

            // Content
            $table->text('content')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('attachment_mime_type')->nullable();
            $table->unsignedBigInteger('attachment_size_bytes')->nullable();

            // Timestamps
            $table->timestamp('posted_at')->useCurrent();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('couple_id');
            $table->index('user_id');
            $table->index('posted_at');
            $table->index('post_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timeline_posts');
    }
};
