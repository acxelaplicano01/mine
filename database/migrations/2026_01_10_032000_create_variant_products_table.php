<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVariantProductsTable extends Migration
{
    public function up()
    {
        Schema::create('variant_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('sku')->nullable();
            $table->string('barcode')->nullable();
            $table->decimal('price', 16, 4)->nullable();
            $table->integer('cantidad_inventario')->default(0);
            $table->integer('cantidad_no_disponible')->default(0);
            $table->decimal('weight', 16, 4)->nullable();
            $table->string('name_variant')->nullable();
            $table->json('valores_variante')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('variant_products');
    }
}
