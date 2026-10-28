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
        Schema::create('studentsgradinfo', function (Blueprint $table) {
            $table->id();
            $table->integer('studIDprim')->nullable();
            $table->string('studIDno')->nullable();
            $table->string('spouseparent')->nullable();
            $table->string('elementary')->nullable();
            $table->string('elemyeargrad')->nullable();
            $table->string('highschool')->nullable();
            $table->string('highschoolyeargrad')->nullable();
            $table->string('tertiary')->nullable();
            $table->string('tertiaryyeargrad')->nullable();
            $table->string('tertiarycourse')->nullable();
            $table->string('tertiarymajor')->nullable();
            $table->string('masterspeciallization')->nullable();
            $table->string('masterschool')->nullable();
            $table->string('masternounit')->nullable();
            $table->string('masteraddress')->nullable();
            $table->string('masterinclyear')->nullable();
            $table->string('doctorcourse')->nullable();
            $table->string('doctorspecialization')->nullable();
            $table->string('doctorschool')->nullable();
            $table->string('doctornounit')->nullable();
            $table->string('doctoraddress')->nullable();
            $table->string('doctorinclyear')->nullable();
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
        Schema::dropIfExists('studentsgradinfo');
    }
};
