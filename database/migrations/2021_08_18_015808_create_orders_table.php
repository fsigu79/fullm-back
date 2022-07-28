<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->enum('status', ['R', 'C', 'F', 'D'])->default('R');
            $table->date('date')->nullable();
            $table->bigInteger('provider_id')->unsigned();
            $table->foreign('provider_id')->references('id')->on('providers');
            $table->bigInteger('client_id')->unsigned();
            $table->foreign('client_id')->references('id')->on('clients');
            $table->string('marking', 255)->nullable();
            $table->bigInteger('agency_id')->unsigned();
            $table->foreign('agency_id')->references('id')->on('agencies');
            $table->bigInteger('cold_room_id')->unsigned();
            $table->foreign('cold_room_id')->references('id')->on('cold_rooms');
            $table->date('delivery_date');
            $table->date('flight_date');
            $table->string('awb', 255)->nullable();
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('woo_order', 255)->nullable();
            $table->string('unosof_order', 255)->nullable();
            $table->text('observation')->nullable();
            $table->double('total_stems', 10, 2);
            $table->double('total', 10, 2);
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
        Schema::dropIfExists('orders');
    }
}
