<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product\OrderPurchase\ConditionPay;

class ConditionPaySeeder extends Seeder
{
    public function run()
    {
        $conditions = [
            [
                'nombre_condicion' => 'A pagar al momento de la recepción',
                'descripcion_condicion' => 'El pago se realizará cuando se envíe la factura',
                'dias_vencimiento' => 0,
                'type' => 'reception',
                'is_active' => true,
            ],
            [
                'nombre_condicion' => 'A pagar al momento del envío',
                'descripcion_condicion' => 'El pago se realizará cuando el pedido esté preparado',
                'dias_vencimiento' => 0,
                'type' => 'envio',
                'is_active' => true,
            ],
            [
                'nombre_condicion' => 'En un plazo de 7 días',
                'descripcion_condicion' => 'El pago vence 7 días después de la emisión',
                'dias_vencimiento' => 7,
                'type' => 'neto_dias',
                'is_active' => true,
            ],
            [
                'nombre_condicion' => 'En un plazo de 15 días',
                'descripcion_condicion' => 'El pago vence 15 días después de la emisión',
                'dias_vencimiento' => 15,
                'type' => 'neto_dias',
                'is_active' => true,
            ],
            [
                'nombre_condicion' => 'En un plazo de 30 días',
                'descripcion_condicion' => 'El pago vence 30 días después de la emisión',
                'dias_vencimiento' => 30,
                'type' => 'neto_dias',
                'is_active' => true,
            ],
            [
                'nombre_condicion' => 'En un plazo de 60 días',
                'descripcion_condicion' => 'El pago vence 60 días después de la emisión',
                'dias_vencimiento' => 60,
                'type' => 'neto_dias',
                'is_active' => true,
            ],
            [
                'nombre_condicion' => 'En un plazo de 90 días',
                'descripcion_condicion' => 'El pago vence 90 días después de la emisión',
                'dias_vencimiento' => 90,
                'type' => 'neto_dias',
                'is_active' => true,
            ],
            [
                'nombre_condicion' => 'Fecha fija',
                'descripcion_condicion' => 'El pago vence en una fecha específica determinada por el usuario',
                'dias_vencimiento' => 0,
                'type' => 'fecha_fija',
                'is_active' => true,
            ],
        ];

        foreach ($conditions as $condition) {
            ConditionPay::create($condition);
        }
    }
}
