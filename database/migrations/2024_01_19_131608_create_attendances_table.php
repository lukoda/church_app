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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->date('date');
            $table->string('remark')->nullable();
            $table->integer('men');
            $table->integer('women');
            $table->integer('children');
            // $table->integer('status');
            $table->foreignId('church_id')->references('id')->on('churches');
            $table->foreignId('church_mass_id')->references('id')->on('church_masses');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
