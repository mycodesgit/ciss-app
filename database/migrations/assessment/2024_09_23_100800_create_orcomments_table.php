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
        Schema::create('orcomments', function (Blueprint $table) {
            $table->id();
            $table->integer('studpayID')->nullable()->index();
            $table->string('orno')->nullable()->index();
            $table->string('studID')->nullable()->index();
            $table->string('semester')->nullable();
            $table->string('schlyear')->nullable();
            $table->string('campus')->nullable();
            $table->string('datepaid')->nullable();
            $table->text('comments')->nullable();
            $table->integer('postedBy')->nullable();
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
        Schema::dropIfExists('orcomments');
    }
};
