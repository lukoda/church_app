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
        Schema::create('church_districts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('diocese_id')->references('id')->on('dioceses');
            $table->string('name');
            $table->string('status');
            $table->json('all_wards');
            $table->json('regions');
            $table->json('districts');
            $table->json('wards');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('church_districts');
    }
};
