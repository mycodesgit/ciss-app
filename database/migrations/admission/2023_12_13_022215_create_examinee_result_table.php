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
        Schema::create('ad_examinee_result', function (Blueprint $table) {
            $table->id();
            $table->integer('app_id')->nullable();
            $table->string('camp')->nullable();
            $table->integer('admission_id');
            $table->integer('raw_score')->length(5)->nullable();
            $table->string('percentile')->length(50)->nullable();
            $table->enum('qualifier', ['1', '2'])->nullable();
            $table->dateTime('rawscoredate')->nullable();
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
        Schema::dropIfExists('ad_examinee_result');
    }
};
