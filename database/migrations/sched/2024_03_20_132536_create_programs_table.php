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
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('campus')->nullable();
            $table->string('progCod')->nullable();
            $table->string('progName')->nullable();
            $table->string('progLev')->nullable();
            $table->string('progSta')->nullable();
            $table->string('proglad')->nullable();
            $table->string('progDisc')->nullable();
            $table->string('progLic')->nullable();
            $table->string('progAut')->nullable();
            $table->string('progYer')->nullable();
            $table->string('progRev')->nullable();
            $table->string('progAcr')->nullable();
            $table->string('progYr1')->nullable();
            $table->string('progYr2')->nullable();
            $table->string('progNor')->nullable();
            $table->string('progUni')->nullable();
            $table->string('progDep')->nullable();
            $table->string('progCollege')->nullable();
            $table->string('progAcronym')->nullable();
            $table->string('progFund')->nullable();
            $table->string('progAccount')->nullable();
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
        Schema::dropIfExists('programs');
    }
};
