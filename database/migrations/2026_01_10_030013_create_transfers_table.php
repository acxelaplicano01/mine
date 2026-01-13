<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransfersTable extends Migration
{
    public function up()
    {
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_sucursal_origen')->nullable();
            $table->unsignedBigInteger('id_sucursal_destino')->nullable();
            $table->dateTime('fecha_envio_creacion')->nullable();
            $table->unsignedBigInteger('id_product')->nullable();
            $table->integer('cantidad')->default(0);
            $table->string('nombre_referencia')->nullable();
            $table->json('nota_interna')->nullable();
            $table->string('id_etquetas')->nullable();
            $table->unsignedBigInteger('id_status_transfer')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('transfers');
    }
}
