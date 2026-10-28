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
        Schema::create('faculty', function (Blueprint $table) {
            $table->id();
            $table->string('campus');
            $table->string('campactive');
            $table->string('faccollege');
            $table->string('facdept')->nullable();
            $table->string('deptmajor')->nullable();
            $table->string('fname');
            $table->string('mname');
            $table->string('lname');
            $table->string('ext');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('rank');
            $table->string('role');
            $table->integer('prefix');
            $table->string('suffix');
            $table->string('verification_code')->nullable();
            $table->integer('status');
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
        Schema::dropIfExists('faculty');
    }
};
