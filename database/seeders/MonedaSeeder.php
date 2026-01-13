<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Money\Moneda;

class MonedaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $monedas = [
            [
                'codigo' => 'HNL',
                'nombre' => 'Lempira hondureno',
                'simbolo' => 'L',
                'tipo_cambio' => 1.00,
            ],
            [
                'codigo' => 'USD',
                'nombre' => 'Dolar estadounidense',
                'simbolo' => '$',
                'tipo_cambio' => 24.50,
            ],
        ];

        foreach ($monedas as $moneda) {
            Moneda::updateOrCreate(
                ['codigo' => $moneda['codigo']],
                $moneda
            );
        }

        $this->command->info('Monedas creadas exitosamente.');
    }
}
