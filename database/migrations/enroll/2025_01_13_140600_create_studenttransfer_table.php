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
        Schema::create('studenttransfer', function (Blueprint $table) {
            $table->id();
            $table->string('stud_id')->length(12);
            $table->integer('studbaseprim_id')->length(12);
            $table->string('fromcampus')->nullable();
            $table->string('tocampus')->nullable();
            $table->integer('transferby')->nullable();
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
        Schema::dropIfExists('studenttransfer');
    }
};
