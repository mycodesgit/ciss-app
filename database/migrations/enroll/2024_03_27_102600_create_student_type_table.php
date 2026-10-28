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
        Schema::create('student_type', function (Blueprint $table) {
            $table->id();
            $table->string('studentTypeName')->nullable();
            $table->timestamps();
        });

        DB::table('student_type')->insert([
            ['studentTypeName' => 'NEW', 'created_at' => now(), 'updated_at' => now()],
            ['studentTypeName' => 'CONTINUING', 'created_at' => now(), 'updated_at' => now()],
            ['studentTypeName' => 'RETURNING', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_type');
    }
};
