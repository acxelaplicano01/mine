<?php

namespace App\Livewire\Order;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Order\Orders as OrderModel;
use App\Models\Product\Products;
use App\Models\Customer\Customers;
use App\Livewire\Traits\HasSavedViews;

#[Layout('components.layouts.collapsable')]
class Orders extends Component
{
    use WithPagination, HasSavedViews;

    // Propiedades para la tabla
    public $search = '';
    public $perPage = 10;
    public $sortField = 'id';
    public $sortDirection = 'asc';
    public $selected = [];
    public $selectAll = false;
    public $currentOrderIds = []; // IDs de los pedidos en la página actual
    public $showOnlySelected = false; // Mostrar solo elementos seleccionados
    
    // Propiedades para exportación
    public $showExportModal = false;
    public $exportOption = 'current_page'; // current_page, all, selected, search, filtered
    public $exportFormat = 'csv'; // csv, plain_csv

    protected $listeners = [
        'selectedUpdated' => 'handleSelectedUpdated',
        'sortUpdated' => 'handleSortUpdated',
    ];
    // Eliminar la propiedad $orders, ya que se debe usar la paginación directamente en render()
    // Propiedades del formulario
    public $orderId;
    public $user_id;
    public $product_id;
    public $id_customer;
    public $quantity = 1;
    public $total_price = 0;
    public $subtotal_price = 0;
    public $note;
    public $id_status_order;

    // Propiedades de control
    public $isEditing = false;
    public $showModal = false;
    public $showFilterDropdown = false;

    protected $rules = [
        'product_id' => 'required|exists:products,id',
        'id_customer' => 'required|exists:customers,id',
        'quantity' => 'required|integer|min:1',
        'total_price' => 'required|numeric|min:0',
        'note' => 'nullable|string',
    ];

    protected $messages = [
        'product_id.required' => 'El producto es obligatorio.',
        'id_customer.required' => 'El cliente es obligatorio.',
        'quantity.required' => 'La cantidad es obligatoria.',
        'quantity.min' => 'La cantidad debe ser al menos 1.',
        'total_price.required' => 'El precio total es obligatorio.',
    ];

