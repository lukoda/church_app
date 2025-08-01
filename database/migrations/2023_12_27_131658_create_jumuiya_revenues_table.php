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
        Schema::create('jumuiya_revenues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jumuiya_id')->constrained();
            $table->integer('jumuiya_attendance');
            $table->double('amount');
            $table->date('date_recorded');
            $table->string('approval_status');
            $table->foreignId('jumuiya_host_id')->references('id')->on('church_members');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jumuiya_revenues');
    }
};
