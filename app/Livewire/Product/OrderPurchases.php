<?php

namespace App\Livewire\Product;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Product\OrderPurchase\OrderPurchases as OrderPurchaseModel;
use App\Models\Product\Products;
use App\Models\Distribuidor\Distribuidores;
use App\Livewire\Traits\HasSavedViews;

#[Layout('components.layouts.collapsable')]
class OrderPurchases extends Component
{
    use WithPagination, HasSavedViews;

    // Propiedades para la tabla
    public $search = '';
    public $perPage = 10;
    public $sortField = 'id';
    public $sortDirection = 'asc';
    public $selected = [];
    public $selectAll = false;
    public $currentOrderIds = [];
    public $showOnlySelected = false;
    
    // Propiedades para exportación
    public $showExportModal = false;
    public $exportOption = 'current_page';
    public $exportFormat = 'csv';

    protected $listeners = [
        'selectedUpdated' => 'handleSelectedUpdated',
        'sortUpdated' => 'handleSortUpdated',
    ];

    // Propiedades del formulario
    public $orderId;
    public $id_distribuidor;
    public $id_sucursal_destino;
    public $id_condiciones_pago;
    public $id_moneda_del_distribuidor;
    public $fecha_llegada_estimada;
    public $id_empresa_trasnportista;
    public $numero_guia;
    public $id_product;
    public $numero_referencia;
    public $nota_al_distribuidor;

    // Propiedades de control
    public $isEditing = false;
    public $showModal = false;
    public $showFilterDropdown = false;

    protected $rules = [
        'id_distribuidor' => 'required|exists:distribuidores,id',
        'id_product' => 'required|exists:products,id',
        'fecha_llegada_estimada' => 'nullable|date',
        'numero_referencia' => 'nullable|string',
        'nota_al_distribuidor' => 'nullable|string',
    ];

    protected $messages = [
        'id_distribuidor.required' => 'El distribuidor es obligatorio.',
        'id_product.required' => 'El producto es obligatorio.',
    ];

