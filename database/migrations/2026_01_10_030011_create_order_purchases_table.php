<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderPurchasesTable extends Migration
{
    public function up()
    {
        Schema::create('order_purchases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_distribuidor')->nullable();
            $table->unsignedBigInteger('id_sucursal_destino')->nullable();
            $table->unsignedBigInteger('id_condiciones_pago')->nullable();
            $table->unsignedBigInteger('id_moneda_del_distribuidor')->nullable();
            $table->dateTime('fecha_llegada_estimada')->nullable();
            $table->unsignedBigInteger('id_empresa_trasnportista')->nullable();
            $table->string('numero_guia')->nullable();
            $table->unsignedBigInteger('id_product')->nullable();
            $table->string('numero_referencia')->nullable();
            $table->json('nota_al_distribuidor')->nullable();
            $table->string('id_etquetas')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_purchases');
    }
}
