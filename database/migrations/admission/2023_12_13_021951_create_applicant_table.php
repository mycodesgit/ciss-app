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
        Schema::create('ad_applicant_admission', function (Blueprint $table) {
            $table->id();
            $table->string('studagree');
            $table->integer('year');
            $table->integer('admission_id')->length(11)->unique();
            $table->enum('status', array(1, 2, 3, 4))->default(1);
            $table->enum('en_status', array(1, 2,))->nullable();
            $table->enum('p_status', array(1, 2, 3, 4,5,6))->default(1);
            $table->enum('type', array(1, 2, 36))->nullable();
            $table->string('campus')->nullable();
            $table->string('lname')->nullable();
            $table->string('fname')->nullable();
            $table->string('mname')->nullable();
            $table->string('ext')->nullable();
            $table->string('gender')->nullable();
            $table->string('bday')->nullable();
            $table->string('age')->nullable();
            $table->string('contact')->nullable();
            $table->string('email')->nullable();
            $table->string('civil_status')->nullable();
            $table->string('address')->nullable();
            $table->string('hnum')->nullable();
            $table->string('brgy')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('region')->nullable();
            $table->string('zcode')->nullable();
            $table->string('religion')->nullable();
            $table->string('monthly_income')->nullable();
            $table->string('lstsch_attended')->nullable();
            $table->string('strand')->nullable();
            $table->string('suc_lst_attended')->nullable();
            $table->string('course')->nullable();
            $table->string('preference_1')->nullable();
            $table->string('preference_2')->nullable();
            $table->string('dateID')->nullable();
            $table->string('d_admission')->nullable();
            $table->string('time')->nullable();
            $table->string('venue')->nullable();
            $table->string('image')->nullable();
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
        Schema::dropIfExists('ad_applicant_admission');
    }
};
