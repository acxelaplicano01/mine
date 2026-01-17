<?php

namespace App\Services;

use App\Models\Customer\Segments;
use App\Models\Customer\Customers;
use Illuminate\Support\Facades\DB;

class CustomerSegmentService
{
    /**
     * Segmentos automáticos del sistema
     */
    const SEGMENT_NO_PURCHASES = 'Clientes que no han comprado';
    const SEGMENT_AT_LEAST_ONE = 'Clientes que han comprado al menos una vez';
    const SEGMENT_MULTIPLE = 'Clientes que han comprado más de una vez';

    /**
     * Actualizar los segmentos automáticos de un cliente
     */
    public static function updateCustomerSegments($customerId)
    {
        if (!$customerId) {
            return;
        }

        // Obtener el conteo de órdenes del cliente
        $ordersCount = DB::table('orders')
            ->where('id_customer', $customerId)
            ->count();

        // Eliminar al cliente de todos los segmentos automáticos
        DB::table('customer_segments')
            ->where('id_customer', $customerId)
            ->whereIn('name', [
                self::SEGMENT_NO_PURCHASES,
                self::SEGMENT_AT_LEAST_ONE,
                self::SEGMENT_MULTIPLE,
            ])
            ->delete();

        // Asignar al segmento correspondiente según el número de órdenes
        if ($ordersCount === 0) {
            self::assignToSegment(self::SEGMENT_NO_PURCHASES, $customerId);
        } elseif ($ordersCount === 1) {
            self::assignToSegment(self::SEGMENT_AT_LEAST_ONE, $customerId);
        } else {
            // Si tiene más de una orden, pertenece a ambos segmentos
            self::assignToSegment(self::SEGMENT_AT_LEAST_ONE, $customerId);
            self::assignToSegment(self::SEGMENT_MULTIPLE, $customerId);
        }
    }

    /**
     * Asignar un cliente a un segmento automático
     */
    private static function assignToSegment($segmentName, $customerId)
    {
        // Verificar si ya existe la asignación
        $exists = DB::table('customer_segments')
            ->where('name', $segmentName)
            ->where('id_customer', $customerId)
            ->exists();

        if ($exists) {
            return;
        }

        // Obtener la descripción del segmento
        $description = DB::table('customer_segments')
            ->where('name', $segmentName)
            ->whereNull('id_customer')
            ->value('description');

        if (!$description) {
            $description = "Segmento automático: {$segmentName}";
        }

        // Crear la asignación
        Segments::create([
            'name' => $segmentName,
            'description' => $description,
            'id_customer' => $customerId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Sincronizar todos los segmentos automáticos del sistema
     */
    public static function syncAllAutomaticSegments()
    {
        // Obtener todos los clientes
        $allCustomers = Customers::pluck('id')->toArray();

        // Obtener órdenes por cliente
        $customersWithOrders = DB::table('orders')
            ->select('id_customer', DB::raw('COUNT(*) as orders_count'))
            ->whereNotNull('id_customer')
            ->groupBy('id_customer')
            ->get()
            ->keyBy('id_customer');

        // Limpiar todos los segmentos automáticos
        DB::table('customer_segments')
            ->whereIn('name', [
                self::SEGMENT_NO_PURCHASES,
                self::SEGMENT_AT_LEAST_ONE,
                self::SEGMENT_MULTIPLE,
            ])
            ->whereNotNull('id_customer')
            ->delete();

        // Asignar cada cliente a su segmento correspondiente
        foreach ($allCustomers as $customerId) {
            $ordersCount = $customersWithOrders->get($customerId)->orders_count ?? 0;

            if ($ordersCount === 0) {
                self::assignToSegment(self::SEGMENT_NO_PURCHASES, $customerId);
            } elseif ($ordersCount === 1) {
                self::assignToSegment(self::SEGMENT_AT_LEAST_ONE, $customerId);
            } else {
                self::assignToSegment(self::SEGMENT_AT_LEAST_ONE, $customerId);
                self::assignToSegment(self::SEGMENT_MULTIPLE, $customerId);
            }
        }
    }
}
