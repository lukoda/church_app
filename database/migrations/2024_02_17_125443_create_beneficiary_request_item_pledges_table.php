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
        Schema::create('beneficiary_request_item_pledges', function (Blueprint $table) {
            $table->id();
            $table->integer('item_quantity_pledged')->nullable();
            $table->integer('item_quantity_complete')->nullable();
            $table->float('amount_pledged')->nullable();
            $table->float('amount_completed')->nullable();
            $table->string('payment_status');
            $table->foreignId('pledged_item_id')->nullable()->references('id')->on('beneficiary_request_items');
            $table->foreignId('request_item_id')->nullable()->references('id')->on('beneficiary_requests');
            $table->foreignId('user_id')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beneficiary_request_item_pledges');
    }
};
