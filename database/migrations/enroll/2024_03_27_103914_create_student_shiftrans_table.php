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
        Schema::create('student_shiftrans', function (Blueprint $table) {
            $table->id();
            $table->string('studentShiftTransDesc')->nullable();
            $table->timestamps();
        });

        DB::table('student_shiftrans')->insert([
            ['studentShiftTransDesc' => 'TRANSFEREE', 'created_at' => now(), 'updated_at' => now()],
            ['studentShiftTransDesc' => 'SHIFTEE', 'created_at' => now(), 'updated_at' => now()],
            ['studentShiftTransDesc' => 'NONE', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_shiftrans');
    }
};
