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
        Schema::create('pastors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_member_id')->constrained();
            $table->date('date_registered');
            $table->string('status');
            $table->string('comment')->nullable();
            $table->string('title');
            $table->foreignId('church_assigned_id')->references('id')->on('churches');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pastors');
    }
};
