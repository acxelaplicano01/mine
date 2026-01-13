<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Market\Markets;
use App\Models\Money\Moneda;

class MarketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener monedas
        $monedaHNL = Moneda::where('codigo', 'HNL')->first();
        $monedaUSD = Moneda::where('codigo', 'USD')->first();

        $markets = [
            [
                'name' => 'Honduras',
                'description' => 'Mercado de Honduras',
                'domain' => 'hn.tutienda.com',
                'id_moneda' => $monedaHNL ? $monedaHNL->id : null,
            ],
            [
                'name' => 'Estados Unidos',
                'description' => 'Mercado de Estados Unidos',
                'domain' => 'us.tutienda.com',
                'id_moneda' => $monedaUSD ? $monedaUSD->id : null,
            ],
            [
                'name' => 'Internacional',
                'description' => 'Mercado Internacional',
                'domain' => 'global.tutienda.com',
                'id_moneda' => $monedaUSD ? $monedaUSD->id : null,
            ],
        ];

        foreach ($markets as $market) {
            Markets::updateOrCreate(
                ['name' => $market['name']],
                $market
            );
        }

        $this->command->info('Markets creados exitosamente.');
    }
}
