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
        Schema::create('preenrollsubj', function (Blueprint $table) {
            $table->id();
            $table->string('studID')->nullable()->index();
            $table->string('subjID')->nullable()->index();
            $table->string('subjFgrade')->nullable();
            $table->string('subjComp')->nullable();
            $table->string('creditEarned')->nullable();
            $table->enum('status', array(1, 2))->nullable();
            $table->string('postedBy')->nullable();
            $table->string('campus')->nullable();
            $table->string('encode')->nullable();
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
        Schema::dropIfExists('preenrollsubj');
    }
};
