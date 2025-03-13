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
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('travelling_user_id')->nullable(); //siapa yang berangkat
            $table->date('meeting_date')->nullable(); //tanggal pertemuan
            $table->string('location')->nullable(); //lokasi
            $table->boolean('is_departure_transport_ready')->default(false); //transportasi keberangkatan siap
            $table->boolean('is_return_transport_ready')->default(false); //transportasi kembali siap
            $table->boolean('is_rest_place_ready')->default(false); //tempat istirahat siap
            $table->longText('note')->nullable(); //catatan
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};
