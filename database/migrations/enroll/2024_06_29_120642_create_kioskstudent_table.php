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
        Schema::create('kioskstudent', function (Blueprint $table) {
            $table->id();
            $table->string('studid')->index();
            $table->string('password');
            $table->string('passtext')->nullable();
            $table->integer('postedBy')->nullable();
            $table->string('role')->nullable();
            $table->integer('resetnumber')->nullable();
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
        Schema::dropIfExists('kioskstudent');
    }
};
