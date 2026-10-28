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
        Schema::create('student_status', function (Blueprint $table) {
            $table->id();
            $table->string('studentStatName')->nullable();
            $table->timestamps();
        });

        DB::table('student_status')->insert([
            ['studentStatName' => 'REGULAR', 'created_at' => now(), 'updated_at' => now()],
            ['studentStatName' => 'IRREGULAR', 'created_at' => now(), 'updated_at' => now()],
            ['studentStatName' => 'UNDER PRO', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_status');
    }
};
