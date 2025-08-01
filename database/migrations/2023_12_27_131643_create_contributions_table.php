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
        Schema::create('contributions', function (Blueprint $table) {
            $table->id();
            $table->string('contribution_type');
            $table->string('title');
            $table->string('status');
            $table->string('frequency');
            $table->boolean('is_card');
            $table->date('date_of_contribution')->nullable();
            $table->json('active_on')->nullable();
            $table->foreignId('church_id')->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contributions');
    }
};
