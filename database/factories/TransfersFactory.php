<?php

namespace Database\Factories;

use App\Models\Product\Transfer\Transfers;
use App\Models\Product\Products;
use App\Models\Product\Transfer\StatusTransfer;
use App\Models\PointSale\Branch\Branches;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransfersFactory extends Factory
{
    protected $model = Transfers::class;

    public function definition(): array
    {
        $sucursales = Sucursal::pluck('id')->toArray();
        
        // Asegurarse de que origen y destino sean diferentes
        $origen = $this->faker->randomElement($sucursales);
        $destino = $this->faker->randomElement(array_diff($sucursales, [$origen]));
        
        return [
            'id_sucursal_origen' => $origen,
            'id_sucursal_destino' => $destino,
            'fecha_envio_creacion' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'id_product' => Products::inRandomOrder()->first()?->id ?? 1,
            'cantidad' => $this->faker->numberBetween(1, 100),
            'nombre_referencia' => 'TRANS-' . $this->faker->unique()->numerify('######'),
            'nota_interna' => $this->faker->optional(0.5)->sentence(),
            'id_etquetas' => null,
            'id_status_transfer' => StatusTransfer::inRandomOrder()->first()?->id ?? 1,
            'created_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'updated_at' => now(),
        ];
    }

    /**
     * Transferencia pendiente
     */
    public function pending(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'id_status_transfer' => 1,
            ];
        });
    }

    /**
     * Transferencia en tránsito
     */
    public function inTransit(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'id_status_transfer' => 2,
            ];
        });
    }

    /**
     * Transferencia completada
     */
    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'id_status_transfer' => 3,
            ];
        });
    }
}
