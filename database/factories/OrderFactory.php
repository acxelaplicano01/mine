<?php

namespace Database\Factories;

use App\Models\Order\Orders;
use App\Models\Customer\Customers;
use App\Models\User;
use App\Models\Market\Markets;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Orders::class;

    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 50, 5000);
        $tax = $subtotal * 0.16; // 16% de impuesto
        $total = $subtotal + $tax;

        return [
            'user_id' => User::inRandomOrder()->first()?->id ?? 1,
            'id_customer' => Customers::inRandomOrder()->first()?->id ?? null,
            'subtotal_price' => $subtotal,
            'total_price' => $total,
            'id_market' => Markets::inRandomOrder()->first()?->id ?? null,
            'id_discount' => null,
            'id_envio' => null,
            'id_impuesto' => null,
            'id_moneda' => null,
            'id_condiciones_pago' => null,
            'fecha_emision' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'fecha_vencimiento' => $this->faker->dateTimeBetween('now', '+30 days'),
            'note' => $this->faker->optional(0.3)->sentence(),
            'id_etiqueta' => null,
            'id_status_prepared_order' => $this->faker->numberBetween(1, 5),
            'id_status_order' => $this->faker->numberBetween(1, 6),
            'created_at' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'updated_at' => now(),
        ];
    }

    /**
     * Factory para crear órdenes sin eventos
     * (útil para evitar la actualización de inventario y segmentos)
     */
    public function withoutEvents(): static
    {
        return $this->state(function (array $attributes) {
            return $attributes;
        });
    }
}
