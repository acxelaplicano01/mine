<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMarketsTable extends Migration
{
    public function up()
    {
        Schema::create('markets', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->string('domain')->nullable();
            $table->unsignedBigInteger('id_moneda')->nullable();
            $table->unsignedBigInteger('id_catalogo')->nullable();
            $table->unsignedBigInteger('id_pais')->nullable();
            $table->unsignedBigInteger('id_tienda_online')->nullable();
            $table->unsignedBigInteger('id_status_market')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('markets');
    }
}
