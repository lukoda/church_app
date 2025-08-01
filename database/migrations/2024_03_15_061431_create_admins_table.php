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
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('phone')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('church_level');
            $table->foreignId('diocese_id')->nullable()->references('id')->on('dioceses');
            $table->foreignId('church_district_id')->nullable()->references('id')->on('church_districts');
            $table->foreignId('church_id')->nullable()->references('id')->on('churches');
            $table->foreignId('dinomination_id')->references('id')->on('dinominations');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
