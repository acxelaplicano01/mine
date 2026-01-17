<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('discounts', function (Blueprint $table) {
            // Verificar si las columnas ya existen antes de agregarlas
            if (!Schema::hasColumn('discounts', 'minimum_purchase_amount')) {
                $table->decimal('minimum_purchase_amount', 16, 2)->nullable()->after('amount');
            }
            if (!Schema::hasColumn('discounts', 'minimum_quantity')) {
                $table->integer('minimum_quantity')->nullable()->after('minimum_purchase_amount');
            }
            if (!Schema::hasColumn('discounts', 'selected_customers')) {
                $table->json('selected_customers')->nullable();
            }
            if (!Schema::hasColumn('discounts', 'selected_segments')) {
                $table->json('selected_segments')->nullable()->after('selected_customers');
            }
            if (!Schema::hasColumn('discounts', 'selected_products')) {
                $table->json('selected_products')->nullable()->after('id_product');
            }
            if (!Schema::hasColumn('discounts', 'selected_collections')) {
                $table->json('selected_collections')->nullable()->after('selected_products');
            }
            if (!Schema::hasColumn('discounts', 'applies_to')) {
                $table->string('applies_to')->nullable()->after('selected_collections');
            }
            if (!Schema::hasColumn('discounts', 'combine_with_product')) {
                $table->boolean('combine_with_product')->default(false)->after('una_vez_por_pedido');
            }
            if (!Schema::hasColumn('discounts', 'combine_with_order')) {
                $table->boolean('combine_with_order')->default(false)->after('combine_with_product');
            }
            if (!Schema::hasColumn('discounts', 'combine_with_shipping')) {
                $table->boolean('combine_with_shipping')->default(false)->after('combine_with_order');
            }
            if (!Schema::hasColumn('discounts', 'limit_per_customer')) {
                $table->boolean('limit_per_customer')->default(false)->after('usage_per_customer');
            }
            if (!Schema::hasColumn('discounts', 'channel_promotion')) {
                $table->boolean('channel_promotion')->default(false)->after('accesible_channel_sales');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('discounts', function (Blueprint $table) {
            $columnsToDropIfExist = [
                'minimum_purchase_amount',
                'minimum_quantity',
                'selected_customers',
                'selected_segments',
                'selected_products',
                'selected_collections',
                'applies_to',
                'combine_with_product',
                'combine_with_order',
                'combine_with_shipping',
                'limit_per_customer',
                'channel_promotion',
            ];
            
            foreach ($columnsToDropIfExist as $column) {
                if (Schema::hasColumn('discounts', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
