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
        Schema::create('counters', function (Blueprint $table) {
            $table->id();
            $table->integer('windowname');
            $table->string('category')->nullable();
            $table->integer('useridlog')->nullable();
            $table->integer('activeidnumber')->nullable();
            $table->integer('currentid')->default('0')->nullable();
            $table->integer('callid')->default('0')->nullable();
            $table->string('campus');
            $table->enum('counterstatus', [1, 2, 3])->default(1);
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
        Schema::dropIfExists('counters');
    }
};
