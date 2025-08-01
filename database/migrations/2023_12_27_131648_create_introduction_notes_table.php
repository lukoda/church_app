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
        Schema::create('introduction_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_church_id')->references('id')->on('churches');
            $table->foreignId('to_church_id')->nullable()->references('id')->on('churches');
            $table->foreignId('church_member_id')->constrained();
            $table->string('title');
            $table->string('description');
            $table->date('date_requested');
            $table->integer('sundays_on_leave');
            $table->date('approved_on')->nullable();
            $table->date('date_of_return');
            $table->foreignId('region_id')->nullable()->references('id')->on('regions');
            $table->foreignId('district_id')->nullable()->references('id')->on('districts');
            $table->foreignId('ward_id')->nullable()->references('id')->on('wards');
            $table->string('approval_status')->nullable();
            $table->string('leaving_note')->nullable();
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('introduction_notes');
    }
};
