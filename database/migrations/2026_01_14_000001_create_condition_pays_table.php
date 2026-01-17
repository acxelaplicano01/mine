<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConditionPaysTable extends Migration
{
    public function up()
    {
        Schema::create('condition_pay', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_condicion');
            $table->text('descripcion_condicion')->nullable();
            $table->integer('dias_vencimiento')->default(0); // Días para calcular vencimiento
            $table->enum('type', ['reception', 'envio', 'neto_dias', 'fecha_fija'])->default('reception');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('condition_pay');
    }
}
