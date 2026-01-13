<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransportistasTable extends Migration
{
    public function up()
    {
        Schema::create('transportistas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_transportista')->nullable();
            $table->string('telefono_transportista')->nullable();
            $table->string('email_transportista')->nullable();
            $table->text('direccion_transportista')->nullable();
            $table->unsignedBigInteger('id_status_transportista')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('transportistas');
    }
}
