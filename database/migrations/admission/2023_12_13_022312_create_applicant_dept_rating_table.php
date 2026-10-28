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
        Schema::create('ad_applicant_dept_rating', function (Blueprint $table) {
            $table->id();
            $table->integer('app_id');
            $table->integer('interviewerid');
            $table->string('camp');
            $table->integer('admission_id');
            $table->string('interviewer')->nullable();
            $table->integer('rating')->length(5)->nullable();
            $table->string('remarks')->nullable();
            $table->string('course')->nullable();
            $table->string('deptcol')->nullable();
            $table->string('reason')->nullable();
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
        Schema::dropIfExists('ad_applicant_dept_rating');
    }
};
