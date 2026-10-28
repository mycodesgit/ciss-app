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
        Schema::create('yearlevel', function (Blueprint $table) {
            $table->id();
            $table->string('yearleveldesc')->nullable();
            $table->integer('yearsection')->nullable();
            $table->timestamps();
        });

        DB::table('yearlevel')->insert([
            ['yearleveldesc' => '1ST YEAR STANDING IN LEVELS 40, 50 or 70', 'yearsection' => '1', 'created_at' => now(), 'updated_at' => now()],
            ['yearleveldesc' => '2ND YEAR STANDING IN LEVELS 40,50 or 70', 'yearsection' => '2', 'created_at' => now(), 'updated_at' => now()],
            ['yearleveldesc' => '3RD YEAR STANDING IN LEVELS 40,50 or 70', 'yearsection' => '3', 'created_at' => now(), 'updated_at' => now()],
            ['yearleveldesc' => '4TH YEAR STANDING IN LEVELS 40, 50 or 70', 'yearsection' => '4', 'created_at' => now(), 'updated_at' => now()],
            ['yearleveldesc' => '5TH YEAR STANDING IN LEVELS 40, 50 or 70', 'yearsection' => '5', 'created_at' => now(), 'updated_at' => now()],
            ['yearleveldesc' => '6TH YEAR STANDING IN LEVELS 40, 50 or 70', 'yearsection' => '6', 'created_at' => now(), 'updated_at' => now()],
            ['yearleveldesc' => '7TH YEAR STANDING IN LEVELS 40, 50 or 70', 'yearsection' => '7', 'created_at' => now(), 'updated_at' => now()],
            ['yearleveldesc' => 'STUDLEV IS 05, 10, 20, 30, 80, 90 or 99', 'yearsection' => '8', 'created_at' => now(), 'updated_at' => now()],
            ['yearleveldesc' => 'NOT KNOWN OR NOT INDICATED', 'yearsection' => 'REGULAR', 'created_at' => now(), 'updated_at' => now()],
            ['yearleveldesc' => 'GRADE 10', 'yearsection' => 'NULL', 'created_at' => now(), 'updated_at' => now()],
            ['yearleveldesc' => 'GRADE 11', 'yearsection' => 'NULL', 'created_at' => now(), 'updated_at' => now()],
            ['yearleveldesc' => 'GRADE 12', 'yearsection' => 'NULL', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yearlevel');
    }
};
