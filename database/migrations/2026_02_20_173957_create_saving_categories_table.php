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
        Schema::create('saving_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icon')->default('💰');
            $table->string('color')->default('#6c757d');
            $table->string('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // Add category_id to savings table
        Schema::table('savings', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('user_id')->constrained('saving_categories')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('savings', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });

        Schema::dropIfExists('saving_categories');
    }
};
