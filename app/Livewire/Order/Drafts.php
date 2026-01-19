<?php

namespace App\Livewire\Order;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Order\Drafts as DraftModel;
use App\Models\Order\Orders as OrderModel;
use App\Models\Product\Products;
use App\Models\Customer\Customers;
use App\Livewire\Traits\HasSavedViews;

#[Layout('components.layouts.collapsable')]
class Drafts extends Component
{
    use WithPagination, HasSavedViews;

    // Propiedades para la tabla
    public $search = '';
    public $perPage = 10;
    public $sortField = 'id';
    public $sortDirection = 'desc';
    public $selected = [];
    public $selectAll = false;
    public $currentDraftIds = []; // IDs de los borradores en la página actual
    public $showOnlySelected = false; // Mostrar solo elementos seleccionados
    
    // Propiedades para exportación
    public $showExportModal = false;
    public $exportOption = 'current_page'; // current_page, all, selected, search, filtered
    public $exportFormat = 'csv'; // csv, plain_csv

    protected $listeners = [
        'selectedUpdated' => 'handleSelectedUpdated',
        'sortUpdated' => 'handleSortUpdated',
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
            // Obtener todos los IDs de la consulta actual (todas las páginas)
            $allIds = $this->getAllDraftIds();
            $this->selected = array_map('intval', $allIds);
        } else {
            $this->selected = [];
            $this->showOnlySelected = false;
        }
    }
    
    /**
     * Obtener todos los IDs de borradores según filtros actuales
     */
    protected function getAllDraftIds()
    {
        return DraftModel::select('drafts.id')
            ->leftJoin('customers', 'drafts.id_customer', '=', 'customers.id')
            ->leftJoin('markets', 'drafts.id_market', '=', 'markets.id')
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->whereHas('customer', function($q2) {
                        $q2->where('name', 'like', '%' . $this->search . '%')
                          ->orWhere('email', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('items.product', function($q2) {
                        $q2->where('name', 'like', '%' . $this->search . '%');
                    })
                    ->orWhere('drafts.note', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->showOnlySelected && count($this->selected) > 0, function($query) {
                $query->whereIn('drafts.id', $this->selected);
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
        $this->selectAll = count($this->selected) === count($this->currentDraftIds) && count($this->currentDraftIds) > 0;
        
        // Desactivar "mostrar solo seleccionados" si no hay selección
        if (count($this->selected) === 0) {
            $this->showOnlySelected = false;
        }
    }

    public function handleSelectedUpdated($selected)
    {
        $this->selected = $selected;
        $this->selectAll = count($this->selected) === count($this->currentDraftIds) && count($this->currentDraftIds) > 0;
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
     * Exportar borradores
     */
    public function exportDrafts()
    {
        $drafts = $this->getDraftsForExport();
        
        if ($drafts->isEmpty()) {
            session()->flash('error', 'No hay borradores para exportar');
            return;
        }
        
        $filename = 'borradores_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($drafts) {
            $file = fopen('php://output', 'w');
            
            // BOM para UTF-8 en Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Encabezados
            fputcsv($file, [
                'Borrador',
                'Fecha de creación',
                'Cliente',
                'Email',
                'Mercado',
                'Total',
                'Artículos',
                'Nota',
            ], $this->exportFormat === 'csv' ? ',' : ';');
            
            // Datos
            foreach ($drafts as $draft) {
                fputcsv($file, [
                    '#' . str_pad($draft->id, 4, '0', STR_PAD_LEFT),
                    $draft->created_at->format('d/m/Y H:i'),
                    $draft->customer?->name ?? 'Sin cliente',
                    $draft->customer?->email ?? '',
                    $draft->market?->name ?? '—',
                    number_format($draft->total_price, 2) . ' L',
                    $draft->items->count(),
                    $draft->note ?? '',
                ], $this->exportFormat === 'csv' ? ',' : ';');
            }
            
            fclose($file);
        };
        
        $this->closeExportModal();
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Obtener borradores según opción de exportación
     */
    protected function getDraftsForExport()
    {
        $query = DraftModel::with(['customer', 'items', 'market'])
            ->leftJoin('customers', 'drafts.id_customer', '=', 'customers.id')
            ->leftJoin('markets', 'drafts.id_market', '=', 'markets.id')
            ->select('drafts.*');
        
        // Aplicar filtros según la opción seleccionada
        switch ($this->exportOption) {
            case 'current_page':
                $query->whereIn('drafts.id', $this->currentDraftIds);
                break;
                
            case 'all':
                // Sin filtros adicionales, todos los borradores
                break;
                
            case 'selected':
                if (empty($this->selected)) {
                    return collect();
                }
                $query->whereIn('drafts.id', $this->selected);
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
                    ->orWhere('drafts.note', 'like', '%' . $this->search . '%');
                });
                break;
                
            case 'filtered':
                // Los filtros se aplicarán automáticamente después del switch
                break;
        }
        
        // Aplicar filtros activos del sistema
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
    
    /**
     * Define el tipo de vista para este componente
     */
    protected function getViewType(): string
    {
        return 'drafts';
    }

    /**
     * Eliminar borradores seleccionados
     */
    public function deleteSelected()
    {
        if (empty($this->selected)) {
            return;
        }
        
        DraftModel::whereIn('id', $this->selected)->delete();

        $this->selected = [];
        $this->selectAll = false;
        
        session()->flash('message', 'Borradores eliminados correctamente');
    }

    /**
     * Convertir borradores seleccionados en pedidos
     */
    public function convertToOrders()
    {
        if (empty($this->selected)) {
            return;
        }

        // Obtener los borradores seleccionados con sus items
        $drafts = DraftModel::with('items')->whereIn('id', $this->selected)->get();
        
        // Crear pedidos a partir de los borradores
        foreach ($drafts as $draft) {
            $order = OrderModel::create([
                'user_id' => $draft->user_id,
                'id_customer' => $draft->id_customer,
                'subtotal_price' => $draft->subtotal_price,
                'total_price' => $draft->total_price,
                'id_market' => $draft->id_market,
                'id_discount' => $draft->id_discount,
                'id_envio' => $draft->id_envio,
                'id_impuesto' => $draft->id_impuesto,
                'id_moneda' => $draft->id_moneda,
                'note' => $draft->note,
                'id_etiqueta' => $draft->id_etiqueta,
                'id_status_prepared_order' => $draft->id_status_prepared_order,
                'id_status_order' => 2, // Pendiente
            ]);

            // Copiar los items del borrador al pedido
            foreach ($draft->items as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'subtotal' => $item->subtotal,
                ]);
            }
        }
        
        // Eliminar los borradores convertidos
        DraftModel::whereIn('id', $this->selected)->delete();

        $this->selected = [];
        $this->selectAll = false;
        
        session()->flash('message', 'Borradores convertidos en pedidos correctamente');
    }
    
    /**
     * Aplicar filtro específico a la consulta de borradores
     */
    protected function applyFilterToQuery($query, $filter)
    {
        switch($filter['type']) {
            case 'cliente':
                if(isset($filter['value'])) {
                    $query->where('drafts.id_customer', $filter['value']);
                }
                break;
            case 'producto':
                if(isset($filter['value'])) {
                    $query->whereHas('items', function($q) use ($filter) {
                        $q->where('product_id', $filter['value']);
                    });
                }
                break;
            case 'con_cliente':
                $query->whereNotNull('drafts.id_customer');
                break;
            case 'sin_cliente':
                $query->whereNull('drafts.id_customer');
                break;
            case 'con_items':
                $query->whereHas('items');
                break;
            case 'sin_items':
                $query->whereDoesntHave('items');
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
            case 'con_cliente':
            case 'sin_cliente':
            case 'con_items':
            case 'sin_items':
                // Para estos filtros únicos, solo aplicar el primero
                $this->applyFilterToQuery($query, reset($filters));
                break;
                
            case 'cliente':
                // Agrupar múltiples clientes con OR
                $customerIds = array_filter(array_column($filters, 'value'));
                if (!empty($customerIds)) {
                    $query->whereIn('drafts.id_customer', $customerIds);
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

    public function render()
    {
        // Mapeo de columnas de visualización a columnas reales de la base de datos
        $columnMap = [
            'fecha' => 'drafts.created_at',
            'total' => 'drafts.total_price',
            'cliente' => 'customers.name',
            'Mercado' => 'markets.name',
            'articulos' => 'items_count',
        ];
        
        // Obtener el nombre real de la columna para la consulta SQL
        $dbSortField = $columnMap[$this->sortField] ?? ('drafts.' . $this->sortField);
        
        $drafts = DraftModel::select('drafts.*')
            ->selectSub(function ($query) {
                $query->selectRaw('COUNT(*)')
                    ->from('draft_items')
                    ->whereColumn('draft_items.draft_id', 'drafts.id');
            }, 'items_count')
            ->with(['customer', 'items.product', 'items.variant', 'market'])
            ->leftJoin('customers', 'drafts.id_customer', '=', 'customers.id')
            ->leftJoin('markets', 'drafts.id_market', '=', 'markets.id')
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->whereHas('customer', function($q2) {
                        $q2->where('name', 'like', '%' . $this->search . '%')
                          ->orWhere('email', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('items.product', function($q2) {
                        $q2->where('name', 'like', '%' . $this->search . '%');
                    })
                    ->orWhere('drafts.note', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->showOnlySelected && count($this->selected) > 0, function($query) {
                $query->whereIn('drafts.id', $this->selected);
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
        $this->currentDraftIds = $drafts->pluck('id')->toArray();
        
        // Si mostramos solo seleccionados, asegurar que todos estén marcados correctamente
        if ($this->showOnlySelected && count($this->selected) > 0) {
            // Normalizar selected a enteros para comparación consistente
            $this->selected = array_map('intval', array_values($this->selected));
            $this->currentDraftIds = array_map('intval', $this->currentDraftIds);
        }

        return view('livewire.order.drafts', [
            'drafts' => $drafts,
            'products' => $products,
            'customers' => $customers,
        ]);
    }
}
