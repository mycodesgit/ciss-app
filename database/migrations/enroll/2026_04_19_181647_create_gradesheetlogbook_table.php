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
        Schema::create('gradesheetlogbook', function (Blueprint $table) {
            $table->id();
            $table->string('schlyear')->nullable();
            $table->string('semester')->nullable();
            $table->string('campus')->nullable();
            $table->string('collegeabbr')->nullable();
            $table->string('facultyid')->nullable(); 
            $table->string('subjectid')->nullable(); 
            $table->string('timebeingsubmitted')->nullable(); 
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
        Schema::dropIfExists('gradesheetlogbook');
    }
};
