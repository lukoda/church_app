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
        Schema::create('auction_item_payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_mode');
            $table->double('amount_payed');
            $table->date('date_registered');
            $table->string('description')->nullable();
            $table->string('account_provider')->nullable();
            $table->string('bank_branch_name')->nullable();
            $table->string('bank_transaction_id')->nullable();
            $table->string('mobile_account_provider')->nullable();
            $table->string('mobile_transaction_id')->nullable();
            $table->string('receipt_picture')->nullable();
            $table->string('verification_status');
            $table->foreignId('church_id')->references('id')->on('churches');
            $table->foreignId('auction_item_id')->references('id')->on('auction_items');
            $table->foreignId('registered_by')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auction_item_payments');
    }
};
