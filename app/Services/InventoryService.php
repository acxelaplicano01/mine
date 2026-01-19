<?php

namespace App\Services;

use App\Models\Product\Products;
use App\Models\Product\VariantProduct;
use App\Models\Product\Inventory\Inventories;
use App\Models\Product\Inventory\InventoryMovements;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Notifications\LowStockNotification;
use App\Models\User;

class InventoryService
{
    /**
     * Reducir inventario al crear un pedido
     */
    public static function reduceStock($productId, $quantity, $variantId = null, $orderId = null)
    {
        try {
            DB::beginTransaction();

            if ($variantId) {
                // Manejar variante
                $variant = VariantProduct::findOrFail($variantId);
                $cantidadAnterior = $variant->cantidad_inventario;
                $cantidadNueva = $cantidadAnterior - $quantity;

                // Actualizar inventario de variante
                $variant->update(['cantidad_inventario' => max(0, $cantidadNueva)]);

                // Registrar movimiento
                InventoryMovements::create([
                    'id_product' => $productId,
                    'id_variant' => $variantId,
                    'id_inventory' => null,
                    'type' => 'salida',
                    'quantity' => $quantity,
                    'cantidad_anterior' => $cantidadAnterior,
                    'cantidad_nueva' => max(0, $cantidadNueva),
                    'reason' => 'Venta - Pedido',
                    'reference_type' => 'App\\Models\\Order\\Orders',
                    'reference_id' => $orderId,
                    'user_id' => Auth::id(),
                ]);
            } else {
                // Manejar producto sin variante
                $product = Products::with('inventory')->findOrFail($productId);
                
                if ($product->inventory && $product->inventory->seguimiento_inventario) {
                    $inventory = $product->inventory;
                    $cantidadAnterior = $inventory->cantidad_inventario;
                    $cantidadNueva = $cantidadAnterior - $quantity;

                    // Verificar si permite vender sin inventario
                    if ($cantidadNueva < 0 && !$inventory->permitir_vender_sin_inventario) {
                        throw new \Exception("Stock insuficiente para el producto: {$product->name}");
                    }

                    // Actualizar inventario
                    $inventory->update(['cantidad_inventario' => max(0, $cantidadNueva)]);

                    // Registrar movimiento
                    InventoryMovements::create([
                        'id_product' => $productId,
                        'id_variant' => null,
                        'id_inventory' => $inventory->id,
                        'type' => 'salida',
                        'quantity' => $quantity,
                        'cantidad_anterior' => $cantidadAnterior,
                        'cantidad_nueva' => max(0, $cantidadNueva),
                        'reason' => 'Venta - Pedido',
                        'reference_type' => 'App\\Models\\Order\\Orders',
                        'reference_id' => $orderId,
                        'user_id' => Auth::id(),
                    ]);
                    
                    // Verificar stock bajo
                    self::checkLowStock($product, $inventory, max(0, $cantidadNueva));
                }
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Aumentar inventario (devoluciones, entradas)
     */
    public static function increaseStock($productId, $quantity, $variantId = null, $reason = 'Entrada', $referenceType = null, $referenceId = null)
    {
        try {
            DB::beginTransaction();

            if ($variantId) {
                // Manejar variante
                $variant = VariantProduct::findOrFail($variantId);
                $cantidadAnterior = $variant->cantidad_inventario;
                $cantidadNueva = $cantidadAnterior + $quantity;

                $variant->update(['cantidad_inventario' => $cantidadNueva]);

                InventoryMovements::create([
                    'id_product' => $productId,
                    'id_variant' => $variantId,
                    'id_inventory' => null,
                    'type' => 'entrada',
                    'quantity' => $quantity,
                    'cantidad_anterior' => $cantidadAnterior,
                    'cantidad_nueva' => $cantidadNueva,
                    'reason' => $reason,
                    'reference_type' => $referenceType,
                    'reference_id' => $referenceId,
                    'user_id' => Auth::id(),
                ]);
            } else {
                // Manejar producto sin variante
                $product = Products::with('inventory')->findOrFail($productId);
                
                if ($product->inventory) {
                    $inventory = $product->inventory;
                    $cantidadAnterior = $inventory->cantidad_inventario;
                    $cantidadNueva = $cantidadAnterior + $quantity;

                    $inventory->update(['cantidad_inventario' => $cantidadNueva]);

                    InventoryMovements::create([
                        'id_product' => $productId,
                        'id_variant' => null,
                        'id_inventory' => $inventory->id,
                        'type' => 'entrada',
                        'quantity' => $quantity,
                        'cantidad_anterior' => $cantidadAnterior,
                        'cantidad_nueva' => $cantidadNueva,
                        'reason' => $reason,
                        'reference_type' => $referenceType,
                        'reference_id' => $referenceId,
                        'user_id' => Auth::id(),
                    ]);
                }
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Ajustar inventario manualmente
     */
    public static function adjustStock($productId, $newQuantity, $variantId = null, $reason = 'Ajuste manual', $notes = null)
    {
        try {
            DB::beginTransaction();

            if ($variantId) {
                $variant = VariantProduct::findOrFail($variantId);
                $cantidadAnterior = $variant->cantidad_inventario;
                $difference = $newQuantity - $cantidadAnterior;

                $variant->update(['cantidad_inventario' => $newQuantity]);

                InventoryMovements::create([
                    'id_product' => $productId,
                    'id_variant' => $variantId,
                    'id_inventory' => null,
                    'type' => 'ajuste',
                    'quantity' => abs($difference),
                    'cantidad_anterior' => $cantidadAnterior,
                    'cantidad_nueva' => $newQuantity,
                    'reason' => $reason,
                    'user_id' => Auth::id(),
                    'notes' => $notes,
                ]);
            } else {
                $product = Products::with('inventory')->findOrFail($productId);
                
                if ($product->inventory) {
                    $inventory = $product->inventory;
                    $cantidadAnterior = $inventory->cantidad_inventario;
                    $difference = $newQuantity - $cantidadAnterior;

                    $inventory->update(['cantidad_inventario' => $newQuantity]);

                    InventoryMovements::create([
                        'id_product' => $productId,
                        'id_variant' => null,
                        'id_inventory' => $inventory->id,
                        'type' => 'ajuste',
                        'quantity' => abs($difference),
                        'cantidad_anterior' => $cantidadAnterior,
                        'cantidad_nueva' => $newQuantity,
                        'reason' => $reason,
                        'user_id' => Auth::id(),
                        'notes' => $notes,
                    ]);
                }
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Devolución de stock (cancelación de pedido)
     */
    public static function returnStock($productId, $quantity, $variantId = null, $orderId = null)
    {
        return self::increaseStock(
            $productId, 
            $quantity, 
            $variantId, 
            'Devolución - Cancelación de pedido',
            'App\\Models\\Order\\Orders',
            $orderId
        );
    }

    /**
     * Verificar disponibilidad de stock
     */
    public static function checkAvailability($productId, $quantity, $variantId = null)
    {
        if ($variantId) {
            $variant = VariantProduct::find($variantId);
            return $variant && $variant->cantidad_inventario >= $quantity;
        } else {
            $product = Products::with('inventory')->find($productId);
            
            if (!$product || !$product->inventory) {
                return false;
            }

            $inventory = $product->inventory;
            
            // Si no hace seguimiento, siempre disponible
            if (!$inventory->seguimiento_inventario) {
                return true;
            }

            // Si permite vender sin inventario
            if ($inventory->permitir_vender_sin_inventario) {
                return true;
            }

            // Verificar stock disponible
            return $inventory->cantidad_inventario >= $quantity;
        }
    }

    /**
     * Obtener historial de movimientos
     */
    public static function getMovements($productId, $variantId = null, $limit = 50)
    {
        $query = InventoryMovements::with(['user', 'product', 'variant'])
            ->where('id_product', $productId);

        if ($variantId) {
            $query->where('id_variant', $variantId);
        }

        return $query->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Verificar y enviar alerta si el stock está bajo
     */
    protected static function checkLowStock($product, $inventory, $currentStock)
    {
        if (!$inventory || !$inventory->seguimiento_inventario) {
            return;
        }

        $threshold = $inventory->umbral_aviso_inventario ?? 0;
        
        // Si el stock actual es menor o igual al umbral, enviar notificación
        if ($threshold > 0 && $currentStock <= $threshold) {
            // Obtener administradores para notificar
            $admins = User::whereHas('roles', function($q) {
                $q->whereIn('name', ['admin', 'super-admin', 'inventory-manager']);
            })->get();
            
            foreach ($admins as $admin) {
                $admin->notify(new LowStockNotification($product, $currentStock, $threshold));
            }
        }
    }

    /**
     * Obtener productos con stock bajo (incluye variantes)
     */
    public static function getLowStockProducts()
    {
        // Productos SIN variantes con stock bajo
        $productosSinVariantes = Products::with('inventory')
            ->doesntHave('variants')
            ->whereHas('inventory', function($q) {
                $q->where('seguimiento_inventario', true)
                  ->whereColumn('cantidad_inventario', '<=', 'umbral_aviso_inventario')
                  ->where('umbral_aviso_inventario', '>', 0);
            })
            ->get();

        // Variantes con stock bajo (el umbral está en el inventory del producto padre)
        // Obtener todos los productos con variantes que tienen seguimiento de inventario
        $productosConVariantes = Products::with(['inventory', 'variants'])
            ->has('variants')
            ->whereHas('inventory', function($q) {
                $q->where('seguimiento_inventario', true)
                  ->where('umbral_aviso_inventario', '>', 0);
            })
            ->get();

        // Combinar resultados
        $resultado = collect();
        
        foreach ($productosSinVariantes as $producto) {
            $resultado->push($producto);
        }
        
        // Filtrar variantes con stock bajo
        foreach ($productosConVariantes as $producto) {
            $umbral = $producto->inventory->umbral_aviso_inventario ?? 0;
            
            foreach ($producto->variants as $variante) {
                $stock = $variante->cantidad_inventario ?? 0;
                
                if ($umbral > 0 && $stock <= $umbral && $stock > 0) {
                    // Crear un objeto que represente la variante con stock bajo
                    $resultado->push((object)[
                        'id' => 'variant-' . $variante->id,
                        'type' => 'variant',
                        'product_id' => $producto->id,
                        'variant_id' => $variante->id,
                        'name' => $producto->name . ' - ' . ($variante->name_variant ?? 'Variante'),
                        'variant_name' => $variante->name_variant ?? 'Variante',
                        'image' => $producto->image ?? null,
                        'sku' => $variante->sku,
                        'inventory' => (object)[
                            'cantidad_inventario' => $variante->cantidad_inventario,
                            'umbral_aviso_inventario' => $umbral,
                            'sku' => $variante->sku,
                        ],
                    ]);
                }
            }
        }

        return $resultado;
    }
}
