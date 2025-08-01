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
        Schema::create('jumuiyas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('region');
            $table->string('district');
            $table->string('postal_code')->nullable();
            $table->string('ward');
            $table->string('status');
            $table->string('street')->nullable();
            $table->foreignId('church_id')->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jumuiyas');
    }
};
