<?php

namespace App\Livewire\Product;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Product\Products as ProductModel;
use App\Livewire\Traits\HasSavedViews;

#[Layout('components.layouts.collapsable')]
class Products extends Component
{
    use WithPagination, HasSavedViews;

    // Propiedades para la tabla
    public $search = '';
    public $perPage = 10;
    public $sortField = 'id';
    public $sortDirection = 'desc';
    public $selected = [];
    public $selectAll = false;
    public $currentProductIds = [];
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
            $allIds = $this->getAllProductIds();
            $this->selected = array_map('intval', $allIds);
        } else {
            $this->selected = [];
            $this->showOnlySelected = false;
        }
    }
    
    protected function getAllProductIds()
    {
        return ProductModel::select('products.id')
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->showOnlySelected && count($this->selected) > 0, function($query) {
                $query->whereIn('products.id', $this->selected);
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
        $this->selectAll = count($this->selected) === count($this->currentProductIds) && count($this->currentProductIds) > 0;
        
        if (count($this->selected) === 0) {
            $this->showOnlySelected = false;
        }
    }

    public function handleSelectedUpdated($selected)
    {
        $this->selected = $selected;
        $this->selectAll = count($this->selected) === count($this->currentProductIds) && count($this->currentProductIds) > 0;
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
    
    public function exportProducts()
    {
        $products = $this->getProductsForExport();
        
        if ($products->isEmpty()) {
            session()->flash('error', 'No hay productos para exportar');
            return;
        }
        
        $filename = 'productos_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($products) {
            $file = fopen('php://output', 'w');
            
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, [
                'ID',
                'Nombre',
                'Descripción',
                'Precio',
                'Costo',
                'Beneficio',
                'Margen %',
                'Stock',
                'Estado',
                'Cobrar impuestos',
            ], $this->exportFormat === 'csv' ? ',' : ';');
            
            foreach ($products as $product) {
                fputcsv($file, [
                    '#' . str_pad($product->id, 4, '0', STR_PAD_LEFT),
                    $product->name ?? '',
                    $product->description ?? '',
                    number_format($product->price_unitario ?? 0, 2) . ' L',
                    number_format($product->costo ?? 0, 2) . ' L',
                    number_format($product->beneficio ?? 0, 2) . ' L',
                    number_format($product->margen_beneficio ?? 0, 2) . '%',
                    $product->inventory->cantidad_inventario ?? 0,
                    $product->id_status_product == 1 ? 'Activo' : 'Inactivo',
                    $product->cobrar_impuestos ? 'Sí' : 'No',
                ], $this->exportFormat === 'csv' ? ',' : ';');
            }
            
            fclose($file);
        };
        
        $this->closeExportModal();
        
        return response()->stream($callback, 200, $headers);
    }
    
    protected function getProductsForExport()
    {
        $query = ProductModel::select('products.*')->with('inventory');
        
        switch ($this->exportOption) {
            case 'current_page':
                $query->whereIn('products.id', $this->currentProductIds);
                break;
                
            case 'all':
                break;
                
            case 'selected':
                if (empty($this->selected)) {
                    return collect();
                }
                $query->whereIn('products.id', $this->selected);
                break;
                
            case 'search':
                if (empty($this->search)) {
                    return collect();
                }
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
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
        return 'products';
    }

    /**
     * Eliminar productos seleccionados
     */
    public function deleteSelected()
    {
        if (empty($this->selected)) {
            return;
        }
        
        ProductModel::whereIn('id', $this->selected)->delete();

        $this->selected = [];
        $this->selectAll = false;
        
        session()->flash('message', 'Productos eliminados correctamente');
    }

    /**
     * Cambiar estado de productos seleccionados
     */
    public function changeStatus($status)
    {
        if (empty($this->selected)) {
            return;
        }

        ProductModel::whereIn('id', $this->selected)
            ->update(['id_status_product' => $status]);

        $this->selected = [];
        $this->selectAll = false;
        
        session()->flash('message', 'Estado de productos actualizado correctamente');
    }
    
    protected function applyFilterToQuery($query, $filter)
    {
        switch($filter['type']) {
            case 'activo':
                $query->where('products.id_status_product', 1);
                break;
            case 'inactivo':
                $query->where('products.id_status_product', 0);
                break;
            case 'cobrar_impuestos_si':
                $query->where('products.cobrar_impuestos', true);
                break;
            case 'cobrar_impuestos_no':
                $query->where('products.cobrar_impuestos', false);
                break;
            case 'con_stock':
                $query->whereHas('inventory', function($q) {
                    $q->where('cantidad_inventario', '>', 0);
                });
                break;
            case 'sin_stock':
                $query->where(function($q) {
                    $q->whereDoesntHave('inventory')
                      ->orWhereHas('inventory', function($q2) {
                          $q2->where('cantidad_inventario', '<=', 0);
                      });
                });
                break;
            case 'categoria':
                if(isset($filter['value'])) {
                    $query->where('products.id_category', $filter['value']);
                }
                break;
            case 'distribuidor':
                if(isset($filter['value'])) {
                    $query->where('products.id_distributor', $filter['value']);
                }
                break;
        }
        
        return $query;
    }

    protected function applyFilterGroupToQuery($query, $type, $filters)
    {
        switch($type) {
            case 'activo':
            case 'inactivo':
            case 'cobrar_impuestos_si':
            case 'cobrar_impuestos_no':
            case 'con_stock':
            case 'sin_stock':
                $this->applyFilterToQuery($query, reset($filters));
                break;
                
            case 'categoria':
                $categoryIds = array_filter(array_column($filters, 'value'));
                if (!empty($categoryIds)) {
                    $query->whereIn('products.id_category', $categoryIds);
                }
                break;
                
            case 'distribuidor':
                $distributorIds = array_filter(array_column($filters, 'value'));
                if (!empty($distributorIds)) {
                    $query->whereIn('products.id_distributor', $distributorIds);
                }
                break;
        }
        
        return $query;
    }

    public function render()
    {
        $columnMap = [
            'nombre' => 'products.name',
            'precio' => 'products.price_unitario',
            'costo' => 'products.costo',
            'beneficio' => 'products.beneficio',
            'margen' => 'products.margen_beneficio',
            'estado' => 'products.id_status_product',
        ];
        
        $dbSortField = $columnMap[$this->sortField] ?? ('products.' . $this->sortField);
        
        $products = ProductModel::select('products.*')
            ->with(['variants', 'inventory'])
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->showOnlySelected && count($this->selected) > 0, function($query) {
                $query->whereIn('products.id', $this->selected);
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

        $this->currentProductIds = $products->pluck('id')->toArray();
        
        if ($this->showOnlySelected && count($this->selected) > 0) {
            $this->selected = array_map('intval', array_values($this->selected));
            $this->currentProductIds = array_map('intval', $this->currentProductIds);
        }

        return view('livewire.producto.products', [
            'products' => $products,
        ]);
    }
}
