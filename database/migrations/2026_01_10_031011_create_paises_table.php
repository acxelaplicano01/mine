<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaisesTable extends Migration
{
    public function up()
    {
        Schema::create('paises', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->nullable();
            $table->string('codigo_iso2')->nullable();
            $table->string('codigo_iso3')->nullable();
            $table->string('codigo_numerico')->nullable();
            $table->string('prefijo_telefono')->nullable();
            $table->unsignedBigInteger('id_moneda')->nullable();
            $table->string('region')->nullable();
            $table->string('subregion')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('paises');
    }
}