    // Método para ordenar por columna
    public function sortBy($field)
    {
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
            $allIds = $this->getAllOrderIds();
            $this->selected = array_map('intval', $allIds);
        } else {
            $this->selected = [];
            $this->showOnlySelected = false;
        }
    }
    
    protected function getAllOrderIds()
    {
        return OrderPurchaseModel::select('order_purchases.id')
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('order_purchases.numero_referencia', 'like', '%' . $this->search . '%')
                      ->orWhere('order_purchases.numero_guia', 'like', '%' . $this->search . '%')
                      ->orWhere('order_purchases.nota_al_distribuidor', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->showOnlySelected && count($this->selected) > 0, function($query) {
                $query->whereIn('order_purchases.id', $this->selected);
            })
            ->when(count($this->activeFilters) > 0, function($query) {
                $filtersByType = [];
                foreach($this->activeFilters as $filterId => $filter) {
                    $filtersByType[$filter['type']][$filterId] = $filter;
                }
                
                foreach($filtersByType as $type => $filters) {
                    $this->applyFilterGroupToQuery($query, $type, $filters);
                }
            })
            ->pluck('id')
            ->toArray();
    }

    public function updatedSelected($value)
    {
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
        $this->loadSavedViews();
    }
    
    public function openExportModal()
    {
        $this->showExportModal = true;
        $this->exportOption = 'current_page';
        $this->exportFormat = 'csv';
    }
    
    public function closeExportModal()
    {
        $this->showExportModal = false;
    }
    
    public function exportOrders()
    {
        $orders = $this->getOrdersForExport();
        
        if ($orders->isEmpty()) {
            session()->flash('error', 'No hay órdenes de compra para exportar');
            return;
        }
        
        $filename = 'ordenes_compra_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($orders) {
            $file = fopen('php://output', 'w');
            
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, [
                'ID',
                'Referencia',
                'Distribuidor',
                'Producto',
                'Fecha estimada',
                'Guía',
                'Fecha creación',
            ], $this->exportFormat === 'csv' ? ',' : ';');
            
            foreach ($orders as $order) {
                fputcsv($file, [
                    '#' . str_pad($order->id, 4, '0', STR_PAD_LEFT),
                    $order->numero_referencia ?? '—',
                    $order->distribuidor->name ?? 'Sin distribuidor',
                    $order->product->name ?? 'Sin producto',
                    $order->fecha_llegada_estimada ? date('d/m/Y', strtotime($order->fecha_llegada_estimada)) : '—',
                    $order->numero_guia ?? '—',
                    $order->created_at->format('d/m/Y H:i'),
                ], $this->exportFormat === 'csv' ? ',' : ';');
            }
            
            fclose($file);
        };
        
        $this->closeExportModal();
        
        return response()->stream($callback, 200, $headers);
    }
    
    protected function getOrdersForExport()
    {
        $query = OrderPurchaseModel::with(['distribuidor', 'product']);
        
        switch ($this->exportOption) {
            case 'current_page':
                $query->whereIn('order_purchases.id', $this->currentOrderIds);
                break;
                
            case 'all':
                break;
                
            case 'selected':
                if (empty($this->selected)) {
                    return collect();
                }
                $query->whereIn('order_purchases.id', $this->selected);
                break;
                
            case 'search':
                if (empty($this->search)) {
                    return collect();
                }
                $query->where(function($q) {
                    $q->where('numero_referencia', 'like', '%' . $this->search . '%')
                      ->orWhere('numero_guia', 'like', '%' . $this->search . '%')
                      ->orWhere('nota_al_distribuidor', 'like', '%' . $this->search . '%');
                });
                break;
                
            case 'filtered':
                break;
        }
        
        $query->when(count($this->activeFilters) > 0, function($q) {
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
    
    protected function getViewType(): string
    {
        return 'order_purchases';
    }

    protected function applyFilterToQuery($query, $filter)
    {
        switch($filter['type']) {
            case 'distribuidor':
                if(isset($filter['value'])) {
                    $query->where('order_purchases.id_distribuidor', $filter['value']);
                }
                break;
            case 'producto':
                if(isset($filter['value'])) {
                    $query->where('order_purchases.id_product', $filter['value']);
                }
                break;
        }
        
        return $query;
    }

    protected function applyFilterGroupToQuery($query, $type, $filters)
    {
        switch($type) {
            case 'distribuidor':
                $distributorIds = array_filter(array_column($filters, 'value'));
                if (!empty($distributorIds)) {
                    $query->whereIn('order_purchases.id_distribuidor', $distributorIds);
                }
                break;
                
            case 'producto':
                $productIds = array_filter(array_column($filters, 'value'));
                if (!empty($productIds)) {
                    $query->whereIn('order_purchases.id_product', $productIds);
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
    
    public function marcarComoPedido($orderId)
    {
        $order = OrderPurchaseModel::findOrFail($orderId);
        
        if ($order->estado === 'borrador') {
            $order->update(['estado' => 'solicitado']);
            session()->flash('message', 'Orden marcada como pedido exitosamente');
        }
    }
    
    public function recibirInventario($orderId)
    {
        // Esta funcionalidad se implementará en otro componente
        return redirect()->route('order_purchases_receive', ['id' => $orderId]);
    }

    public function render()
    {
        $columnMap = [
            'fecha' => 'order_purchases.created_at',
            'referencia' => 'order_purchases.numero_referencia',
            'distribuidor' => 'order_purchases.id_distribuidor',
            'producto' => 'order_purchases.id_product',
            'fecha_estimada' => 'order_purchases.fecha_llegada_estimada',
        ];
        
        $dbSortField = $columnMap[$this->sortField] ?? ('order_purchases.' . $this->sortField);
        
        $orders = OrderPurchaseModel::select('order_purchases.*')
            ->with(['distribuidor', 'product'])
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('order_purchases.numero_referencia', 'like', '%' . $this->search . '%')
                      ->orWhere('order_purchases.numero_guia', 'like', '%' . $this->search . '%')
                      ->orWhere('order_purchases.nota_al_distribuidor', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->activeFilter === 'borrador', function($query) {
                $query->where('order_purchases.estado', 'borrador');
            })
            ->when($this->activeFilter === 'solicitados', function($query) {
                $query->where('order_purchases.estado', 'solicitado');
            })
            ->when($this->activeFilter === 'recibidos', function($query) {
                $query->where('order_purchases.estado', 'recibido');
            })
            ->when($this->activeFilter === 'cancelados', function($query) {
                $query->where('order_purchases.estado', 'cancelado');
            })
            ->when($this->showOnlySelected && count($this->selected) > 0, function($query) {
                $query->whereIn('order_purchases.id', $this->selected);
            })
            ->when(count($this->activeFilters) > 0, function($query) {
                $filtersByType = [];
                foreach($this->activeFilters as $filterId => $filter) {
                    $filtersByType[$filter['type']][$filterId] = $filter;
                }
                
                foreach($filtersByType as $type => $filters) {
                    $this->applyFilterGroupToQuery($query, $type, $filters);
                }
            })
            ->orderBy($dbSortField, $this->sortDirection)
            ->paginate($this->perPage);

        $products = Products::all();
        $distribuidores = Distribuidores::all();

        $this->currentOrderIds = $orders->pluck('id')->toArray();
        
        if ($this->showOnlySelected && count($this->selected) > 0) {
            $this->selected = array_map('intval', array_values($this->selected));
            $this->currentOrderIds = array_map('intval', $this->currentOrderIds);
        }

        return view('livewire.producto.order-purchase.orders-purchases', [
            'orders' => $orders,
            'products' => $products,
            'distribuidores' => $distribuidores,
        ]);
    }

    public function create()
    {
        return redirect()->route('orders_purchases_create');
    }

    public function edit($id)
    {
        $order = OrderPurchaseModel::findOrFail($id);
        
        $this->orderId = $order->id;
        $this->id_distribuidor = $order->id_distribuidor;
        $this->id_sucursal_destino = $order->id_sucursal_destino;
        $this->id_condiciones_pago = $order->id_condiciones_pago;
        $this->id_moneda_del_distribuidor = $order->id_moneda_del_distribuidor;
        $this->fecha_llegada_estimada = $order->fecha_llegada_estimada;
        $this->id_empresa_trasnportista = $order->id_empresa_trasnportista;
        $this->numero_guia = $order->numero_guia;
        $this->id_product = $order->id_product;
        $this->numero_referencia = $order->numero_referencia;
        $this->nota_al_distribuidor = $order->nota_al_distribuidor;
        
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function store()
    {
        $this->validate();

        OrderPurchaseModel::create([
            'id_distribuidor' => $this->id_distribuidor,
            'id_sucursal_destino' => $this->id_sucursal_destino,
            'id_condiciones_pago' => $this->id_condiciones_pago,
            'id_moneda_del_distribuidor' => $this->id_moneda_del_distribuidor,
            'fecha_llegada_estimada' => $this->fecha_llegada_estimada,
            'id_empresa_trasnportista' => $this->id_empresa_trasnportista,
            'numero_guia' => $this->numero_guia,
            'id_product' => $this->id_product,
            'numero_referencia' => $this->numero_referencia,
            'nota_al_distribuidor' => $this->nota_al_distribuidor,
        ]);

        session()->flash('message', 'Orden de compra creada exitosamente');
        
        $this->resetForm();
        $this->showModal = false;
    }

    public function update()
    {
        $this->validate();

        $order = OrderPurchaseModel::findOrFail($this->orderId);
        
        $order->update([
            'id_distribuidor' => $this->id_distribuidor,
            'id_sucursal_destino' => $this->id_sucursal_destino,
            'id_condiciones_pago' => $this->id_condiciones_pago,
            'id_moneda_del_distribuidor' => $this->id_moneda_del_distribuidor,
            'fecha_llegada_estimada' => $this->fecha_llegada_estimada,
            'id_empresa_trasnportista' => $this->id_empresa_trasnportista,
            'numero_guia' => $this->numero_guia,
            'id_product' => $this->id_product,
            'numero_referencia' => $this->numero_referencia,
            'nota_al_distribuidor' => $this->nota_al_distribuidor,
        ]);

        session()->flash('message', 'Orden de compra actualizada exitosamente');

        $this->resetForm();
        $this->showModal = false;
    }

    public function delete($id)
    {
        $order = OrderPurchaseModel::findOrFail($id);
        $order->delete();

        session()->flash('message', 'Orden de compra eliminada exitosamente');
    }

    public function cancel()
    {
        $this->resetForm();
        $this->showModal = false;
    }

    private function resetForm()
    {
        $this->orderId = null;
        $this->id_distribuidor = null;
        $this->id_sucursal_destino = null;
        $this->id_condiciones_pago = null;
        $this->id_moneda_del_distribuidor = null;
        $this->fecha_llegada_estimada = null;
        $this->id_empresa_trasnportista = null;
        $this->numero_guia = null;
        $this->id_product = null;
        $this->numero_referencia = null;
        $this->nota_al_distribuidor = null;
        $this->isEditing = false;
        $this->resetErrorBag();
    }
}
