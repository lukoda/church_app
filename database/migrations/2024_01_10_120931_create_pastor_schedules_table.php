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
        Schema::create('pastor_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('frequency');
            $table->foreignId('church_id')->constrained();
            $table->foreignId('pastor_id')->references('id')->on('pastors');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pastor_schedules');
    }
};
