<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCatalogsTable extends Migration
{
    public function up()
    {
        Schema::create('catalogs', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 16, 4)->nullable();
            $table->integer('stock')->nullable();
            $table->unsignedBigInteger('id_moneda')->nullable();
            $table->unsignedBigInteger('id_price_aditional')->nullable();
            $table->unsignedBigInteger('id_product')->nullable();
            $table->unsignedBigInteger('id_market')->nullable();
            $table->unsignedBigInteger('id_status_catalog')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('catalogs');
    }
}
