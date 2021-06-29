<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string("product_code");
            $table->string("product_name");
            $table->double("mrp")->nullable();
            $table->double("unit_cost");
            $table->double("discount")->nullable();
            $table->double("sale_price");
            $table->double("gst");
            $table->integer("qty");
            $table->date('mfg')->nullable();
            $table->date('expiry')->nullable();
            $table->unsignedBigInteger('purchase_id');
            $table->timestamps();

            $table->foreign('purchase_id')->references('id')->on('purchases');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('items');
    }
}
