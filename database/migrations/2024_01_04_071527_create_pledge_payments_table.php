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
        Schema::create('pledge_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pledge_id')->references('id')->on('pledges');
            $table->double('amount');
            $table->date('date_payed');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pledge_payments');
    }
};
