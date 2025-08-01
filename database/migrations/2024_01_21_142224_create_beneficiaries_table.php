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
        Schema::create('beneficiaries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('group_leader_name')->nullable();
            $table->string('type');
            $table->string('gender');
            $table->string('phone_no');
            $table->string('status');
            $table->json('payment_mode');
            $table->json('account_name')->nullable();
            $table->json('account_provider')->nullable();
            $table->json('account_no')->nullable();
            $table->json('mobile_no')->nullable();
            $table->json('mobile_account_provider')->nullble();
            $table->json('mobile_account_name')->nullble();
            $table->string('frequency');
            $table->foreignId('church_id')->references('id')->on('churches');
            $table->foreignId('registered_by')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beneficiaries');
    }
};
