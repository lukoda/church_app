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
        Schema::create('church_service_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_member_id')->constrained();
            $table->foreignId('church_service_id')->constrained();
            $table->string('full_name')->nullable();
            $table->foreignId('requested_on_behalf_by')->nullable()->references('id')->on('users');
            $table->string('request_service_for')->nullable();
            $table->boolean('is_churchMember')->nullable();
            $table->date('date_requested');
            $table->text('message')->nullable();
            $table->string('jumuiya_chairperson_comment')->nullable();
            $table->string('jumuiya_chairperson_approval_status')->nullable();
            $table->string('approval_status')->nullable();
            $table->foreignId('approved_by')->nullable()->references('id')->on('users');
            $table->string('approval_comment')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('church_service_requests');
    }
};