    // Método para ordenar por columna
    public function sortBy($field)
    {
        // Mantener el nombre de visualización, el mapeo se hace en render()
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        
        $this->sortField = $field;
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            // Obtener todos los IDs de la consulta actual (todas las páginas)
            $allIds = $this->getAllOrderIds();
            $this->selected = array_map('intval', $allIds);
        } else {
            $this->selected = [];
            $this->showOnlySelected = false; // Desactivar filtro cuando no hay selección
        }
    }
    
    /**
     * Obtener todos los IDs de pedidos según filtros actuales
     */
    protected function getAllOrderIds()
    {
        return OrderModel::select('orders.id')
            ->leftJoin('customers', 'orders.id_customer', '=', 'customers.id')
            ->leftJoin('markets', 'orders.id_market', '=', 'markets.id')
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->whereHas('customer', function($q2) {
                        $q2->where('name', 'like', '%' . $this->search . '%')
                          ->orWhere('email', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('items.product', function($q2) {
                        $q2->where('name', 'like', '%' . $this->search . '%');
                    })
                    ->orWhere('orders.note', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->activeFilter === 'no_pagados', function($query) {
                $query->where('orders.id_status_order', '!=', 1);
            })
            ->when($this->activeFilter === 'no_preparados', function($query) {
                $query->where('orders.id_status_order', 2);
            })
            ->when($this->showOnlySelected && count($this->selected) > 0, function($query) {
                $query->whereIn('orders.id', $this->selected);
            })
            ->when(count($this->activeFilters) > 0, function($query) {
                // Agrupar filtros por tipo para aplicar OR dentro del mismo tipo
                $filtersByType = [];
                foreach($this->activeFilters as $filterId => $filter) {
                    $filtersByType[$filter['type']][$filterId] = $filter;
                }
                
                // Aplicar cada grupo de filtros con OR dentro del grupo
                foreach($filtersByType as $type => $filters) {
                    $this->applyFilterGroupToQuery($query, $type, $filters);
                }
            })
            ->pluck('id')
            ->toArray();
    }

    public function updatedSelected($value)
    {
        // Solo normalizar si hay valores no enteros
        $needsNormalization = false;
        foreach ($this->selected as $id) {
            if (!is_int($id)) {
                $needsNormalization = true;
                break;
            }
        }
        
        if ($needsNormalization) {
            $this->selected = array_values(array_unique(array_map('intval', array_filter($this->selected, fn($id) => (int)$id > 0))));
        }
        $this->selectAll = count($this->selected) === count($this->currentOrderIds) && count($this->currentOrderIds) > 0;
        
        // Desactivar "mostrar solo seleccionados" si no hay selección
        if (count($this->selected) === 0) {
            $this->showOnlySelected = false;
        }
    }

    public function handleSelectedUpdated($selected)
    {
        $this->selected = $selected;
        $this->selectAll = count($this->selected) === count($this->currentOrderIds) && count($this->currentOrderIds) > 0;
    }

    public function handleSortUpdated($sortField, $sortDirection)
    {
        $this->sortField = $sortField;
        $this->sortDirection = $sortDirection;
    }

    public function mount()
    {
        $this->user_id = auth()->id();
        $this->loadSavedViews();
    }
    
    /**
     * Abrir modal de exportación
     */
    public function openExportModal()
    {
        $this->showExportModal = true;
        $this->exportOption = 'current_page';
        $this->exportFormat = 'csv';
    }
    
    /**
     * Cerrar modal de exportación
     */
    public function closeExportModal()
    {
        $this->showExportModal = false;
    }
    
    /**
     * Exportar pedidos
     */
    public function exportOrders()
    {
        $orders = $this->getOrdersForExport();
        
        if ($orders->isEmpty()) {
            session()->flash('error', 'No hay pedidos para exportar');
            return;
        }
        
        $filename = 'pedidos_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($orders) {
            $file = fopen('php://output', 'w');
            
            // BOM para UTF-8 en Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Encabezados
            fputcsv($file, [
                'Pedido',
                'Fecha',
                'Cliente',
                'Email',
                'Canal',
                'Total',
                'Estado del pago',
                'Estado de preparación',
                'Artículos',
                'Forma de entrega',
            ], $this->exportFormat === 'csv' ? ',' : ';');
            
            // Datos
            foreach ($orders as $order) {
                fputcsv($file, [
                    '#' . str_pad($order->id, 4, '0', STR_PAD_LEFT),
                    $order->created_at->format('d/m/Y H:i'),
                    $order->customer?->name ?? 'Sin cliente',
                    $order->customer?->email ?? '',
                    $order->market?->name ?? '—',
                    number_format($order->total_price, 2) . ' L',
                    $order->statusOrder?->name ?? 'Sin estado',
                    $order->statusPreparedOrder?->name ?? '—',
                    $order->items->count(),
                    $order->envio ? 'Envío' : 'Recogida',
                ], $this->exportFormat === 'csv' ? ',' : ';');
            }
            
            fclose($file);
        };
        
        $this->closeExportModal();
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Obtener pedidos según opción de exportación
     */
    protected function getOrdersForExport()
    {
        $query = OrderModel::with(['customer', 'items', 'market', 'statusOrder', 'statusPreparedOrder', 'envio'])
            ->leftJoin('customers', 'orders.id_customer', '=', 'customers.id')
            ->leftJoin('markets', 'orders.id_market', '=', 'markets.id')
            ->select('orders.*');
        
        // Aplicar filtros según la opción seleccionada
        switch ($this->exportOption) {
            case 'current_page':
                $query->whereIn('orders.id', $this->currentOrderIds);
                break;
                
            case 'all':
                // Sin filtros adicionales, todos los pedidos
                break;
                
            case 'selected':
                if (empty($this->selected)) {
                    return collect();
                }
                $query->whereIn('orders.id', $this->selected);
                break;
                
            case 'search':
                if (empty($this->search)) {
                    return collect();
                }
                $query->where(function($q) {
                    $q->whereHas('customer', function($q2) {
                        $q2->where('name', 'like', '%' . $this->search . '%')
                          ->orWhere('email', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('items.product', function($q2) {
                        $q2->where('name', 'like', '%' . $this->search . '%');
                    })
                    ->orWhere('orders.note', 'like', '%' . $this->search . '%');
                });
                break;
                
            case 'filtered':
                // Los filtros se aplicarán automáticamente después del switch
                // Esta opción simplemente no aplica restricciones adicionales
                break;
        }
        
        // Aplicar filtros activos del sistema
        $query->when($this->activeFilter === 'no_pagados', function($q) {
            $q->where('orders.id_status_order', '!=', 1);
        })
        ->when($this->activeFilter === 'no_preparados', function($q) {
            $q->where('orders.id_status_order', 2);
        })
        ->when(count($this->activeFilters) > 0, function($q) {
            $filtersByType = [];
            foreach($this->activeFilters as $filterId => $filter) {
                $filtersByType[$filter['type']][$filterId] = $filter;
            }
            
            foreach($filtersByType as $type => $filters) {
                $this->applyFilterGroupToQuery($q, $type, $filters);
            }
        });
        
        return $query->get();
    }
    
    /**
     * Define el tipo de vista para este componente
     */
    protected function getViewType(): string
    {
        return 'orders';
    }

    /**
     * Imprimir notas de entrega de los pedidos seleccionados
     */
    public function printDeliveryNotes()
    {
        if (empty($this->selected)) {
            return;
        }
        
        // Redirigir a una nueva ventana con la vista de impresión
        $ids = implode(',', $this->selected);
        $this->dispatch('openPrintWindow', url: route('orders.print', ['ids' => $ids]));
    }

    /**
     * Marcar pedidos seleccionados con un estado
     */
    public function markAsStatus($status)
    {
        if (empty($this->selected)) {
            return;
        }

        $statusMap = [
            'no_preparado' => 1,
            'en_preparacion' => 2,
            'preparado' => 3,
            'en_espera' => 4,
        ];

        if (!isset($statusMap[$status])) {
            return;
        }

        OrderModel::whereIn('id', $this->selected)
            ->update(['id_status_prepared_order' => $statusMap[$status]]);

        $this->selected = [];
        $this->selectAll = false;
        
        session()->flash('message', 'Pedidos actualizados correctamente');
    }
    
    /**
     * Aplicar filtro específico a la consulta de orders
     */
    protected function applyFilterToQuery($query, $filter)
    {
        switch($filter['type']) {
            case 'estado_pago_pagado':
                $query->where('orders.id_status_order', 1);
                break;
            case 'estado_pago_pendiente':
                $query->where('orders.id_status_order', 2);
                break;
            case 'estado_pago_no_pagado':
                $query->where('orders.id_status_order', '!=', 1);
                break;
            case 'estado_preparacion_no_preparado':
                $query->where('orders.id_status_prepared_order', 2);
                break;
            case 'cliente':
                if(isset($filter['value'])) {
                    $query->where('orders.id_customer', $filter['value']);
                }
                break;
            case 'producto':
                if(isset($filter['value'])) {
                    $query->whereHas('items', function($q) use ($filter) {
                        $q->where('product_id', $filter['value']);
                    });
                }
                break;
        }
        
        return $query;
    }

    /**
     * Aplicar un grupo de filtros del mismo tipo con OR
     */
    protected function applyFilterGroupToQuery($query, $type, $filters)
    {
        switch($type) {
            case 'estado_pago_pagado':
            case 'estado_pago_pendiente':
            case 'estado_pago_no_pagado':
            case 'estado_preparacion_no_preparado':
                // Para estos filtros únicos, solo aplicar el primero
                $this->applyFilterToQuery($query, reset($filters));
                break;
                
            case 'cliente':
                // Agrupar múltiples clientes con OR
                $customerIds = array_filter(array_column($filters, 'value'));
                if (!empty($customerIds)) {
                    $query->whereIn('orders.id_customer', $customerIds);
                }
                break;
                
            case 'producto':
                // Agrupar múltiples productos con OR
                $productIds = array_filter(array_column($filters, 'value'));
                if (!empty($productIds)) {
                    $query->whereHas('items', function($q) use ($productIds) {
                        $q->whereIn('product_id', $productIds);
                    });
                }
                break;
        }
        
        return $query;
    }
    
    public function toggleFilterDropdown()
    {
        $this->showFilterDropdown = !$this->showFilterDropdown;
        $this->filterSearch = '';
    }

    public function render()
    {
        // Mapeo de columnas de visualización a columnas reales de la base de datos
        $columnMap = [
            'fecha' => 'orders.created_at',
            'total' => 'orders.total_price',
            'estado_pago' => 'orders.id_status_order',
            'estado_preparacion' => 'orders.id_status_prepared_order',
            'cliente' => 'customers.name',
            'canal' => 'markets.name',
            'articulos' => 'items_count',
            'estado_entrega' => 'orders.id_status_order',
            'forma_entrega' => 'orders.id_envio',
        ];
        
        // Obtener el nombre real de la columna para la consulta SQL
        $dbSortField = $columnMap[$this->sortField] ?? ('orders.' . $this->sortField);
        
        $orders = OrderModel::select('orders.*')
            ->selectSub(function ($query) {
                $query->selectRaw('COUNT(*)')
                    ->from('order_items')
                    ->whereColumn('order_items.order_id', 'orders.id');
            }, 'items_count')
            ->with(['customer', 'items.product', 'items.variant', 'user', 'statusOrder', 'statusPreparedOrder', 'envio', 'market'])
            ->leftJoin('customers', 'orders.id_customer', '=', 'customers.id')
            ->leftJoin('markets', 'orders.id_market', '=', 'markets.id')
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->whereHas('customer', function($q2) {
                        $q2->where('name', 'like', '%' . $this->search . '%')
                          ->orWhere('email', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('items.product', function($q2) {
                        $q2->where('name', 'like', '%' . $this->search . '%');
                    })
                    ->orWhere('orders.note', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->activeFilter === 'no_pagados', function($query) {
                $query->where('orders.id_status_order', '!=', 1);
            })
            ->when($this->activeFilter === 'no_preparados', function($query) {
                $query->where('orders.id_status_order', 2);
            })
            ->when($this->showOnlySelected && count($this->selected) > 0, function($query) {
                $query->whereIn('orders.id', $this->selected);
            })
            ->when(count($this->activeFilters) > 0, function($query) {
                // Agrupar filtros por tipo para aplicar OR dentro del mismo tipo
                $filtersByType = [];
                foreach($this->activeFilters as $filterId => $filter) {
                    $filtersByType[$filter['type']][$filterId] = $filter;
                }
                
                // Aplicar cada grupo de filtros con OR dentro del grupo
                foreach($filtersByType as $type => $filters) {
                    $this->applyFilterGroupToQuery($query, $type, $filters);
                }
            })
            ->orderBy($dbSortField, $this->sortDirection)
            ->paginate($this->perPage);

        $products = Products::all();
        $customers = Customers::all();

        // Actualizar los IDs de la página actual para los checkboxes
        $this->currentOrderIds = $orders->pluck('id')->toArray();
        
        // Si mostramos solo seleccionados, asegurar que todos estén marcados correctamente
        if ($this->showOnlySelected && count($this->selected) > 0) {
            // Normalizar selected a enteros para comparación consistente
            $this->selected = array_map('intval', array_values($this->selected));
            $this->currentOrderIds = array_map('intval', $this->currentOrderIds);
        }

        return view('livewire.order.orders', [
            'orders' => $orders,
            'products' => $products,
            'customers' => $customers,
        ]);
    }

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
        $this->isEditing = false;
    }

    public function edit($id)
    {
        $order = OrderModel::findOrFail($id);
        
        $this->orderId = $order->id;
        $this->user_id = $order->user_id;
        $this->product_id = $order->product_id;
        $this->id_customer = $order->id_customer;
        $this->quantity = $order->quantity;
        $this->total_price = $order->total_price;
        $this->subtotal_price = $order->subtotal_price;
        $this->note = $order->note;
        $this->id_status_order = $order->id_status_order;
        
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function store()
    {
        $this->validate();

        OrderModel::create([
            'user_id' => $this->user_id,
            'product_id' => $this->product_id,
            'id_customer' => $this->id_customer,
            'quantity' => $this->quantity,
            'total_price' => $this->total_price,
            'subtotal_price' => $this->subtotal_price,
            'note' => $this->note,
            'id_status_order' => $this->id_status_order,
        ]);

        session()->flash('message', 'Pedido creado exitosamente');
        
        $this->resetForm();
        $this->showModal = false;
    }

    public function update()
    {
        $this->validate();

        $order = OrderModel::findOrFail($this->orderId);
        
        $order->update([
            'product_id' => $this->product_id,
            'id_customer' => $this->id_customer,
            'quantity' => $this->quantity,
            'total_price' => $this->total_price,
            'subtotal_price' => $this->subtotal_price,
            'note' => $this->note,
            'id_status_order' => $this->id_status_order,
        ]);

        session()->flash('message', 'Pedido actualizado exitosamente');

        $this->resetForm();
        $this->showModal = false;
    }

    public function delete($id)
    {
        $order = OrderModel::findOrFail($id);
        $order->delete();

        session()->flash('message', 'Pedido eliminado exitosamente');
    }

    public function cancel()
    {
        $this->resetForm();
        $this->showModal = false;
    }

    public function updatedProductId($value)
    {
        if ($value) {
            $product = Products::find($value);
            if ($product) {
                $this->calculateTotal();
            }
        }
    }

    public function updatedQuantity()
    {
        $this->calculateTotal();
    }

    private function calculateTotal()
    {
        if ($this->product_id && $this->quantity) {
            $product = Products::find($this->product_id);
            if ($product) {
                $this->subtotal_price = $product->price * $this->quantity;
                $this->total_price = $this->subtotal_price; // Aquí se pueden agregar impuestos, descuentos, etc.
            }
        }
    }

    private function resetForm()
    {
        $this->orderId = null;
        $this->product_id = null;
        $this->id_customer = null;
        $this->quantity = 1;
        $this->total_price = 0;
        $this->subtotal_price = 0;
        $this->note = null;
        $this->id_status_order = null;
        $this->isEditing = false;
        $this->resetErrorBag();
    }
}
