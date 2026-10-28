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
        Schema::create('studentrfidcard', function (Blueprint $table) {
            $table->id();
            $table->string('stdntid');
            $table->string('stdntrfid')->unique();
            $table->string('studphoto')->nullable();
            $table->string('studsignature')->nullable();
            $table->string('contactperson')->nullable();
            $table->string('contactpersonno')->nullable();
            $table->string('campus');
            $table->string('schlyear')->nullable();
            $table->string('semester')->nullable();
            $table->string('postedBy');
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
        Schema::dropIfExists('studentrfidcard');
    }
};
