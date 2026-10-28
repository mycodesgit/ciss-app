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
        Schema::create('class_enroll', function (Blueprint $table) {
            $table->id();
            $table->string('schlyear')->nullable();
            $table->integer('semester')->nullable();
            $table->string('campus')->nullable();
            $table->integer('progCode')->nullable();
            $table->string('classSection')->nullable();
            $table->string('classno')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('class_enroll');
    }
};
