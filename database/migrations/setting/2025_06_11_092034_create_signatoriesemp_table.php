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
        Schema::create('signatoriesemp', function (Blueprint $table) {
            $table->id();
            $table->string('fulname')->nullable();
            $table->string('titledeg')->nullable();
            $table->string('position')->nullable();
            $table->string('schlyear')->nullable();
            $table->string('semester')->nullable();
            $table->string('campus')->nullable();
            $table->enum('status', ['1', '2'])->default('1');
            $table->text('esign')->nullable();
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
        Schema::dropIfExists('signatoriesemp');
    }
};
