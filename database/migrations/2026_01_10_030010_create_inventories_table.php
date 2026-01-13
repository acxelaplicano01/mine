<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoriesTable extends Migration
{
    public function up()
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_product')->nullable();
            $table->integer('cantidad_inventario')->default(0);
            $table->boolean('seguimiento_inventario')->default(false);
            $table->string('location')->nullable();
            $table->unsignedBigInteger('id_status_inventory')->nullable();
            $table->integer('umbral_aviso_inventario')->nullable();
            $table->boolean('permitir_vender_sin_inventario')->default(false);
            // `sku` and `barcode` removed from model; do not create columns
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventories');
    }
}
