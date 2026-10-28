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
        Schema::create('ad_applicant_docs', function (Blueprint $table) {
            $table->id();
            $table->integer('app_id')->nullable();
            $table->string('camp')->nullable();
            $table->integer('admission_id')->nullable();
            $table->string('r_card')->nullable();
            $table->string('g_moral')->nullable();
            $table->string('t_record')->nullable();
            $table->string('b_cert')->nullable();
            $table->string('h_dismissal')->nullable();
            $table->string('m_cert')->nullable();
            $table->string('qstion1')->nullable();
            $table->string('qstion2')->nullable();
            $table->string('typefileproofupload')->nullable();
            $table->string('studiddoc_image')->nullable();
            $table->string('proofdoc_image')->nullable();
            $table->string('grade12File')->nullable();
            $table->string('shsFile')->nullable();
            $table->string('transfereeFile')->nullable();
            $table->string('alsFile')->nullable();
            $table->string('lifelongFile')->nullable();
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
        Schema::dropIfExists('ad_applicant_docs');
    }
};
