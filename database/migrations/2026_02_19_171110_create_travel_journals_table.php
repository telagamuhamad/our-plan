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
        Schema::create('travel_journals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('travel_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->string('title');
            $table->longText('content');
            $table->date('journal_date')->nullable();
            $table->string('mood')->nullable()->comment('Mood saat perjalanan');
            $table->string('weather')->nullable()->comment('Cuaca saat perjalanan');
            $table->string('location')->nullable()->comment('Lokasi spesifik kunjungan');
            $table->boolean('is_favorite')->default(false)->comment('Tandai sebagai favorit');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('travel_id')->references('id')->on('travels')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->index(['travel_id', 'journal_date']);
            $table->index(['user_id', 'journal_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travel_journals');
    }
};
