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
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('dinomination_id')->references('id')->on('dinominations');
            $table->json('documents')->nullable();
            $table->text('message');
            $table->date('begin_date');
            $table->date('end_date');
            $table->integer('duration');
            $table->string('status');
            $table->string('level');
            $table->boolean('all_dioceses')->nullable();
            $table->json('diocese')->nullable();
            $table->boolean('all_church_districts')->nullable();
            $table->json('church_districts')->nullable();
            $table->boolean('all_churches')->nullable();
            $table->json('church')->nullable();
            $table->boolean('all_sub_parishes')->nullable();
            $table->json('sub_parish')->nullable();
            $table->boolean('all_jumuiyas')->nullable();
            $table->json('jumuiya')->nullable();
            $table->string('published_level');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
