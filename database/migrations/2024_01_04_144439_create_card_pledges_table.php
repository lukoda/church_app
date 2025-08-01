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
        Schema::create('card_pledges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_member_id')->references('id')->on('church_members');
            $table->foreignId('card_id')->references('id')->on('cards');
            $table->integer('card_no')->nullable();
            $table->double('amount_pledged');
            $table->double('amount_completed');
            $table->double('amount_remains');
            $table->date('date_pledged');
            $table->string('status');
            $table->foreignId('church_id')->references('id')->on('churches');
            $table->foreignId('created_by')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('card_pledges');
    }
};
