<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->json('multimedia')->nullable();
            $table->string('sku')->nullable();
            $table->string('barcode')->nullable();
            $table->unsignedBigInteger('id_category')->nullable();
            $table->decimal('price_comparacion', 16, 4)->nullable();
            $table->decimal('price_unitario', 16, 4)->nullable();
            $table->boolean('cobrar_impuestos')->default(false);
            $table->decimal('costo', 16, 4)->nullable();
            $table->decimal('beneficio', 16, 4)->nullable();
            $table->decimal('margen_beneficio', 16, 4)->nullable();
            $table->unsignedBigInteger('id_inventory')->nullable();
            $table->unsignedBigInteger('id_envio')->nullable();
            $table->json('id_variants')->nullable();
            $table->unsignedBigInteger('id_type_product')->nullable();
            $table->unsignedBigInteger('id_distributor')->nullable();
            $table->unsignedBigInteger('id_collection')->nullable();
            $table->unsignedBigInteger('id_etiquetas')->nullable();
            $table->unsignedBigInteger('id_organization_product')->nullable();
            $table->unsignedBigInteger('id_status_product')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
}
