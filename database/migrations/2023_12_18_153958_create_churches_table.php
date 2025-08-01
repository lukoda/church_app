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
        Schema::create('churches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('church_type');
            $table->foreignId('parent_church')->nullable()->references('id')->on('churches');
            $table->json('pictures')->nullable();
            $table->geometry('church_location')->nullable();
            $table->foreignId('church_district_id')->constrained();
            $table->foreignId('region_id')->constrained();
            $table->foreignId('district_id')->constrained();
            $table->foreignId('ward_id')->constrained();
            $table->json('pastors')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('churches');
    }
};
