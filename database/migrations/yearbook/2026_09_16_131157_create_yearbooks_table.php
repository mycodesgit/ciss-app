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
        Schema::create('yearbooks', function (Blueprint $table) {
            $table->id();
            $table->string('school_year');
            $table->string('edition_title');
            $table->integer('total_ordered')->default(0);
            $table->integer('total_received')->default(0);
            $table->decimal('unit_cost', 8, 2)->nullable();
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
        Schema::dropIfExists('yearbooks');
    }
};
