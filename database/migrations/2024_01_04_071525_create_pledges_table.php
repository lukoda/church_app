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
        Schema::create('pledges', function (Blueprint $table) {
            $table->id();
            $table->integer('card_no')->nullable();
            $table->string('phone_no')->nullable();
            $table->string('full_name')->nullable();
            $table->double('amount_pledged');
            $table->double('amount_completed');
            $table->double('amount_remain');
            $table->date('date_of_pledge');
            $table->foreignId('church_id')->constrained();
            $table->foreignId('church_member_id')->nullable()->references('id')->on('church_members');
            $table->foreignId('log_contibution_id')->references('id')->on('log_contributions');
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pledges');
    }
};
