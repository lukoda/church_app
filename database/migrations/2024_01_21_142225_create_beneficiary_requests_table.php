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
        Schema::create('beneficiary_requests', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('beneficiary_type');
            $table->foreignId('church_id')->constrained();
            $table->double('amount');
            $table->text('purpose');
            $table->json('supporting_documents')->nullable();
            $table->date('begin_date');
            $table->date('end_date');
            $table->string('status_approval')->nullable();
            $table->string('request_visible_on');
            $table->string('comment')->nullable();
            $table->string('frequency')->nullable();
            $table->date('inactive_on')->nullable();
            $table->integer('weeks')->nullable();
            $table->integer('months')->nullable();
            $table->string('status')->nullable();
            $table->double('amount_threshold')->nullable();
            $table->foreignId('beneficiary_id')->references('id')->on('beneficiaries');
            $table->foreignId('registered_by')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beneficiary_requests');
    }
};
