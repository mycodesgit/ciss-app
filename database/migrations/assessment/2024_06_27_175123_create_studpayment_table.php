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
        Schema::create('studpayment', function (Blueprint $table) {
            $table->id();
            $table->string('orno')->nullable();
            $table->string('studID')->nullable()->index();
            $table->string('semester')->nullable();
            $table->string('schlyear')->nullable();
            $table->string('campus')->nullable();
            $table->string('fund')->nullable();
            $table->string('account')->nullable();
            $table->string('datepaid')->nullable();
            $table->string('amountpaid')->nullable();
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
        Schema::dropIfExists('studpayment');
    }
};
