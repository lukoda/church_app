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
        Schema::create('beneficiary_request_item_payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_mode')->nullable();
            $table->string('request_type');
            $table->integer('item_quantity_payed')->nullable();
            $table->integer('item_quantity_verified')->nullable();
            $table->string('secret_key')->nullable();
            $table->date('pay_date');
            $table->float('amount_payed')->nullable();
            $table->float('amount_payed_verified')->nullable();
            $table->string('account_provider')->nullable();
            $table->string('bank_branch_name')->nullable();
            $table->string('bank_transaction_id')->nullable();
            $table->string('mobile_account_provider')->nullable();
            $table->string('mobile_transaction_id')->nullable();
            $table->json('receipt_picture')->nullable();
            $table->foreignId('item_id')->references('id')->on('beneficiary_request_item_pledges');
            $table->foreignId('user_id')->references('id')->on('users');
            $table->string('verification_status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beneficiary_request_item_payments');
    }
};
