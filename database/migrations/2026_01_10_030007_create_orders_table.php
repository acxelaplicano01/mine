<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('id_customer')->nullable();
            $table->decimal('subtotal_price', 16, 4)->nullable();
            $table->decimal('total_price', 16, 4)->nullable();
            $table->unsignedBigInteger('id_market')->nullable();
            $table->unsignedBigInteger('id_discount')->nullable();
            $table->unsignedBigInteger('id_envio')->nullable();
            $table->unsignedBigInteger('id_impuesto')->nullable();
            $table->unsignedBigInteger('id_moneda')->nullable();
            $table->text('note')->nullable();
            $table->unsignedBigInteger('id_etiqueta')->nullable();
            $table->unsignedBigInteger('id_status_prepared_order')->nullable();
            $table->unsignedBigInteger('id_status_order')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
