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
        Schema::create('gradesheetsignatory', function (Blueprint $table) {
            $table->id();
            $table->string('schlyear')->index();
            $table->string('semester')->index();
            $table->string('campus')->index();
            $table->string('namesign');
            $table->string('positionsign');
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
        Schema::dropIfExists('gradesheetsignatory');
    }
};
