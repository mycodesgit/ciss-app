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
        Schema::create('yearbook_issuances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('yearbook_id');
            $table->string('student_id');
            $table->timestamp('issued_at')->nullable();
            $table->string('issued_by')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            // Foreign Key Constraint
            $table->foreign('yearbook_id')
                  ->references('id')
                  ->on('yearbooks')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yearbook_issuances');
    }
};
