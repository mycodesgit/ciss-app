<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('enrollmentmode', function (Blueprint $table) {
            $table->id();
            $table->string('statusenroll')->default('Off'); 
            $table->string('type')->default('Undergrad'); 
            $table->string('campus'); 
            $table->string('postedBy'); 
            $table->timestamps();
        });

        $campuses = ['MC', 'VC', 'SCC', 'HC', 'MP', 'IC', 'CA', 'CC', 'SC', 'HinC'];

        foreach ($campuses as $campus) {
            DB::table('enrollmentmode')->insert([
                'statusenroll' => 'Off',
                'type' => 'Undergrad',
                'campus' => $campus,
                'postedBy' => 'system',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('enrollmentmode');
    }
};
