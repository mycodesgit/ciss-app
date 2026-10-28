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
        Schema::create('studgrades_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('grdeprimID')->nullable()->index();
            $table->string('studsID')->nullable()->index();
            $table->integer('subjctsID')->nullable()->index();
            $table->string('datefgrade')->nullable();
            $table->string('datecgrade')->nullable();
            $table->string('campus')->nullable();
            $table->string('fgrade')->nullable();
            $table->string('cgrade')->nullable();
            $table->string('encodedBy')->nullable();
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
        Schema::dropIfExists('studgrades_logs');
    }
};
