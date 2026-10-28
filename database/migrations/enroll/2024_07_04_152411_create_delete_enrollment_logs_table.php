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
        Schema::create('delete_enrollment_logs', function (Blueprint $table) {
            $table->id();
            $table->string('delstudentID')->index();
            $table->string('delMC');
            $table->string('delemployeename');
            $table->string('delsemester');
            $table->string('delschlyear');
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
        Schema::dropIfExists('delete_enrollment_logs');
    }
};
