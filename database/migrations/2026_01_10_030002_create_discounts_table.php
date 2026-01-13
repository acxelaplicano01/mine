<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiscountsTable extends Migration
{
    public function up()
    {
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('code_discount')->nullable();
            $table->text('description')->nullable();
            $table->decimal('valor_discount', 16, 4)->nullable();
            $table->decimal('amount', 16, 4)->nullable();
            $table->integer('usage_limit')->nullable();
            $table->integer('used_count')->default(0);
            $table->unsignedBigInteger('id_market')->nullable();
            $table->unsignedBigInteger('id_type_discount')->nullable();
            $table->unsignedBigInteger('id_method_discount')->nullable();
            $table->unsignedBigInteger('id_colection')->nullable();
            $table->unsignedBigInteger('id_product')->nullable();
            $table->boolean('una_vez_por_pedido')->default(false);
            $table->unsignedBigInteger('id_elegibility_discount')->nullable();
            $table->unsignedBigInteger('id_requirement_discount')->nullable();
            $table->integer('number_usage_max')->nullable();
            $table->integer('usage_per_customer')->nullable();
            $table->dateTime('fecha_inicio_uso')->nullable();
            $table->dateTime('hora_inicio_uso')->nullable();
            $table->dateTime('fecha_fin_uso')->nullable();
            $table->dateTime('hora_fin_uso')->nullable();
            $table->json('accesible_channel_sales')->nullable();
            $table->unsignedBigInteger('id_status_discount')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('discounts');
    }
}
