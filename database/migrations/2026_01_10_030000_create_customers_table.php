<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomersTable extends Migration
{
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('language')->nullable();
            $table->boolean('acepta_mensajes')->default(false);
            $table->boolean('acepta_email')->default(false);
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->decimal('total_spent', 16, 4)->default(0);
            $table->integer('orders_count')->default(0);
            $table->text('address')->nullable();
            $table->unsignedBigInteger('id_info_fiscal')->nullable();
            $table->text('notas')->nullable();
            $table->unsignedBigInteger('id_etiqueta')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('customers');
    }
}
