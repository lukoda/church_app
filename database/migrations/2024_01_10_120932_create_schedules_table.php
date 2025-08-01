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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->time('from');
            $table->time('to');
            $table->integer('status');
            $table->integer('max_members');
            $table->integer('current_booked_members')->default(0);
            $table->integer('pending_approvals')->default(0);
            $table->date('day')->nullable();
            $table->string('day_of_week')->nullable();
            $table->foreignId('pastor_schedule_id')->references('id')->on('pastor_schedules');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
