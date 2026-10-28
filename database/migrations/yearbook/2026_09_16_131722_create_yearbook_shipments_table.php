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
        Schema::create('yearbook_shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('yearbook_id')->nullable()->constrained('yearbooks')->onDelete('cascade');
            $table->string('supplier_name');
            $table->string('tracking_number')->nullable();
            $table->integer('quantity_sent');
            $table->integer('quantity_received')->default(0);

            // Status lifecycle: pending -> released_by_supplier -> received_by_office -> completed / disputed
            $table->enum('status', [
                'pending',
                'released_by_supplier',
                'received_by_office',
                'disputed'
            ])->default('pending');

            $table->timestamp('released_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->text('notes')->nullable();
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
        Schema::dropIfExists('yearbook_shipments');
    }
};
