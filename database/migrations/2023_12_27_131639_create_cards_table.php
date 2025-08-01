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
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->string('card_name');
            $table->foreignId('church_id')->references('id')->on('churches');
            $table->string('card_description')->nullable();
            // $table->integer('card_duration');
            // $table->text('verse_for_card');
            $table->string('card_color');
            $table->string('card_status');
            $table->double('card_target')->default(0);
            $table->double('minimum_target')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
