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
        Schema::create('curriculum', function (Blueprint $table) {
            $table->id();
            $table->string('progCode')->nullable()->index();
            $table->integer('yrlvl')->nullable();
            $table->string('subSec')->nullable();
            $table->string('semester')->nullable();
            $table->string('campus')->nullable();
            $table->string('subCode')->nullable()->index();
            $table->string('lecUnit')->nullable();
            $table->string('labUnit')->nullable();
            $table->string('subUnit')->nullable();
            $table->string('lecFee')->nullable();
            $table->string('labFee')->nullable();
            $table->string('devFee')->nullable();
            $table->string('isOJT')->nullable();
            $table->string('isTemp')->nullable();
            $table->string('fund')->nullable();
            $table->string('fundAccount')->nullable();
            $table->enum('itfee', ['No', 'Yes'])->default('No');
            $table->string('isType')->nullable();
            $table->string('postedBy')->nullable();
            $table->string('prerequisite')->nullable();
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
        Schema::dropIfExists('curriculum');
    }
};
