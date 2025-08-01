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
        Schema::create('service_offerings', function (Blueprint $table) {
            $table->id();
            $table->integer('card_no')->nullable();
            $table->string('full_name')->nullable();
            $table->string('phone')->nullable();
            $table->foreignId('church_member_id')->nullable()->constrained();
            $table->foreignId('church_id')->constrained();
            $table->date('date');
            $table->double('amount');
            $table->string('status');
            $table->foreignId('log_contibution_id')->references('id')->on('log_contributions');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_offerings');
    }
};
