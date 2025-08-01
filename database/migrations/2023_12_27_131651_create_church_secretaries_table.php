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
        Schema::create('church_secretaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_member_id')->constrained();
            $table->date('date_registered');
            $table->string('status');
            $table->foreignId('church_assigned_id')->references('id')->on('churches');
            $table->string('title');
            $table->string('comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('church_secretaries');
    }
};
