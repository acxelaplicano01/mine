<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('order_discount', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('discount_id');
            $table->string('discount_code')->nullable();
            $table->decimal('discount_amount', 16, 4)->default(0);
            $table->string('discount_type')->nullable(); // 'manual', 'automatic', 'custom'
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('discount_id')->references('id')->on('discounts')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_discount');
    }
};
