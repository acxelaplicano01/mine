<?php

namespace Database\Factories;

use App\Models\Product\OrderPurchase\OrderPurchases;
use App\Models\Distribuidor\Distribuidores;
use App\Models\Product\Products;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderPurchasesFactory extends Factory
{
    protected $model = OrderPurchases::class;

    public function definition(): array
    {
        return [
            'id_distribuidor' => Distribuidores::inRandomOrder()->first()?->id ?? null,
            'id_sucursal_destino' => null,
            'id_condiciones_pago' => null,
            'id_moneda_del_distribuidor' => null,
            'fecha_llegada_estimada' => $this->faker->dateTimeBetween('now', '+60 days'),
            'id_empresa_trasnportista' => null,
            'numero_guia' => $this->faker->optional(0.6)->numerify('GUIA-####-####'),
            'id_product' => Products::inRandomOrder()->first()?->id ?? 1,
            'numero_referencia' => 'ORD-' . $this->faker->unique()->numerify('######'),
            'nota_al_distribuidor' => $this->faker->optional(0.4)->sentence(),
            'id_etquetas' => null,
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => now(),
        ];
    }

    /**
     * Orden pendiente de recibir
     */
    public function pending(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'fecha_llegada_estimada' => $this->faker->dateTimeBetween('now', '+30 days'),
            ];
        });
    }

    /**
     * Orden ya recibida
     */
    public function received(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'fecha_llegada_estimada' => $this->faker->dateTimeBetween('-30 days', 'now'),
            ];
        });
    }

    /**
     * Orden con número de guía
     */
    public function withTracking(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'numero_guia' => $this->faker->numerify('TRACK-####-####-####'),
            ];
        });
    }
}
