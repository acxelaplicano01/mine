<?php

namespace App\Livewire\Product;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Product\Products;
use App\Models\Product\Inventory\InventoryMovements;
use App\Services\InventoryService;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Traits\HasSavedViews;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.collapsable')]
class Inventories extends Component
{
    use WithPagination, HasSavedViews;

    // Configuración de vistas guardadas
    protected $viewEntity = 'inventarios';

    // Propiedades para filtros
    public $search = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $perPage = 10;

    // Selección múltiple
    public $selected = [];
    public $selectAll = false;
    public $currentPageItemIds = [];
    public $showOnlySelected = false;
    
    // Propiedades para filtros guardables (desde HasSavedViews)
    public $showFilterDropdown = false;
    
    // Visibilidad de columnas
    public $showThresholdColumn = false;
    public $showStatusColumn = false;
    public $activeFilter = 'todos';
    
    // Modal de umbral masivo
    public $showThresholdModal = false;
    public $bulkThreshold = 0;

    // Modal de ajuste de inventario
    public $showAdjustModal = false;
    public $selectedProduct = null;
    public $adjustQuantity = 0;
    public $adjustReason = '';
    public $adjustNotes = '';

    // Modal de historial
    public $showHistoryModal = false;
    public $movements = [];

    // Estado de expansión de variantes
    public $expandedProducts = [];

    // Cambios pendientes de guardar
    public $pendingChanges = [];
    public $dropdownOpen = [];
    
