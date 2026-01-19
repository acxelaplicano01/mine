<?php

namespace App\Livewire\Order;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Order\AbandonedOrders as AbandonedOrderModel;
use App\Models\Product\Products;
use App\Models\Customer\Customers;
use App\Livewire\Traits\HasSavedViews;
use Illuminate\Support\Facades\Mail;

#[Layout('components.layouts.collapsable')]
class AbandonedOrders extends Component
{
    use WithPagination, HasSavedViews;

    // Propiedades para la tabla
    public $search = '';
    public $perPage = 10;
    public $sortField = 'id';
    public $sortDirection = 'desc';
    public $selected = [];
    public $selectAll = false;
    public $currentAbandonedOrderIds = [];
    public $showOnlySelected = false;
    
    // Propiedades para exportación
    public $showExportModal = false;
    public $exportOption = 'current_page';
    public $exportFormat = 'csv';

    protected $listeners = [
        'selectedUpdated' => 'handleSelectedUpdated',
        'sortUpdated' => 'handleSortUpdated',
    ];

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
            $allIds = $this->getAllAbandonedOrderIds();
            $this->selected = array_map('intval', $allIds);
        } else {
            $this->selected = [];
            $this->showOnlySelected = false;
        }
    }
    
    protected function getAllAbandonedOrderIds()
    {
        return AbandonedOrderModel::select('abandoned_orders.id')
            ->leftJoin('users', 'abandoned_orders.user_id', '=', 'users.id')
            ->leftJoin('customers', 'abandoned_orders.id_customer', '=', 'customers.id')
            ->leftJoin('products', 'abandoned_orders.product_id', '=', 'products.id')
            ->leftJoin('markets', 'abandoned_orders.id_market', '=', 'markets.id')
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->whereHas('user', function($q2) {
                        $q2->where('name', 'like', '%' . $this->search . '%')
                          ->orWhere('email', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('customer', function($q2) {
                        $q2->where('nombre', 'like', '%' . $this->search . '%')
                          ->orWhere('email', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('product', function($q2) {
                        $q2->where('name', 'like', '%' . $this->search . '%');
                    })
                    ->orWhere('abandoned_orders.note', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->showOnlySelected && count($this->selected) > 0, function($query) {
                $query->whereIn('abandoned_orders.id', $this->selected);
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
        $this->selectAll = count($this->selected) === count($this->currentAbandonedOrderIds) && count($this->currentAbandonedOrderIds) > 0;
        
        if (count($this->selected) === 0) {
            $this->showOnlySelected = false;
        }
    }

    public function handleSelectedUpdated($selected)
    {
        $this->selected = $selected;
        $this->selectAll = count($this->selected) === count($this->currentAbandonedOrderIds) && count($this->currentAbandonedOrderIds) > 0;
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
    
    public function exportAbandonedOrders()
    {
        $abandonedOrders = $this->getAbandonedOrdersForExport();
        
        if ($abandonedOrders->isEmpty()) {
            session()->flash('error', 'No hay carritos abandonados para exportar');
            return;
        }
        
        $filename = 'carritos_abandonados_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($abandonedOrders) {
            $file = fopen('php://output', 'w');
            
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, [
                'ID',
                'Fecha',
                'Usuario',
                'Email',
                'Producto',
                'Cantidad',
                'Total',
                'Mercado',
                'Email enviado',
            ], $this->exportFormat === 'csv' ? ',' : ';');
            
            foreach ($abandonedOrders as $order) {
                fputcsv($file, [
                    '#' . str_pad($order->id, 4, '0', STR_PAD_LEFT),
                    $order->created_at->format('d/m/Y H:i'),
                    $order->user?->name ?? 'Sin usuario',
                    $order->user?->email ?? '',
                    $order->product?->name ?? 'Sin producto',
                    $order->quantity,
                    number_format($order->total_price, 2) . ' L',
                    $order->market?->name ?? '—',
                    $order->email_sent_at ? $order->email_sent_at->format('d/m/Y H:i') : 'No',
                ], $this->exportFormat === 'csv' ? ',' : ';');
            }
            
            fclose($file);
        };
        
        $this->closeExportModal();
        
        return response()->stream($callback, 200, $headers);
    }
    
    protected function getAbandonedOrdersForExport()
    {
        $query = AbandonedOrderModel::with(['user', 'customer', 'product', 'market'])
            ->leftJoin('users', 'abandoned_orders.user_id', '=', 'users.id')
            ->leftJoin('customers', 'abandoned_orders.id_customer', '=', 'customers.id')
            ->leftJoin('products', 'abandoned_orders.product_id', '=', 'products.id')
            ->leftJoin('markets', 'abandoned_orders.id_market', '=', 'markets.id')
            ->select('abandoned_orders.*');
        
        switch ($this->exportOption) {
            case 'current_page':
                $query->whereIn('abandoned_orders.id', $this->currentAbandonedOrderIds);
                break;
                
            case 'all':
                break;
                
            case 'selected':
                if (empty($this->selected)) {
                    return collect();
                }
                $query->whereIn('abandoned_orders.id', $this->selected);
                break;
                
            case 'search':
                if (empty($this->search)) {
                    return collect();
                }
                $query->where(function($q) {
                    $q->whereHas('user', function($q2) {
                        $q2->where('name', 'like', '%' . $this->search . '%')
                          ->orWhere('email', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('customer', function($q2) {
                        $q2->where('nombre', 'like', '%' . $this->search . '%')
                          ->orWhere('email', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('product', function($q2) {
                        $q2->where('name', 'like', '%' . $this->search . '%');
                    })
                    ->orWhere('abandoned_orders.note', 'like', '%' . $this->search . '%');
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
        return 'abandoned_orders';
    }

    /**
     * Enviar email de recuperación de carrito a los seleccionados
     */
    public function sendRecoveryEmails()
    {
        if (empty($this->selected)) {
            return;
        }
        
        $abandonedOrders = AbandonedOrderModel::with('user')
            ->whereIn('id', $this->selected)
            ->whereNotNull('user_id')
            ->get();
        
        $sentCount = 0;
        foreach ($abandonedOrders as $order) {
            if ($order->user && $order->user->email) {
                // Generar token si no existe
                $order->generateCartToken();
                
                // Aquí iría la lógica real de envío de email
                // Mail::to($order->user->email)->send(new CartRecoveryMail($order));
                
                $order->email_sent_at = now();
                $order->save();
                $sentCount++;
            }
        }
        
        $this->selected = [];
        $this->selectAll = false;
        
        session()->flash('message', "Se enviaron {$sentCount} correos de recuperación");
    }

    /**
     * Eliminar carritos abandonados seleccionados
     */
    public function deleteSelected()
    {
        if (empty($this->selected)) {
            return;
        }
        
        AbandonedOrderModel::whereIn('id', $this->selected)->delete();

        $this->selected = [];
        $this->selectAll = false;
        
        session()->flash('message', 'Carritos abandonados eliminados correctamente');
    }
    
    protected function applyFilterToQuery($query, $filter)
    {
        switch($filter['type']) {
            case 'usuario':
                if(isset($filter['value'])) {
                    $query->where('abandoned_orders.user_id', $filter['value']);
                }
                break;
            case 'cliente':
                if(isset($filter['value'])) {
                    $query->where('abandoned_orders.id_customer', $filter['value']);
                }
                break;
            case 'producto':
                if(isset($filter['value'])) {
                    $query->where('abandoned_orders.product_id', $filter['value']);
                }
                break;
            case 'email_enviado':
                $query->whereNotNull('abandoned_orders.email_sent_at');
                break;
            case 'email_no_enviado':
                $query->whereNull('abandoned_orders.email_sent_at');
                break;
            case 'con_usuario':
                $query->whereNotNull('abandoned_orders.user_id');
                break;
            case 'sin_usuario':
                $query->whereNull('abandoned_orders.user_id');
                break;
            case 'con_cliente':
                $query->whereNotNull('abandoned_orders.id_customer');
                break;
            case 'sin_cliente':
                $query->whereNull('abandoned_orders.id_customer');
                break;
        }
        
        return $query;
    }

    protected function applyFilterGroupToQuery($query, $type, $filters)
    {
        switch($type) {
            case 'email_enviado':
            case 'email_no_enviado':
            case 'con_usuario':
            case 'sin_usuario':
                $this->applyFilterToQuery($query, reset($filters));
                break;
                
            case 'usuario':
                $userIds = array_filter(array_column($filters, 'value'));
                if (!empty($userIds)) {
                    $query->whereIn('abandoned_orders.user_id', $userIds);
                }
                break;
                
            case 'producto':
                $productIds = array_filter(array_column($filters, 'value'));
                if (!empty($productIds)) {
                    $query->whereIn('abandoned_orders.product_id', $productIds);
                }
                break;
        }
        
        return $query;
    }

    public function render()
    {
        $columnMap = [
            'fecha' => 'abandoned_orders.created_at',
            'total' => 'abandoned_orders.total_price',
            'usuario' => 'users.name',
            'cliente' => 'customers.nombre',
            'producto' => 'products.name',
            'mercado' => 'markets.name',
        ];
        
        $dbSortField = $columnMap[$this->sortField] ?? ('abandoned_orders.' . $this->sortField);
        
        $abandonedOrders = AbandonedOrderModel::select('abandoned_orders.*')
            ->with(['user', 'customer', 'product', 'market'])
            ->leftJoin('users', 'abandoned_orders.user_id', '=', 'users.id')
            ->leftJoin('customers', 'abandoned_orders.id_customer', '=', 'customers.id')
            ->leftJoin('products', 'abandoned_orders.product_id', '=', 'products.id')
            ->leftJoin('markets', 'abandoned_orders.id_market', '=', 'markets.id')
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->whereHas('user', function($q2) {
                        $q2->where('name', 'like', '%' . $this->search . '%')
                          ->orWhere('email', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('customer', function($q2) {
                        $q2->where('nombre', 'like', '%' . $this->search . '%')
                          ->orWhere('email', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('product', function($q2) {
                        $q2->where('name', 'like', '%' . $this->search . '%');
                    })
                    ->orWhere('abandoned_orders.note', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->showOnlySelected && count($this->selected) > 0, function($query) {
                $query->whereIn('abandoned_orders.id', $this->selected);
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
        $users = \App\Models\User::all();
        $customers = Customers::all();

        $this->currentAbandonedOrderIds = $abandonedOrders->pluck('id')->toArray();
        
        if ($this->showOnlySelected && count($this->selected) > 0) {
            $this->selected = array_map('intval', array_values($this->selected));
            $this->currentAbandonedOrderIds = array_map('intval', $this->currentAbandonedOrderIds);
        }

        return view('livewire.order.abandoned-orders', [
            'abandonedOrders' => $abandonedOrders,
            'products' => $products,
            'users' => $users,
            'customers' => $customers,
        ]);
    }
}
