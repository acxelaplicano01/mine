<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('discounts', function (Blueprint $table) {
            $table->unsignedBigInteger('id_customer')->nullable()->after('id_product');
        });
    }

    public function down()
    {
        Schema::table('discounts', function (Blueprint $table) {
            $table->dropColumn('id_customer');
        });
    }
};