    // Almacenar valores de dropdown de manera dinámica
    public $dropdownValues = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'activeFilter' => ['except' => 'todos'],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            // Seleccionar todos los items de la página actual
            $this->selected = $this->getCurrentPageItemIds();
        } else {
            $this->selected = [];
        }
    }

    public function updatedSelected($value)
    {
        // Verificar si todos los items de la página actual están seleccionados
        $currentPageIds = $this->getCurrentPageItemIds();
        $this->selectAll = count(array_intersect($this->selected, $currentPageIds)) === count($currentPageIds) && count($currentPageIds) > 0;
    }

    protected function getCurrentPageItemIds()
    {
        // Este método se sobrescribirá al obtener los IDs durante el render
        return property_exists($this, 'currentPageItemIds') ? $this->currentPageItemIds : [];
    }

    public function toggleVariants($productId)
    {
        if (in_array($productId, $this->expandedProducts)) {
            $this->expandedProducts = array_diff($this->expandedProducts, [$productId]);
        } else {
            $this->expandedProducts[] = $productId;
        }
    }

    public function quickAdjust($itemId, $newQuantity)
    {
        // Guardar cambio pendiente en lugar de guardarlo inmediatamente
        if (!isset($this->pendingChanges[$itemId])) {
            $this->pendingChanges[$itemId] = [];
        }
        $this->pendingChanges[$itemId]['disponible'] = $newQuantity;
    }

    public function updateExistencia($itemId, $newQuantity)
    {
        // Guardar cambio pendiente
        if (!isset($this->pendingChanges[$itemId])) {
            $this->pendingChanges[$itemId] = [];
        }
        $this->pendingChanges[$itemId]['existencia'] = $newQuantity;
    }

    public function toggleDropdown($itemId, $type)
    {
        $key = $itemId . '-' . $type;
        if (isset($this->dropdownOpen[$key]) && $this->dropdownOpen[$key]) {
            $this->dropdownOpen[$key] = false;
        } else {
            // Cerrar todos los demás dropdowns
            $this->dropdownOpen = [];
            $this->dropdownOpen[$key] = true;
        }
    }

    public function applyAdjustment($itemId, $type)
    {
        // Obtener los valores según el tipo
        if ($type === 'disponible') {
            $action = $this->dropdownValues['adjustAction_' . $itemId] ?? 'ajustar';
            $amount = $this->dropdownValues['adjustAmount_' . $itemId] ?? null;
            $reason = $this->dropdownValues['adjustReason_' . $itemId] ?? '';
            $notes = $this->dropdownValues['adjustNotes_' . $itemId] ?? '';
        } else {
            $action = 'ajustar';
            $amount = $this->dropdownValues['adjustAmountExist_' . $itemId] ?? null;
            $reason = $this->dropdownValues['adjustReasonExist_' . $itemId] ?? '';
            $notes = $this->dropdownValues['adjustNotesExist_' . $itemId] ?? '';
        }

        if ($amount === null || $amount === '' || empty($reason)) {
            session()->flash('error', 'Debe ingresar una cantidad y un motivo');
            return;
        }
        
        // Convertir a número
        $amount = (int) $amount;

        // Parsear el ID
        $parts = explode('-', $itemId);
        $itemType = $parts[0];
        $id = $parts[1];

        if ($itemType === 'variant') {
            $variant = \App\Models\Product\VariantProduct::find($id);
            if ($variant) {
                $oldStock = $variant->cantidad_inventario;
                
                if ($type === 'disponible') {
                    if ($action === 'ajustar') {
                        $newStock = $oldStock + $amount;
                        $variant->update(['cantidad_inventario' => $newStock]);
                        
                        // Actualizar pendingChanges
                        if (!isset($this->pendingChanges[$itemId])) {
                            $this->pendingChanges[$itemId] = [];
                        }
                        $this->pendingChanges[$itemId]['disponible'] = $newStock;
                        
                        InventoryMovements::create([
                            'id_variant' => $variant->id,
                            'id_product' => $variant->product_id,
                            'type' => 'ajuste',
                            'quantity' => abs($amount),
                            'cantidad_anterior' => $oldStock,
                            'cantidad_nueva' => $newStock,
                            'reason' => $reason,
                            'notes' => $notes,
                            'user_id' => auth()->id(),
                        ]);
                    } elseif ($action === 'mover_no_disponible') {
                        // Restar la cantidad del stock disponible (se está moviendo a no disponible)
                        $newStock = $oldStock - abs($amount);
                        $noDisponibleActual = $variant->cantidad_no_disponible ?? 0;
                        $variant->update([
                            'cantidad_inventario' => $newStock,
                            'cantidad_no_disponible' => $noDisponibleActual + abs($amount)
                        ]);
                        
                        // Actualizar pendingChanges
                        if (!isset($this->pendingChanges[$itemId])) {
                            $this->pendingChanges[$itemId] = [];
                        }
                        $this->pendingChanges[$itemId]['disponible'] = $newStock;
                        
                        InventoryMovements::create([
                            'id_variant' => $variant->id,
                            'id_product' => $variant->product_id,
                            'type' => 'salida',
                            'quantity' => abs($amount),
                            'cantidad_anterior' => $oldStock,
                            'cantidad_nueva' => $newStock,
                            'reason' => $reason . ' (Movido a no disponible)',
                            'notes' => $notes,
                            'user_id' => auth()->id(),
                        ]);
                    }
                } elseif ($type === 'existencia') {
                    $newStock = $oldStock + $amount;
                    $variant->update(['cantidad_inventario' => $newStock]);
                    
                    // Actualizar también el pendingChanges para que el input de disponible refleje el cambio
                    if (!isset($this->pendingChanges[$itemId])) {
                        $this->pendingChanges[$itemId] = [];
                    }
                    $this->pendingChanges[$itemId]['disponible'] = $newStock;
                    
                    InventoryMovements::create([
                        'id_variant' => $variant->id,
                        'id_product' => $variant->product_id,
                        'type' => $amount > 0 ? 'entrada' : 'salida',
                        'quantity' => abs($amount),
                        'cantidad_anterior' => $oldStock,
                        'cantidad_nueva' => $newStock,
                        'reason' => $reason,
                        'notes' => $notes,
                        'user_id' => auth()->id(),
                    ]);
                }
            }
        } else {
            $product = Products::find($id);
            if ($product && $product->inventory) {
                $oldStock = $product->inventory->cantidad_inventario;
                
                if ($type === 'disponible') {
                    if ($action === 'ajustar') {
                        $newStock = $oldStock + $amount;
                        $product->inventory->update(['cantidad_inventario' => $newStock]);
                        
                        // Actualizar pendingChanges
                        if (!isset($this->pendingChanges[$itemId])) {
                            $this->pendingChanges[$itemId] = [];
                        }
                        $this->pendingChanges[$itemId]['disponible'] = $newStock;
                        
                        InventoryMovements::create([
                            'id_product' => $product->id,
                            'id_inventory' => $product->inventory->id,
                            'type' => 'ajuste',
                            'quantity' => abs($amount),
                            'cantidad_anterior' => $oldStock,
                            'cantidad_nueva' => $newStock,
                            'reason' => $reason,
                            'notes' => $notes,
                            'user_id' => auth()->id(),
                        ]);
                    } elseif ($action === 'mover_no_disponible') {
                        // Restar la cantidad del stock disponible (se está moviendo a no disponible)
                        $newStock = $oldStock - abs($amount);
                        $noDisponibleActual = $product->inventory->cantidad_no_disponible ?? 0;
                        $product->inventory->update([
                            'cantidad_inventario' => $newStock,
                            'cantidad_no_disponible' => $noDisponibleActual + abs($amount)
                        ]);
                        
                        // Actualizar pendingChanges
                        if (!isset($this->pendingChanges[$itemId])) {
                            $this->pendingChanges[$itemId] = [];
                        }
                        $this->pendingChanges[$itemId]['disponible'] = $newStock;
                        
                        InventoryMovements::create([
                            'id_product' => $product->id,
                            'id_inventory' => $product->inventory->id,
                            'type' => 'salida',
                            'quantity' => abs($amount),
                            'cantidad_anterior' => $oldStock,
                            'cantidad_nueva' => $newStock,
                            'reason' => $reason . ' (Movido a no disponible)',
                            'notes' => $notes,
                            'user_id' => auth()->id(),
                        ]);
                    }
                } elseif ($type === 'existencia') {
                    $newStock = $oldStock + $amount;
                    $product->inventory->update(['cantidad_inventario' => $newStock]);
                    
                    // Actualizar también el pendingChanges para que el input de disponible refleje el cambio
                    if (!isset($this->pendingChanges[$itemId])) {
                        $this->pendingChanges[$itemId] = [];
                    }
                    $this->pendingChanges[$itemId]['disponible'] = $newStock;
                    
                    InventoryMovements::create([
                        'id_product' => $product->id,
                        'id_inventory' => $product->inventory->id,
                        'type' => $amount > 0 ? 'entrada' : 'salida',
                        'quantity' => abs($amount),
                        'cantidad_anterior' => $oldStock,
                        'cantidad_nueva' => $newStock,
                        'reason' => $reason,
                        'notes' => $notes,
                        'user_id' => auth()->id(),
                    ]);
                }
            }
        }

        // Limpiar los campos después de aplicar
        if ($type === 'disponible') {
            unset($this->dropdownValues['adjustAction_' . $itemId]);
            unset($this->dropdownValues['adjustAmount_' . $itemId]);
            unset($this->dropdownValues['adjustReason_' . $itemId]);
            unset($this->dropdownValues['adjustNotes_' . $itemId]);
        } else {
            unset($this->dropdownValues['adjustAmountExist_' . $itemId]);
            unset($this->dropdownValues['adjustReasonExist_' . $itemId]);
            unset($this->dropdownValues['adjustNotesExist_' . $itemId]);
        }

        // Forzar refresco del componente para mostrar los cambios
        $this->dispatch('$refresh');
        
        session()->flash('message', 'Ajuste aplicado correctamente');
    }

    public function eliminarInventarioNoDisponible($itemId, $motivo, $cantidad)
    {
        if (!$cantidad || $cantidad <= 0) {
            session()->flash('error', 'La cantidad debe ser mayor a 0');
            return;
        }

        $parts = explode('-', $itemId);
        $type = $parts[0];
        $id = $parts[1];

        if ($type === 'variant') {
            $variant = \App\Models\Product\VariantProduct::find($id);
            if (!$variant) {
                session()->flash('error', 'Variante no encontrada');
                return;
            }

            // Buscar TODOS los movimientos de inventario con este motivo y sumar cantidades
            $movements = InventoryMovements::where('id_variant', $variant->id)
                ->where('reason', 'like', '%(Movido a no disponible)%')
                ->whereRaw("SUBSTRING_INDEX(reason, ' (Movido', 1) = ?", [$motivo])
                ->get();

            $totalDisponible = $movements->sum('quantity');

            if ($totalDisponible == 0) {
                session()->flash('error', 'No hay inventario no disponible con este motivo');
                return;
            }

            // Verificar que no se intente eliminar más de lo disponible
            if ($cantidad > $totalDisponible) {
                session()->flash('error', "Solo puedes eliminar hasta $totalDisponible unidades de este motivo");
                return;
            }

            // Restar de cantidad_no_disponible
            $noDisponibleActual = $variant->cantidad_no_disponible ?? 0;
            $noDisponibleNuevo = max(0, $noDisponibleActual - $cantidad);
            $variant->update([
                'cantidad_no_disponible' => $noDisponibleNuevo
            ]);

            // Crear movimiento negativo para mantener el historial (en lugar de eliminar)
            InventoryMovements::create([
                'id_variant' => $variant->id,
                'id_product' => $variant->product_id,
                'type' => 'ajuste',
                'quantity' => -$cantidad,
                'cantidad_anterior' => $noDisponibleActual,
                'cantidad_nueva' => $noDisponibleNuevo,
                'reason' => "$motivo (Movido a no disponible)",
                'notes' => 'Eliminado de inventario no disponible',
                'user_id' => auth()->id(),
            ]);

            session()->flash('message', "Se eliminaron $cantidad unidades de inventario no disponible");
        } else {
            $product = Products::find($id);
            if (!$product || !$product->inventory) {
                session()->flash('error', 'Producto no encontrado');
                return;
            }

            $movements = InventoryMovements::where('id_product', $product->id)
                ->where('id_inventory', $product->inventory->id)
                ->where('reason', 'like', '%(Movido a no disponible)%')
                ->whereRaw("SUBSTRING_INDEX(reason, ' (Movido', 1) = ?", [$motivo])
                ->get();

            $totalDisponible = $movements->sum('quantity');

            if ($totalDisponible == 0) {
                session()->flash('error', 'No hay inventario no disponible con este motivo');
                return;
            }

            if ($cantidad > $totalDisponible) {
                session()->flash('error', "Solo puedes eliminar hasta $totalDisponible unidades de este motivo");
                return;
            }

            // Restar de cantidad_no_disponible
            $noDisponibleActual = $product->inventory->cantidad_no_disponible ?? 0;
            $noDisponibleNuevo = max(0, $noDisponibleActual - $cantidad);
            $product->inventory->update([
                'cantidad_no_disponible' => $noDisponibleNuevo
            ]);

            // Crear movimiento negativo para mantener el historial (en lugar de eliminar)
            InventoryMovements::create([
                'id_product' => $product->id,
                'id_inventory' => $product->inventory->id,
                'type' => 'ajuste',
                'quantity' => -$cantidad,
                'cantidad_anterior' => $noDisponibleActual,
                'cantidad_nueva' => $noDisponibleNuevo,
                'reason' => "$motivo (Movido a no disponible)",
                'notes' => 'Eliminado de inventario no disponible',
                'user_id' => auth()->id(),
            ]);

            session()->flash('message', "Se eliminaron $cantidad unidades de inventario no disponible");
        }
    }

    public function moverADisponible($itemId, $motivo, $cantidad)
    {
        if (!$cantidad || $cantidad <= 0) {
            session()->flash('error', 'La cantidad debe ser mayor a 0');
            return;
        }

        $parts = explode('-', $itemId);
        $type = $parts[0];
        $id = $parts[1];

        if ($type === 'variant') {
            $variant = \App\Models\Product\VariantProduct::find($id);
            if (!$variant) {
                session()->flash('error', 'Variante no encontrada');
                return;
            }

            $movements = InventoryMovements::where('id_variant', $variant->id)
                ->where('reason', 'like', '%(Movido a no disponible)%')
                ->whereRaw("SUBSTRING_INDEX(reason, ' (Movido', 1) = ?", [$motivo])
                ->get();

            $totalDisponible = $movements->sum('quantity');

            if ($totalDisponible == 0) {
                session()->flash('error', 'No hay inventario no disponible con este motivo');
                return;
            }

            if ($cantidad > $totalDisponible) {
                session()->flash('error', "Solo puedes mover hasta $totalDisponible unidades de este motivo");
                return;
            }

            // Actualizar no_disponible restando la cantidad
            $noDisponibleActual = $variant->cantidad_no_disponible ?? 0;
            $noDisponibleNuevo = max(0, $noDisponibleActual - $cantidad);
            
            // Actualizar tanto no_disponible como cantidad_inventario
            $oldStock = $variant->cantidad_inventario;
            $newStock = $oldStock + $cantidad;
            $variant->update([
                'cantidad_no_disponible' => $noDisponibleNuevo,
                'cantidad_inventario' => $newStock
            ]);

            // Crear movimiento negativo para mantener el historial (en lugar de eliminar)
            InventoryMovements::create([
                'id_variant' => $variant->id,
                'id_product' => $variant->product_id,
                'type' => 'ajuste',
                'quantity' => -$cantidad,
                'cantidad_anterior' => $noDisponibleActual,
                'cantidad_nueva' => $noDisponibleNuevo,
                'reason' => "$motivo (Movido a no disponible)",
                'notes' => 'Movido a disponible',
                'user_id' => auth()->id(),
            ]);

            // Registrar el movimiento de entrada a disponible
            InventoryMovements::create([
                'id_variant' => $variant->id,
                'id_product' => $variant->product_id,
                'type' => 'entrada',
                'quantity' => $cantidad,
                'cantidad_anterior' => $oldStock,
                'cantidad_nueva' => $newStock,
                'reason' => "Movido desde no disponible: $motivo",
                'user_id' => auth()->id(),
            ]);

            // Actualizar pendingChanges para reflejar el cambio en la vista
            $this->pendingChanges[$itemId]['disponible'] = $newStock;

            session()->flash('message', "Se movieron $cantidad unidades a disponible");
        } else {
            $product = Products::find($id);
            if (!$product || !$product->inventory) {
                session()->flash('error', 'Producto no encontrado');
                return;
            }

            $movements = InventoryMovements::where('id_product', $product->id)
                ->where('id_inventory', $product->inventory->id)
                ->where('reason', 'like', '%(Movido a no disponible)%')
                ->whereRaw("SUBSTRING_INDEX(reason, ' (Movido', 1) = ?", [$motivo])
                ->get();

            $totalDisponible = $movements->sum('quantity');

            if ($totalDisponible == 0) {
                session()->flash('error', 'No hay inventario no disponible con este motivo');
                return;
            }

            if ($cantidad > $totalDisponible) {
                session()->flash('error', "Solo puedes mover hasta $totalDisponible unidades de este motivo");
                return;
            }

            // Actualizar no_disponible restando la cantidad
            $noDisponibleActual = $product->inventory->cantidad_no_disponible ?? 0;
            $noDisponibleNuevo = max(0, $noDisponibleActual - $cantidad);
            
            // Actualizar tanto no_disponible como cantidad_inventario
            $oldStock = $product->inventory->cantidad_inventario;
            $newStock = $oldStock + $cantidad;
            $product->inventory->update([
                'cantidad_no_disponible' => $noDisponibleNuevo,
                'cantidad_inventario' => $newStock
            ]);

            // Crear movimiento negativo para mantener el historial (en lugar de eliminar)
            InventoryMovements::create([
                'id_product' => $product->id,
                'id_inventory' => $product->inventory->id,
                'type' => 'ajuste',
                'quantity' => -$cantidad,
                'cantidad_anterior' => $noDisponibleActual,
                'cantidad_nueva' => $noDisponibleNuevo,
                'reason' => "$motivo (Movido a no disponible)",
                'notes' => 'Movido a disponible',
                'user_id' => auth()->id(),
            ]);

            // Registrar el movimiento de entrada a disponible
            InventoryMovements::create([
                'id_product' => $product->id,
                'id_inventory' => $product->inventory->id,
                'type' => 'entrada',
                'quantity' => $cantidad,
                'cantidad_anterior' => $oldStock,
                'cantidad_nueva' => $newStock,
                'reason' => "Movido desde no disponible: $motivo",
                'user_id' => auth()->id(),
            ]);

            // Actualizar pendingChanges para reflejar el cambio en la vista
            $this->pendingChanges[$itemId]['disponible'] = $newStock;

            session()->flash('message', "Se movieron $cantidad unidades a disponible");
        }
    }

    public function agregarInventario($itemId, $motivo, $cantidad)
    {
        if (!$cantidad || $cantidad <= 0) {
            session()->flash('error', 'La cantidad debe ser mayor a 0');
            return;
        }

        $parts = explode('-', $itemId);
        $type = $parts[0];
        $id = $parts[1];

        if ($type === 'variant') {
            $variant = \App\Models\Product\VariantProduct::find($id);
            if (!$variant) {
                session()->flash('error', 'Variante no encontrada');
                return;
            }

            // Sumar a cantidad_no_disponible
            $noDisponibleActual = $variant->cantidad_no_disponible ?? 0;
            $noDisponibleNuevo = $noDisponibleActual + $cantidad;
            $variant->update([
                'cantidad_no_disponible' => $noDisponibleNuevo
            ]);

            // Crear movimiento de "no disponible" con el motivo (es una entrada a no disponible)
            InventoryMovements::create([
                'id_variant' => $variant->id,
                'id_product' => $variant->product_id,
                'type' => 'entrada',
                'quantity' => $cantidad,
                'cantidad_anterior' => $noDisponibleActual,
                'cantidad_nueva' => $noDisponibleNuevo,
                'reason' => "$motivo (Movido a no disponible)",
                'user_id' => auth()->id(),
            ]);

            session()->flash('message', "Se agregaron $cantidad unidades a no disponible ($motivo)");
        } else {
            $product = Products::find($id);
            if (!$product || !$product->inventory) {
                session()->flash('error', 'Producto no encontrado');
                return;
            }

            // Sumar a cantidad_no_disponible
            $noDisponibleActual = $product->inventory->cantidad_no_disponible ?? 0;
            $noDisponibleNuevo = $noDisponibleActual + $cantidad;
            $product->inventory->update([
                'cantidad_no_disponible' => $noDisponibleNuevo
            ]);

            // Crear movimiento de "no disponible" con el motivo (es una entrada a no disponible)
            InventoryMovements::create([
                'id_product' => $product->id,
                'id_inventory' => $product->inventory->id,
                'type' => 'entrada',
                'quantity' => $cantidad,
                'cantidad_anterior' => $noDisponibleActual,
                'cantidad_nueva' => $noDisponibleNuevo,
                'reason' => "$motivo (Movido a no disponible)",
                'user_id' => auth()->id(),
            ]);

            session()->flash('message', "Se agregaron $cantidad unidades a no disponible ($motivo)");
        }
    }

    public function savePendingChanges()
    {
        $changedItems = [];
        
        foreach ($this->pendingChanges as $itemId => $changes) {
            $parts = explode('-', $itemId);
            $type = $parts[0];
            $id = $parts[1];

            if ($type === 'variant') {
                $variant = \App\Models\Product\VariantProduct::find($id);
                if ($variant) {
                    $hasChanges = false;
                    
                    // Guardar cambios en cantidad disponible
                    if (isset($changes['disponible'])) {
                        $oldStock = $variant->cantidad_inventario;
                        $newStock = $changes['disponible'];
                        
                        if ($oldStock != $newStock) {
                            $variant->update(['cantidad_inventario' => $newStock]);
                            
                            InventoryMovements::create([
                                'id_variant' => $variant->id,
                                'id_product' => $variant->product_id,
                                'type' => $newStock > $oldStock ? 'entrada' : 'salida',
                                'quantity' => abs($newStock - $oldStock),
                                'cantidad_anterior' => $oldStock,
                                'cantidad_nueva' => $newStock,
                                'reason' => 'Ajuste manual desde inventario',
                                'user_id' => auth()->id(),
                            ]);
                            
                            $hasChanges = true;
                        }
                    }
                    
                    // Guardar cambios en umbral (solo productos con variantes tienen inventory en producto padre)
                    if (isset($changes['threshold'])) {
                        $product = Products::find($variant->product_id);
                        if ($product && $product->inventory) {
                            $oldThreshold = $product->inventory->umbral_aviso_inventario ?? 0;
                            $newThreshold = $changes['threshold'];
                            
                            if ($oldThreshold != $newThreshold) {
                                $product->inventory->update(['umbral_aviso_inventario' => $newThreshold]);
                                $hasChanges = true;
                            }
                        }
                    }
                    
                    if ($hasChanges) {
                        $changedItems[] = $itemId;
                    }
                }
            } else {
                $product = Products::find($id);
                if ($product && $product->inventory) {
                    $hasChanges = false;
                    
                    // Guardar cambios en cantidad disponible
                    if (isset($changes['disponible'])) {
                        $oldStock = $product->inventory->cantidad_inventario;
                        $newStock = $changes['disponible'];
                        
                        if ($oldStock != $newStock) {
                            $product->inventory->update(['cantidad_inventario' => $newStock]);
                            
                            InventoryMovements::create([
                                'id_product' => $product->id,
                                'id_inventory' => $product->inventory->id,
                                'type' => $newStock > $oldStock ? 'entrada' : 'salida',
                                'quantity' => abs($newStock - $oldStock),
                                'cantidad_anterior' => $oldStock,
                                'cantidad_nueva' => $newStock,
                                'reason' => 'Ajuste manual desde inventario',
                                'user_id' => auth()->id(),
                            ]);
                            
                            $hasChanges = true;
                        }
                    }
                    
                    // Guardar cambios en umbral
                    if (isset($changes['threshold'])) {
                        $oldThreshold = $product->inventory->umbral_aviso_inventario ?? 0;
                        $newThreshold = $changes['threshold'];
                        
                        if ($oldThreshold != $newThreshold) {
                            $product->inventory->update(['umbral_aviso_inventario' => $newThreshold]);
                            $hasChanges = true;
                        }
                    }
                    
                    if ($hasChanges) {
                        $changedItems[] = $itemId;
                    }
                }
            }
        }

        if (count($changedItems) > 0) {
            session()->flash('message', count($changedItems) . ' cambio(s) guardado(s) correctamente');
        } else {
            session()->flash('message', 'No hay cambios para guardar');
        }
        
        // No limpiar pendingChanges para mantener los valores en los inputs
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function openAdjustModal($itemId)
    {
        // Parsear el ID para determinar si es producto o variante
        $parts = explode('-', $itemId);
        $type = $parts[0]; // 'product' o 'variant'
        $id = $parts[1];

        if ($type === 'variant') {
            $variant = \App\Models\Product\VariantProduct::find($id);
            if ($variant) {
                $this->selectedProduct = (object)[
                    'id' => 'variant-' . $variant->id,
                    'type' => 'variant',
                    'variant_id' => $variant->id,
                    'product_id' => $variant->product_id,
                    'name' => Products::find($variant->product_id)->name ?? 'Producto',
                    'variant_name' => $this->getVariantName($variant),
                    'stock' => $variant->cantidad_inventario,
                    'variant' => $variant,
                ];
                $this->adjustQuantity = $variant->cantidad_inventario;
            }
        } else {
            $product = Products::with('inventory')->find($id);
            if ($product && $product->inventory) {
                $this->selectedProduct = (object)[
                    'id' => 'product-' . $product->id,
                    'type' => 'product',
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'variant_name' => null,
                    'stock' => $product->inventory->cantidad_inventario,
                    'product' => $product,
                ];
                $this->adjustQuantity = $product->inventory->cantidad_inventario;
            }
        }
        
        if ($this->selectedProduct) {
            $this->adjustReason = '';
            $this->adjustNotes = '';
            $this->showAdjustModal = true;
        }
    }

    public function closeAdjustModal()
    {
        $this->showAdjustModal = false;
        $this->selectedProduct = null;
        $this->adjustQuantity = 0;
        $this->adjustReason = '';
        $this->adjustNotes = '';
    }

    public function saveAdjustment()
    {
        $this->validate([
            'adjustQuantity' => 'required|integer|min:0',
            'adjustReason' => 'required|string|max:255',
            'adjustNotes' => 'nullable|string|max:500',
        ]);

        try {
            if ($this->selectedProduct->type === 'variant') {
                $variant = $this->selectedProduct->variant;
                $oldStock = $variant->cantidad_inventario;
                $variant->update(['cantidad_inventario' => $this->adjustQuantity]);

                // Registrar movimiento
                InventoryMovements::create([
                    'id_variant' => $variant->id,
                    'id_product' => $variant->product_id,
                    'type' => 'ajuste',
                    'quantity' => abs($this->adjustQuantity - $oldStock),
                    'cantidad_anterior' => $oldStock,
                    'cantidad_nueva' => $this->adjustQuantity,
                    'reason' => $this->adjustReason,
                    'notes' => $this->adjustNotes,
                    'user_id' => auth()->id(),
                ]);
            } else {
                InventoryService::adjustStock(
                    $this->selectedProduct->product_id,
                    $this->adjustQuantity,
                    null,
                    $this->adjustReason,
                    $this->adjustNotes
                );
            }

            session()->flash('message', 'Inventario ajustado correctamente.');
            $this->closeAdjustModal();
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error al ajustar inventario: ' . $e->getMessage());
        }
    }

    public function openHistoryModal($itemId)
    {
        // Parsear el ID para determinar si es producto o variante
        $parts = explode('-', $itemId);
        $type = $parts[0];
        $id = $parts[1];

        if ($type === 'variant') {
            $variant = \App\Models\Product\VariantProduct::find($id);
            if ($variant) {
                $this->selectedProduct = (object)[
                    'name' => Products::find($variant->product_id)->name ?? 'Producto',
                    'variant_name' => $this->getVariantName($variant),
                ];
                $this->movements = InventoryMovements::where('id_variant', $id)
                    ->with('user')
                    ->orderBy('created_at', 'desc')
                    ->limit(100)
                    ->get();
            }
        } else {
            $product = Products::find($id);
            if ($product) {
                $this->selectedProduct = (object)[
                    'name' => $product->name,
                    'variant_name' => null,
                ];
                $this->movements = InventoryService::getMovements($id, null, 100);
            }
        }
        
        $this->showHistoryModal = true;
    }

    public function closeHistoryModal()
    {
        $this->showHistoryModal = false;
        $this->selectedProduct = null;
        $this->movements = [];
    }

    /**
     * Aplicar filtros a la consulta
     * Puede recibir un filtro específico o aplicar todos los filtros activos
     */
    public function applyFilterToQuery($query, $filter = null)
    {
        // Si se pasa un filtro específico, aplicarlo
        if ($filter !== null) {
            switch($filter['type']) {
                case 'nivel_stock_sin_stock':
                    $query->where(function($q) {
                        $q->whereHas('inventory', function($q2) {
                            $q2->where('cantidad_inventario', '<=', 0);
                        })->orWhereHas('variants', function($q2) {
                            $q2->where('cantidad_inventario', '<=', 0);
                        });
                    });
                    break;
                case 'nivel_stock_stock_bajo':
                    $query->where(function($q) {
                        $q->whereHas('inventory', function($q2) {
                            $q2->whereColumn('cantidad_inventario', '<=', 'umbral_aviso_inventario')
                              ->where('umbral_aviso_inventario', '>', 0)
                              ->where('cantidad_inventario', '>', 0);
                        })->orWhereHas('variants', function($q2) {
                            $q2->whereColumn('cantidad_inventario', '<=', 'umbral_aviso_inventario')
                              ->where('umbral_aviso_inventario', '>', 0)
                              ->where('cantidad_inventario', '>', 0);
                        });
                    });
                    break;
                case 'nivel_stock_stock_normal':
                    $query->where(function($q) {
                        $q->whereHas('inventory', function($q2) {
                            $q2->where(function($q3) {
                                $q3->whereColumn('cantidad_inventario', '>', 'umbral_aviso_inventario')
                                   ->orWhere('umbral_aviso_inventario', '<=', 0);
                            })->where('cantidad_inventario', '>', 0);
                        })->orWhereHas('variants', function($q2) {
                            $q2->where(function($q3) {
                                $q3->whereColumn('cantidad_inventario', '>', 'umbral_aviso_inventario')
                                   ->orWhere('umbral_aviso_inventario', '<=', 0);
                            })->where('cantidad_inventario', '>', 0);
                        });
                    });
                    break;
                case 'seguimiento_con_seguimiento':
                    $query->whereHas('inventory', function($q) {
                        $q->where('seguimiento_inventario', true);
                    });
                    break;
                case 'seguimiento_sin_seguimiento':
                    $query->whereHas('inventory', function($q) {
                        $q->where('seguimiento_inventario', false)
                          ->orWhereNull('seguimiento_inventario');
                    });
                    break;
            }
            return $query;
        }
        
        // Si no hay filtro específico, aplicar filtros generales
        // Búsqueda
        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhereHas('inventory', function($q2) {
                      $q2->where('sku', 'like', '%' . $this->search . '%')
                         ->orWhere('barcode', 'like', '%' . $this->search . '%');
                  });
            });
        }

        // Filtro por tabs predefinidos usando activeFilter
        if ($this->activeFilter === 'tracked') {
            $query->whereHas('inventory', function($q) {
                $q->where('seguimiento_inventario', true);
            });
        } elseif ($this->activeFilter === 'untracked') {
            $query->whereHas('inventory', function($q) {
                $q->where('seguimiento_inventario', false)
                  ->orWhereNull('seguimiento_inventario');
            });
        } elseif ($this->activeFilter === 'low-stock') {
            $query->where(function($q) {
                // Productos SIN variantes con stock bajo (stock en inventory)
                $q->where(function($q2) {
                    $q2->doesntHave('variants')
                       ->whereHas('inventory', function($q3) {
                           $q3->whereColumn('cantidad_inventario', '<=', 'umbral_aviso_inventario')
                              ->where('umbral_aviso_inventario', '>', 0)
                              ->where('cantidad_inventario', '>', 0);
                       });
                })
                // Productos CON variantes donde alguna variante tiene stock bajo
                // (el umbral está en inventory del producto padre)
                ->orWhere(function($q2) {
                    $q2->has('variants')
                       ->whereHas('inventory', function($q3) {
                           $q3->where('umbral_aviso_inventario', '>', 0);
                       })
                       ->whereHas('variants', function($q3) {
                           $q3->where('cantidad_inventario', '>', 0)
                              ->whereRaw('cantidad_inventario <= (SELECT umbral_aviso_inventario FROM inventories WHERE inventories.id = (SELECT id_inventory FROM products WHERE products.id = variant_products.product_id))');
                       });
                });
            });
        } elseif ($this->activeFilter === 'out-of-stock') {
            $query->where(function($q) {
                // Productos SIN variantes sin stock
                $q->where(function($q2) {
                    $q2->doesntHave('variants')
                       ->whereHas('inventory', function($q3) {
                           $q3->where('cantidad_inventario', '<=', 0);
                       });
                })
                // Productos CON variantes donde alguna variante tiene stock 0
                ->orWhere(function($q2) {
                    $q2->has('variants')
                       ->whereHas('variants', function($q3) {
                           $q3->where('cantidad_inventario', '<=', 0);
                       });
                });
            });
        }
        
        // Aplicar filtros guardables desde HasSavedViews
        if (count($this->activeFilters) > 0) {
            // Agrupar filtros por tipo para aplicar OR dentro del mismo tipo
            $filtersByType = [];
            foreach($this->activeFilters as $filterId => $filter) {
                $filtersByType[$filter['type']][$filterId] = $filter;
            }
            
            // Aplicar cada grupo de filtros
            foreach($filtersByType as $type => $filters) {
                $this->applyFilterGroupToQuery($query, $type, $filters);
            }
        }

        return $query;
    }

    public function getRealChangesCountProperty()
    {
        $count = 0;
        foreach ($this->pendingChanges as $itemId => $changes) {
            $parts = explode('-', $itemId);
            $type = $parts[0];
            $id = $parts[1];
            
            $hasStockChange = false;
            $hasThresholdChange = false;
            
            // Verificar cambio en disponible
            if (isset($changes['disponible'])) {
                $currentStock = 0;
                if ($type === 'variant') {
                    $variant = \App\Models\Product\VariantProduct::find($id);
                    $currentStock = $variant?->cantidad_inventario ?? 0;
                } else {
                    $product = Products::find($id);
                    $currentStock = $product?->inventory?->cantidad_inventario ?? 0;
                }
                
                if ($changes['disponible'] != $currentStock) {
                    $hasStockChange = true;
                }
            }
            
            // Verificar cambio en threshold
            if (isset($changes['threshold'])) {
                $currentThreshold = 0;
                if ($type === 'variant') {
                    $variant = \App\Models\Product\VariantProduct::find($id);
                    if ($variant) {
                        $product = Products::find($variant->product_id);
                        $currentThreshold = $product?->inventory?->umbral_aviso_inventario ?? 0;
                    }
                } else {
                    $product = Products::find($id);
                    $currentThreshold = $product?->inventory?->umbral_aviso_inventario ?? 0;
                }
                
                if ($changes['threshold'] != $currentThreshold) {
                    $hasThresholdChange = true;
                }
            }
            
            // Contar el item si tiene algún cambio
            if ($hasStockChange || $hasThresholdChange) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Abrir modal para establecer umbral a items seleccionados
     */
    public function setUmbral()
    {
        if (count($this->selected) === 0) {
            session()->flash('error', 'Debes seleccionar al menos un producto');
            return;
        }
        
        $this->bulkThreshold = 0;
        $this->showThresholdModal = true;
    }

    /**
     * Cerrar modal de umbral
     */
    public function closeThresholdModal()
    {
        $this->showThresholdModal = false;
        $this->bulkThreshold = 0;
    }

    /**
     * Guardar umbral para los items seleccionados
     */
    public function saveThresholdForSelected()
    {
        $this->validate([
            'bulkThreshold' => 'required|integer|min:0',
        ]);

        $updatedCount = 0;

        foreach ($this->selected as $itemId) {
            $parts = explode('-', $itemId);
            $type = $parts[0];
            $id = $parts[1];

            if ($type === 'variant') {
                // Para variantes, actualizar el umbral del producto padre
                $variant = \App\Models\Product\VariantProduct::find($id);
                if ($variant) {
                    $product = Products::find($variant->product_id);
                    if ($product && $product->inventory) {
                        $product->inventory->update(['umbral_aviso_inventario' => $this->bulkThreshold]);
                        
                        // Actualizar pendingChanges para reflejar el cambio
                        if (!isset($this->pendingChanges[$itemId])) {
                            $this->pendingChanges[$itemId] = [];
                        }
                        $this->pendingChanges[$itemId]['threshold'] = $this->bulkThreshold;
                        
                        $updatedCount++;
                    }
                }
            } else {
                // Para productos sin variantes
                $product = Products::find($id);
                if ($product && $product->inventory) {
                    $product->inventory->update(['umbral_aviso_inventario' => $this->bulkThreshold]);
                    
                    // Actualizar pendingChanges para reflejar el cambio
                    if (!isset($this->pendingChanges[$itemId])) {
                        $this->pendingChanges[$itemId] = [];
                    }
                    $this->pendingChanges[$itemId]['threshold'] = $this->bulkThreshold;
                    
                    $updatedCount++;
                }
            }
        }

        $this->closeThresholdModal();
        $this->selected = [];
        $this->selectAll = false;
        
        session()->flash('message', "Umbral actualizado en $updatedCount producto(s)");
    }

    public function exportLowStock()
    {
        $products = InventoryService::getLowStockProducts();
        
        $filename = 'productos_stock_bajo_' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($products) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Producto', 'SKU', 'Stock Actual', 'Umbral', 'Estado']);

            foreach ($products as $product) {
                fputcsv($file, [
                    $product->id,
                    $product->name,
                    $product->inventory->sku ?? 'N/A',
                    $product->inventory->cantidad_inventario ?? 0,
                    $product->inventory->umbral_aviso_inventario ?? 0,
                    $product->inventory->cantidad_inventario <= 0 ? 'Sin stock' : 'Stock bajo',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Define el tipo de vista para vistas guardadas
     */
    protected function getViewType(): string
    {
        return 'inventarios';
    }

    /**
     * Método mount para inicializar el componente
     */
    public function mount()
    {
        $this->loadSavedViews();
    }

    /**
     * Aplicar grupo de filtros con OR
     */
    protected function applyFilterGroupToQuery($query, $type, $filters)
    {
        switch($type) {
            case 'nivel_stock_sin_stock':
            case 'nivel_stock_stock_bajo':
            case 'nivel_stock_stock_normal':
            case 'nivel_stock_sobre_stock':
            case 'seguimiento_con_seguimiento':
            case 'seguimiento_sin_seguimiento':
                // Para filtros únicos, aplicar solo el primero
                $this->applyFilterToQuery($query, reset($filters));
                break;
        }
        
        return $query;
    }

    public function render()
    {
        // Crear una colección de items (productos + variantes)
        $query = Products::with(['inventory', 'variants'])
            ->whereNotNull('id_inventory');

        // Aplicar filtros
        $query = $this->applyFilterToQuery($query);

        // Ordenamiento
        if ($this->sortField === 'stock') {
            $query->join('inventories', 'products.id_inventory', '=', 'inventories.id')
                  ->orderBy('inventories.cantidad_inventario', $this->sortDirection)
                  ->select('products.*');
        } else {
            $query->orderBy($this->sortField, $this->sortDirection);
        }

        $products = $query->get();

        // Expandir productos con variantes
        $items = collect();
        foreach ($products as $product) {
            if ($product->variants && $product->variants->count() > 0) {
                // Agregar cada variante como un item separado
                foreach ($product->variants as $variant) {
                    $itemId = 'variant-' . $variant->id;
                    $stock = $variant->cantidad_inventario ?? 0;
                    $threshold = $product->inventory?->umbral_aviso_inventario ?? 0;
                    
                    // Si el filtro es 'low-stock', solo mostrar variantes con stock bajo
                    if ($this->activeFilter === 'low-stock') {
                        // Solo incluir si stock <= umbral Y umbral > 0 Y stock > 0
                        if (!($stock <= $threshold && $threshold > 0 && $stock > 0)) {
                            continue;
                        }
                    }
                    
                    // Si el filtro es 'out-of-stock', solo mostrar variantes sin stock
                    if ($this->activeFilter === 'out-of-stock') {
                        if ($stock > 0) {
                            continue;
                        }
                    }
                    
                    // Calcular cantidad comprometida (en órdenes sin preparar) con detalle de pedidos
                    $orderItems = \App\Models\Order\OrderItems::where('variant_id', $variant->id)
                        ->whereHas('order', function($query) {
                            $query->where('id_status_prepared_order', 1) // Sin preparar
                                  ->whereNull('deleted_at');
                        })
                        ->with('order')
                        ->get();
                    
                    $comprometido = $orderItems->sum('quantity');
                    $comprometidoDetalle = $orderItems->map(function($item) {
                        return [
                            'order_id' => $item->order_id,
                            'quantity' => $item->quantity,
                        ];
                    })->toArray();
                    
                    // Obtener motivos de no disponible desde movimientos de inventario
                    $noDisponibleMotivos = InventoryMovements::where('id_variant', $variant->id)
                        ->where('reason', 'like', '%(Movido a no disponible)%')
                        ->selectRaw('SUBSTRING_INDEX(reason, " (Movido", 1) as motivo, SUM(quantity) as cantidad')
                        ->groupBy('motivo')
                        ->get()
                        ->map(function($item) {
                            return [
                                'motivo' => $item->motivo,
                                'cantidad' => $item->cantidad,
                            ];
                        })
                        ->toArray();
                    
                    // Agregar todos los motivos posibles de "mover a no disponible" (incluso si están en cero)
                    $todosMotivos = [
                        'Dañado', 'Control de calidad', 'Existencias de seguridad', 'Otro'
                    ];
                    
                    $motivosConCero = [];
                    foreach ($todosMotivos as $motivo) {
                        $encontrado = collect($noDisponibleMotivos)->firstWhere('motivo', $motivo);
                        $motivosConCero[] = [
                            'motivo' => $motivo,
                            'cantidad' => $encontrado ? $encontrado['cantidad'] : 0,
                        ];
                    }
                    $noDisponibleMotivos = $motivosConCero;
                    
                    // Inicializar pendingChanges solo si no existe
                    if (!isset($this->pendingChanges[$itemId])) {
                        $this->pendingChanges[$itemId] = [
                            'disponible' => $stock,
                            'threshold' => $product->inventory?->umbral_aviso_inventario ?? 0
                        ];
                    }
                    
                    $items->push((object)[
                        'id' => $itemId,
                        'type' => 'variant',
                        'product_id' => $product->id,
                        'variant_id' => $variant->id,
                        'name' => $product->name,
                        'variant_name' => $this->getVariantName($variant),
                        'image' => $product->image ?? null,
                        'sku' => $variant->sku,
                        'barcode' => $variant->barcode,
                        'stock' => $stock,
                        'no_disponible' => $variant->cantidad_no_disponible ?? 0,
                        'no_disponible_motivos' => $noDisponibleMotivos,
                        'comprometido' => $comprometido,
                        'comprometido_detalle' => $comprometidoDetalle,
                        'threshold' => $product->inventory?->umbral_aviso_inventario ?? 0,
                        'location' => $product->inventory?->location ?? 'Sin ubicación',
                        'product' => $product,
                        'variant' => $variant,
                    ]);
                }
            } else {
                // Producto sin variantes
                $itemId = 'product-' . $product->id;
                $stock = $product->inventory?->cantidad_inventario ?? 0;
                
                // Calcular cantidad comprometida (en órdenes sin preparar) con detalle de pedidos
                $orderItems = \App\Models\Order\OrderItems::where('product_id', $product->id)
                    ->whereNull('variant_id')
                    ->whereHas('order', function($query) {
                        $query->where('id_status_prepared_order', 1) // Sin preparar
                              ->whereNull('deleted_at');
                    })
                    ->with('order')
                    ->get();
                
                $comprometido = $orderItems->sum('quantity');
                $comprometidoDetalle = $orderItems->map(function($item) {
                    return [
                        'order_id' => $item->order_id,
                        'quantity' => $item->quantity,
                    ];
                })->toArray();
                
                // Obtener motivos de no disponible desde movimientos de inventario
                $noDisponibleMotivos = \App\Models\Product\Inventory\InventoryMovements::where('id_product', $product->id)
                    ->whereNull('id_variant')
                    ->where('reason', 'like', '%(Movido a no disponible)%')
                    ->selectRaw('SUBSTRING_INDEX(reason, " (Movido", 1) as motivo, SUM(quantity) as cantidad')
                    ->groupBy('motivo')
                    ->get()
                    ->map(function($item) {
                        return [
                            'motivo' => $item->motivo,
                            'cantidad' => $item->cantidad,
                        ];
                    })
                    ->toArray();
                
                // Agregar todos los motivos posibles de "mover a no disponible" (incluso si están en cero)
                $todosMotivos = [
                    'Dañado', 'Control de calidad', 'Existencias de seguridad', 'Otro'
                ];
                
                $motivosConCero = [];
                foreach ($todosMotivos as $motivo) {
                    $encontrado = collect($noDisponibleMotivos)->firstWhere('motivo', $motivo);
                    $motivosConCero[] = [
                        'motivo' => $motivo,
                        'cantidad' => $encontrado ? $encontrado['cantidad'] : 0,
                    ];
                }
                $noDisponibleMotivos = $motivosConCero;
                
                // Inicializar pendingChanges solo si no existe
                if (!isset($this->pendingChanges[$itemId])) {
                    $this->pendingChanges[$itemId] = [
                        'disponible' => $stock,
                        'threshold' => $product->inventory?->umbral_aviso_inventario ?? 0
                    ];
                }
                
                $items->push((object)[
                    'id' => $itemId,
                    'type' => 'product',
                    'product_id' => $product->id,
                    'variant_id' => null,
                    'name' => $product->name,
                    'variant_name' => null,
                    'image' => $product->image ?? null,
                    'sku' => $product->sku ?? 'N/A',
                    'barcode' => $product->barcode ?? null,
                    'stock' => $stock,
                    'no_disponible' => $product->inventory?->cantidad_no_disponible ?? 0,
                    'no_disponible_motivos' => $noDisponibleMotivos,
                    'comprometido' => $comprometido,
                    'comprometido_detalle' => $comprometidoDetalle,
                    'threshold' => $product->inventory?->umbral_aviso_inventario ?? 0,
                    'location' => $product->inventory?->location ?? 'Sin ubicación',
                    'product' => $product,
                    'variant' => null,
                ]);
            }
        }

        // Paginar items
        $perPage = $this->perPage;
        $currentPage = request()->get('page', 1);
        $items = new \Illuminate\Pagination\LengthAwarePaginator(
            $items->forPage($currentPage, $perPage),
            $items->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // Almacenar IDs de la página actual para checkboxes
        $this->currentPageItemIds = $items->pluck('id')->toArray();

        // Productos con stock bajo para el widget
        $lowStockCount = InventoryService::getLowStockProducts()->count();

        return view('livewire.producto.inventories', [
            'items' => $items,
            'lowStockCount' => $lowStockCount,
        ]);
    }

    private function getVariantName($variant)
    {
        if (!$variant->name_variant || !$variant->valores_variante) {
            return 'Variante #' . $variant->id;
        }

        $valores = is_array($variant->valores_variante) 
            ? $variant->valores_variante 
            : json_decode($variant->valores_variante, true);

        if (!is_array($valores)) {
            return 'Variante #' . $variant->id;
        }

        return implode(' • ', $valores);
    }
}
