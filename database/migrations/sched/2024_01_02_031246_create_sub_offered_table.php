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
        Schema::create('sub_offered', function (Blueprint $table) {
            $table->id();
            $table->string('subCode')->nullable()->index();
            $table->string('subSec')->nullable();
            $table->string('schlyear')->nullable();
            $table->string('semester')->nullable();
            $table->string('campus')->nullable();
            $table->string('lecFee')->nullable();
            $table->string('labFee')->nullable();
            $table->string('devFee')->nullable();
            $table->string('subUnit')->nullable();
            $table->string('postedBy')->nullable();
            $table->string('datePosted')->nullable();
            $table->string('lecUnit')->nullable();
            $table->string('labUnit')->nullable();
            $table->string('maxstud')->nullable();
            $table->string('isOJT')->nullable();
            $table->string('isTemp')->nullable();
            $table->string('isType')->nullable();
            $table->string('fund')->nullable();
            $table->enum('itfee', ['No', 'Yes'])->default('No');
            $table->string('fundAccount')->nullable();
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
        Schema::dropIfExists('sub_offered');
    }
};
