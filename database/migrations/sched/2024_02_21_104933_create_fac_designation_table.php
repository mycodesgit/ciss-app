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
        Schema::create('fac_designation', function (Blueprint $table) {
            $table->id();
            $table->string('schlyear')->nullable();
            $table->integer('semester')->nullable();
            $table->string('campus')->nullable();
            $table->string('facCollege')->nullable();
            $table->integer('fac_id')->nullable();
            $table->string('designation')->nullable();
            $table->integer('dunit')->nullable();
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
        Schema::dropIfExists('fac_designation');
    }
};
