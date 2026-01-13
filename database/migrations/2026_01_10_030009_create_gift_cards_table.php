<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGiftCardsTable extends Migration
{
    public function up()
    {
        Schema::create('gift_cards', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->decimal('valor_inicial', 16, 4)->nullable();
            $table->dateTime('expiry_date')->nullable();
            $table->unsignedBigInteger('id_customer')->nullable();
            $table->unsignedBigInteger('id_status_gift_card')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('gift_cards');
    }
}
