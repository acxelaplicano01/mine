<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEnviosTable extends Migration
{
    public function up()
    {
        Schema::create('envios', function (Blueprint $table) {
            $table->id();
            $table->decimal('costo_envio', 16, 4)->nullable();
            $table->unsignedBigInteger('id_embalaje')->nullable();
            $table->decimal('peso', 16, 4)->nullable();
            $table->string('unidad_peso')->nullable();
            $table->boolean('producto_fisico')->default(true);
            $table->unsignedBigInteger('info_aduana_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('envios');
    }
}
