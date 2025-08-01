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
        Schema::create('beneficiary_request_items', function (Blueprint $table) {
            $table->id();
            $table->string('item');
            $table->text('description');
            $table->integer('quantity');
            $table->foreignId('beneficiary_request_id')->references('id')->on('beneficiary_requests');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beneficiary_request_items');
    }
};
