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
        Schema::create('scheduleclass', function (Blueprint $table) {
            $table->id();
            $table->string('schedday')->nullable();
            $table->string('start_time')->nullable();
            $table->string('end_time')->nullable();
            $table->string('progcodename')->nullable();
            $table->string('progcodesection')->nullable();
            $table->string('schlyear')->nullable();
            $table->string('semester')->nullable();
            $table->string('postedBy')->nullable();
            $table->string('campus')->nullable();
            $table->string('subject_id')->nullable();
            $table->string('faculty_id')->nullable();
            $table->string('room_id')->nullable();
            $table->string('remarks')->nullable();
            $table->string('is_merged')->nullable();
            $table->string('merge_sections')->nullable();
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
        Schema::dropIfExists('scheduleclass');
    }
};
