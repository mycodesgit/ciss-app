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
        Schema::create('student_fee', function (Blueprint $table) {
            $table->id();
            $table->integer('prog_Code')->nullable();
            $table->string('yrlevel')->nullable();
            $table->string('schlyear')->nullable();
            $table->integer('semester')->nullable();
            $table->string('campus')->nullable();
            $table->integer('fundname_code')->nullable();
            $table->string('amountFee')->nullable();
            $table->string('accountName')->nullable();
            $table->integer('postedBy')->nullable();
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
        Schema::dropIfExists('student_fee');
    }
};
