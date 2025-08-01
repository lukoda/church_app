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
        Schema::create('log_contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contribution_id')->references('id')->on('contributions');
            $table->string('level');
            $table->date('date_logged');
            $table->foreignId('logged_by')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_contributions');
    }
};
