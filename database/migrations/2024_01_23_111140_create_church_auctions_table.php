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
        Schema::create('church_auctions', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->foreignId('log_offering')->references('id')->on('log_offerings'); 
            $table->date('auction_date');
            $table->text('auction_description')->nullable();
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('church_auctions');
    }
};
