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
        Schema::create('church_members', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('middle_name');
            $table->string('surname');
            $table->string('full_name')->virtualAs('concat(first_name, \' \', middle_name, \' \' ,surname)');
            $table->string('email')->unique()->nullable();
            $table->string('gender');
            $table->string('phone')->unique();
            $table->date('date_of_birth')->nullable();
            $table->date('date_registered')->nullable();
            $table->string('nida_id')->nullable();
            $table->string('passport_id')->nullable();
            $table->string('picture')->nullable();
            $table->boolean('is_NewMember')->nullable();
            $table->integer('card_no')->nullable();
            $table->string('personal_details')->nullable();
            //address information
            $table->string('postal_code')->nullable();
            $table->foreignId('region_id')->nullable()->constrained();
            $table->foreignId('district_id')->nullable()->constrained();
            $table->foreignId('ward_id')->nullable()->constrained();
            $table->string('street')->nullable();
            $table->string('block_no')->nullable();
            $table->string('house_no')->nullable();
            $table->string('address_details')->nullable();
            //spiritual information
            $table->foreignId('jumuiya_id')->nullable()->references('id')->on('jumuiyas');
            $table->boolean('received_confirmation')->nullable();
            $table->string('confirmation_place')->nullable();
            $table->date('confirmation_date')->nullable();
            $table->boolean('received_baptism')->nullable();
            $table->string('baptism_place')->nullable();
            $table->date('baptism_date')->nullable();
            $table->string('volunteering_in')->nullable();
            $table->string('sacrament_participation')->nullable();
            $table->string('previous_church')->nullable();
            $table->string('spiritual_information')->nullable();
            //Marriage Information
            $table->string('marital_status')->nullable();
            $table->foreignId('spouse_id')->nullable()->references('id')->on('church_members');
            $table->string('spouse_name')->nullable();
            $table->string('spouse_contact_no')->nullable();
            //education and field information
            $table->string('education_level')->nullable();
            $table->string('profession')->nullable();
            $table->string('skills')->nullable();
            $table->string('work_location')->nullable();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('church_id')->constrained();
            $table->string('status')->nullable();
            $table->foreignId('physically_approved_by')->nullable()->references('id')->on('users');
            $table->string('comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('church_members');
    }
};
