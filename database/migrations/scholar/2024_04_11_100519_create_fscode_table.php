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
        Schema::create('fscode', function (Blueprint $table) {
            $table->id();
            $table->string('fndsource_name')->nullable();
            $table->timestamps();
        });

        DB::table('fscode')->insert([
            ['fndsource_name' => 'INTERNAL', 'created_at' => now(), 'updated_at' => now()],
            ['fndsource_name' => 'EXTERNAL', 'created_at' => now(), 'updated_at' => now()],
            ['fndsource_name' => 'NONE', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fscode');
    }
};
