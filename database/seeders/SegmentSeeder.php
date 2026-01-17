<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer\Segments;
use App\Models\Customer\Customers;
use App\Services\CustomerSegmentService;
use Illuminate\Support\Facades\DB;

class SegmentSeeder extends Seeder
{
    public function run(): void
    {
        // Eliminar segmentos existentes si los hay
        DB::table('customer_segments')->truncate();

        // Obtener IDs de clientes disponibles
        $customerIds = Customers::pluck('id')->toArray();
        
        if (empty($customerIds)) {
            $this->command->warn('⚠ No hay clientes disponibles. Ejecuta CustomerSeeder primero.');
            return;
        }

        // Segmentos automáticos (se gestionan automáticamente por el sistema)
        $automaticSegments = [
            [
                'name' => 'Clientes que han comprado al menos una vez',
                'description' => 'Segmento automático: Clientes que han realizado al menos un pedido en el sistema.',
                'is_automatic' => true,
            ],
            [
                'name' => 'Clientes que han comprado más de una vez',
                'description' => 'Segmento automático: Clientes con múltiples pedidos, indicando fidelidad.',
                'is_automatic' => true,
            ],
            [
                'name' => 'Clientes que no han comprado',
                'description' => 'Segmento automático: Clientes registrados que aún no han realizado ningún pedido.',
                'is_automatic' => true,
            ],
        ];

        // Definición de segmentos manuales con sus clientes asignados
        $segmentsData = [
            [
                'name' => 'VIP',
                'description' => 'Clientes con alto volumen de compras y pedidos frecuentes. Gasto total superior a $5,000.',
                'customers' => array_slice($customerIds, 0, min(5, count($customerIds))), // Primeros 5 clientes
            ],
            [
                'name' => 'Nuevos Clientes',
                'description' => 'Clientes que se registraron en los últimos 30 días y aún no han realizado una compra.',
                'customers' => array_slice($customerIds, 5, min(3, count($customerIds) - 5)), // 3 clientes
            ],
            [
                'name' => 'Compradores Frecuentes',
                'description' => 'Clientes que realizan al menos una compra al mes de forma regular.',
                'customers' => array_slice($customerIds, 0, min(8, count($customerIds))), // 8 clientes
            ],
            [
                'name' => 'Mayoristas',
                'description' => 'Clientes que compran productos al por mayor con pedidos de gran volumen.',
                'customers' => array_slice($customerIds, 2, min(4, count($customerIds) - 2)), // 4 clientes
            ],
            [
                'name' => 'Minoristas',
                'description' => 'Clientes que compran productos para reventa en pequeños negocios o tiendas.',
                'customers' => array_slice($customerIds, 6, min(6, count($customerIds) - 6)), // 6 clientes
            ],
            [
                'name' => 'Clientes Inactivos',
                'description' => 'Clientes que no han realizado compras en los últimos 6 meses.',
                'customers' => array_slice($customerIds, 10, min(2, count($customerIds) - 10)), // 2 clientes
            ],
            [
                'name' => 'Primera Compra',
                'description' => 'Clientes que realizaron su primera compra recientemente y tienen potencial de fidelización.',
                'customers' => array_slice($customerIds, 8, min(4, count($customerIds) - 8)), // 4 clientes
            ],
            [
                'name' => 'Alto Valor',
                'description' => 'Clientes con pedidos promedio superiores a $500 por transacción.',
                'customers' => array_slice($customerIds, 0, min(3, count($customerIds))), // 3 clientes
            ],
            [
                'name' => 'Preferencial',
                'description' => 'Clientes con relación comercial especial que reciben atención personalizada.',
                'customers' => array_slice($customerIds, 1, min(5, count($customerIds) - 1)), // 5 clientes
            ],
            [
                'name' => 'Corporativos',
                'description' => 'Empresas y organizaciones con cuentas corporativas para compras a nombre de la empresa.',
                'customers' => array_slice($customerIds, 3, min(3, count($customerIds) - 3)), // 3 clientes
            ],
            [
                'name' => 'Temporada Baja',
                'description' => 'Clientes que compran principalmente durante promociones o temporadas específicas.',
                'customers' => array_slice($customerIds, 7, min(4, count($customerIds) - 7)), // 4 clientes
            ],
            [
                'name' => 'Abandono de Carrito',
                'description' => 'Clientes que agregaron productos al carrito pero no completaron la compra.',
                'customers' => array_slice($customerIds, 9, min(3, count($customerIds) - 9)), // 3 clientes
            ],
        ];

        // Crear segmentos automáticos
        foreach ($automaticSegments as $autoSegment) {
            Segments::create([
                'name' => $autoSegment['name'],
                'description' => $autoSegment['description'],
                'id_customer' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->command->info("✓ Segmento automático creado: {$autoSegment['name']}");
        }

        // Crear segmentos manuales con sus clientes
        $totalSegments = 0;
        $totalAssignments = 0;

        foreach ($segmentsData as $segmentData) {
            $customers = $segmentData['customers'];
            
            // Si el segmento tiene clientes asignados, crear un registro por cada cliente
            if (!empty($customers)) {
                foreach ($customers as $customerId) {
                    Segments::create([
                        'name' => $segmentData['name'],
                        'description' => $segmentData['description'],
                        'id_customer' => $customerId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $totalAssignments++;
                }
                $totalSegments++;
            } else {
                // Si no hay clientes, crear el segmento sin asignación
                Segments::create([
                    'name' => $segmentData['name'],
                    'description' => $segmentData['description'],
                    'id_customer' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $totalSegments++;
            }
        }

        $this->command->info("✓ {$totalSegments} segmentos manuales creados con {$totalAssignments} asignaciones.");
        
        // Sincronizar segmentos automáticos usando el servicio
        $this->command->info('Sincronizando segmentos automáticos...');
        CustomerSegmentService::syncAllAutomaticSegments();
        $this->command->info('✓ Segmentos automáticos sincronizados.');
    }
}
