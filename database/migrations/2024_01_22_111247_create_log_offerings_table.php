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
        Schema::create('log_offerings', function (Blueprint $table) {
            $table->id();
            $table->double('amount_committee');
            $table->double('amount_accountant')->nullable();
            $table->date('date');
            $table->boolean('has_auction');
            $table->foreignId('church_mass_id')->references('id')->on('church_masses');
            $table->foreignId('adhoc_offering_id')->constrained('adhoc_offerings');
            $table->foreignId('church_id')->constrained('churches');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('approved_by')->nullable()->references('id')->on('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_offerings');
    }
};
