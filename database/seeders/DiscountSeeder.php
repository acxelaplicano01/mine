<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DiscountSeeder extends Seeder
{
    public function run()
    {
        DB::table('discounts')->insert([
            [
                'code_discount' => 'BGT874C37PEC',
                'description' => 'AAAA (Geométrico)',
                'valor_discount' => 10.00,
                'discount_value_type' => 'percentage',
                'amount' => null,
                'usage_limit' => 100,
                'used_count' => 0,
                'id_market' => 1,
                'id_type_discount' => 1, // Descuento en productos
                'id_method_discount' => 1, // Código de descuento
                'id_colection' => null,
                'id_product' => 1,
                'una_vez_por_pedido' => false,
                'id_elegibility_discount' => 1,
                'id_requirement_discount' => 1,
                'number_usage_max' => 100,
                'usage_per_customer' => 1,
                'fecha_inicio_uso' => now(),
                'hora_inicio_uso' => now(),
                'fecha_fin_uso' => now()->addMonths(3),
                'hora_fin_uso' => now()->addMonths(3),
                'accesible_channel_sales' => json_encode(['online', 'pos']),
                'id_status_discount' => 1, // Activo
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code_discount' => null,
                'description' => '5% de descuento automático para todos',
                'valor_discount' => 5.00,
                'discount_value_type' => 'percentage',
                'amount' => null,
                'usage_limit' => null,
                'used_count' => 0,
                'id_market' => 1,
                'id_type_discount' => 3, // Descuento en el pedido
                'id_method_discount' => 2, // Descuento automático
                'id_colection' => null,
                'id_product' => 2,
                'una_vez_por_pedido' => false,
                'id_elegibility_discount' => 1,
                'id_requirement_discount' => 1,
                'number_usage_max' => null,
                'usage_per_customer' => null,
                'fecha_inicio_uso' => now(),
                'hora_inicio_uso' => now(),
                'fecha_fin_uso' => now()->addMonths(6),
                'hora_fin_uso' => now()->addMonths(6),
                'accesible_channel_sales' => json_encode(['online', 'pos']),
                'id_status_discount' => 1, // Activo
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code_discount' => null,
                'description' => '10% de descuento por compra mayor a 1000 L',
                'valor_discount' => 10.00,
                'discount_value_type' => 'percentage',
                'amount' => 1000.00, // Monto mínimo requerido
                'usage_limit' => null,
                'used_count' => 0,
                'id_market' => 1,
                'id_type_discount' => 3, // Descuento en el pedido
                'id_method_discount' => 2, // Descuento automático
                'id_colection' => null,
                'id_product' => 1,
                'una_vez_por_pedido' => false,
                'id_elegibility_discount' => 1,
                'id_requirement_discount' => 3,
                'number_usage_max' => null,
                'usage_per_customer' => null,
                'fecha_inicio_uso' => now(),
                'hora_inicio_uso' => now(),
                'fecha_fin_uso' => now()->addMonths(6),
                'hora_fin_uso' => now()->addMonths(6),
                'accesible_channel_sales' => json_encode(['online', 'pos']),
                'id_status_discount' => 1, // Activo
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code_discount' => null,
                'description' => '50 L de descuento fijo automático',
                'valor_discount' => 50.00,
                'discount_value_type' => 'fixed_amount',
                'amount' => null,
                'usage_limit' => null,
                'used_count' => 0,
                'id_market' => 1,
                'id_type_discount' => 3, // Descuento en el pedido
                'id_method_discount' => 2, // Descuento automático
                'id_colection' => null,
                'id_product' => 2,
                'una_vez_por_pedido' => false,
                'id_elegibility_discount' => 1,
                'id_requirement_discount' => 1,
                'number_usage_max' => null,
                'usage_per_customer' => null,
                'fecha_inicio_uso' => now(),
                'hora_inicio_uso' => now(),
                'fecha_fin_uso' => now()->addMonths(6),
                'hora_fin_uso' => now()->addMonths(6),
                'accesible_channel_sales' => json_encode(['online', 'pos']),
                'id_status_discount' => 1, // Activo
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
