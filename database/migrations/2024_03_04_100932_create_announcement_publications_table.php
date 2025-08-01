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
        Schema::create('announcement_publications', function (Blueprint $table) {
            $table->id();
            $table->string('level');
            $table->foreignId('announcement_id')->references('id')->on('announcements');
            $table->json('sub_parish')->nullable();
            $table->json('jumuiya')->nullable();
            $table->boolean('church_members')->nullable();
            $table->foreignId('church_id')->references('id')->on('churches');
            $table->foreignId('user_id')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcement_publications');
    }
};
