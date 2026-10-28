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
        Schema::create('studgenderstat', function (Blueprint $table) {
            $table->id();
            $table->string('genderstat_name')->nullable();
            $table->timestamps();
        });

        DB::table('studgenderstat')->insert([
            ['genderstat_name' => 'Male', 'created_at' => now(), 'updated_at' => now()],
            ['genderstat_name' => 'Female', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('studgenderstat');
    }
};
