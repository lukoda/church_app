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
        Schema::create('offerings', function (Blueprint $table) {
            $table->id();
            $table->integer('card_no');
            $table->foreignId('card_type')->references('id')->on('cards');
            $table->double('amount_offered');
            $table->date('amount_registered_on');
            $table->foreignId('church_id')->constrained();
            $table->bigInteger('created_by')->references('id')->on('users');
            $table->bigInteger('updated_by')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offerings');
    }
};
