<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->integer('year')->length(11);
            $table->string('stud_id')->length(12);
            $table->integer('app_id')->length(11);
            $table->enum('status', array(1, 2, 3, 4))->default(1);
            $table->enum('en_status', array(1, 2, 3))->nullable();
            $table->integer('yeargrad')->nullable();
            $table->enum('p_status', array(1, 2, 3, 4,5,6))->default(1);
            $table->enum('type', array(1, 2, 3, 4))->nullable();
            $table->string('campus')->nullable();
            $table->string('lname')->nullable();
            $table->string('fname')->nullable();
            $table->string('mname')->nullable();
            $table->string('ext')->nullable();
            $table->string('course')->nullable();
            $table->string('gender')->nullable();
            $table->string('civil_status')->nullable();
            $table->string('contact')->nullable();
            $table->string('email')->nullable();
            $table->string('religion')->nullable();
            $table->string('address')->nullable();
            $table->string('bday')->nullable();
            $table->string('pbirth')->nullable();
            $table->string('monthly_income')->nullable();
            $table->string('hnum')->nullable();
            $table->string('brgy')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('region')->nullable();
            $table->string('zcode')->nullable();
            $table->string('lstsch_attended')->nullable();
            $table->string('suc_lst_attended')->nullable();
            $table->integer('studlegstat_id')->nullable();
            $table->string('stud_father')->nullable(); 
            $table->string('stud_mother')->nullable();
            $table->string('stud_guardian')->nullable();
            $table->string('guardian_contact')->nullable();
            $table->string('lst_sch_attended_year')->nullable();
            $table->date('date_admission')->nullable();
            $table->date('graduation_date')->nullable();
            $table->integer('postedBy')->nullable();
            $table->string('stud_pic')->nullable();
            $table->string('graduation_schlyear')->nullable();
            $table->string('graduation_semester')->nullable();
            $table->string('graduation_course')->nullable();
            $table->string('lst_sch_type')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('students');
    }
};
