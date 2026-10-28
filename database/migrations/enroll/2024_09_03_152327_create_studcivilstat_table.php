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
        Schema::create('studcivilstat', function (Blueprint $table) {
            $table->id();
            $table->string('cvlstat_name')->nullable();
            $table->timestamps();
        });

        DB::table('studcivilstat')->insert([
            ['cvlstat_name' => 'Single', 'created_at' => now(), 'updated_at' => now()],
            ['cvlstat_name' => 'Married', 'created_at' => now(), 'updated_at' => now()],
            ['cvlstat_name' => 'Divorced', 'created_at' => now(), 'updated_at' => now()],
            ['cvlstat_name' => 'Widowed', 'created_at' => now(), 'updated_at' => now()],
            ['cvlstat_name' => 'Separated', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('studcivilstat');
    }
};
