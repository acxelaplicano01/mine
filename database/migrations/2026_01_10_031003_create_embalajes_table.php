<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmbalajesTable extends Migration
{
    public function up()
    {
        Schema::create('embalajes', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_embalaje')->nullable();
            $table->string('dimensiones')->nullable();
            $table->string('material')->nullable();
            $table->decimal('peso_maximo', 16, 4)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('embalajes');
    }
}
