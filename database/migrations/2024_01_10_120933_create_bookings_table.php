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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_member_id')->constrained();
            $table->foreignId('pastor_schedule_id')->constrained();
            $table->foreignId('booked_schedule')->references('id')->on('schedules');
            $table->integer('approval_status');
            $table->foreignId('church_id')->references('id')->on('churches');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
