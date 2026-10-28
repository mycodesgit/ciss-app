<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        Schema::create('studentlevel', function (Blueprint $table) {
            $table->id();
            $table->string('studLevel')->nullable();
            $table->string('subletter')->nullable();
            $table->timestamps();
        });

        DB::table('studentlevel')->insert([
            ['studLevel' => 'PRE-SCHOOL', 'created_at' => now(), 'updated_at' => now()],
            ['studLevel' => 'ELEMENTARY', 'created_at' => now(), 'updated_at' => now()],
            ['studLevel' => 'SECONDARY HIGH SCHOOL', 'created_at' => now(), 'updated_at' => now()],
            ['studLevel' => 'SENIOR HIGH SCHOOL', 'created_at' => now(), 'updated_at' => now()],
            ['studLevel' => 'VOCATIONAL-TECHNICAL', 'created_at' => now(), 'updated_at' => now()],
            ['studLevel' => 'PRE-BACCALAUREATE DIPLOMA, CERTIFICATE OR ASSOCIATE', 'created_at' => now(), 'updated_at' => now()],
            ['studLevel' => 'BACCALAUREATE DEGREE (INCLUDING E.G. DVM,DDM,DOpt)', 'created_at' => now(), 'updated_at' => now()],
            ['studLevel' => 'POST-BACCALAUREATE CERTIFICATE OR DIPLOMA PROGRAM', 'created_at' => now(), 'updated_at' => now()],
            ['studLevel' => 'MASTERS DEGREE', 'created_at' => now(), 'updated_at' => now()],
            ['studLevel' => 'DOCTORAL DEGREE', 'created_at' => now(), 'updated_at' => now()]
        ]);


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('studentlevel');
    }
};
