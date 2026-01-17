<?php

namespace App\Livewire\Discount;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Discount\Discounts as DiscountModel;
use App\Models\Market\Markets;
use App\Livewire\Traits\HasSavedViews;

#[Layout('components.layouts.collapsable')]
class Discounts extends Component
{
    use WithPagination, HasSavedViews;

    // Propiedades para la tabla
    public $search = '';
    public $perPage = 10;
    public $sortField = 'id';
    public $sortDirection = 'desc';
    public $selected = [];
    public $selectAll = false;
    public $currentDiscountIds = [];
    public $showOnlySelected = false;
    
    // Propiedades para filtros
    public $filterStatus = 'all';
    public $filterType = 'all';
    public $exportOption = 'current_page'; // current_page, all, selected, search, filtered
    public $exportFormat = 'csv'; // csv, plain_csv
    
    // Modales
    public $showDeleteModal = false;
    public $discountToDelete = null;
    public $showExportModal = false;

    protected $listeners = [
        'selectedUpdated' => 'handleSelectedUpdated',
        'sortUpdated' => 'handleSortUpdated',
    ];

    public function mount()
    {
        $this->loadSavedViews();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            // Obtener todos los IDs de la consulta actual (todas las páginas)
            $allIds = $this->getAllDiscountIds();
            $this->selected = array_map('intval', $allIds);
        } else {
            $this->selected = [];
            $this->showOnlySelected = false;
        }
    }

    protected function getAllDiscountIds()
    {
        return DiscountModel::select('discounts.id')
            ->when($this->search, function($query) {
                $query->where('code_discount', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterStatus !== 'all', function($query) {
                $query->where('id_status_discount', $this->filterStatus);
            })
            ->when($this->filterType !== 'all', function($query) {
                $query->where('id_type_discount', $this->filterType);
            })
            ->when($this->showOnlySelected && count($this->selected) > 0, function($query) {
                $query->whereIn('discounts.id', $this->selected);
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
        $this->selectAll = count($this->selected) === count($this->currentDiscountIds) && count($this->currentDiscountIds) > 0;
        
        if (count($this->selected) === 0) {
            $this->showOnlySelected = false;
        }
    }

    public function handleSelectedUpdated($selected)
    {
        $this->selected = $selected;
        $this->selectAll = count($this->selected) === count($this->currentDiscountIds) && count($this->currentDiscountIds) > 0;
    }

    public function handleSortUpdated($sortField, $sortDirection)
    {
        $this->sortField = $sortField;
        $this->sortDirection = $sortDirection;
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        
        $this->sortField = $field;
    }

    /**
     * Exportar descuentos según la opción seleccionada
     */
    public function exportDiscounts()
    {
        $discounts = $this->getDiscountsForExport();
        
        if ($discounts->isEmpty()) {
            session()->flash('error', 'No hay descuentos para exportar');
            return;
        }
        
        $filename = 'descuentos_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($discounts) {
            $file = fopen('php://output', 'w');
            
            // BOM para UTF-8 en Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Encabezados
            fputcsv($file, [
                'ID', 'Código', 'Descripción', 'Valor', 'Tipo', 'Método', 'Estado', 'Usos', 'Creado', 'Actualizado',
            ], $this->exportFormat === 'csv' ? ',' : ';');
            
            // Datos
            foreach ($discounts as $discount) {
                fputcsv($file, [
                    '#' . str_pad($discount->id, 4, '0', STR_PAD_LEFT),
                    $discount->created_at->format('d/m/Y H:i'),
                    $discount->code_discount,
                    $discount->description,
                    $discount->valor_discount,
                    $discount->id_type_discount,
                    $discount->id_method_discount,
                    $discount->id_status_discount,
                    $discount->used_count,
                    $discount->updated_at->format('d/m/Y H:i'),
                ], $this->exportFormat === 'csv' ? ',' : ';');
            }
            
            fclose($file);
        };
        
        $this->closeExportModal();
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Obtener descuentos según opción de exportación
     */
    protected function getDiscountsForExport()
    {
        $query = DiscountModel::with(['typeDiscount', 'methodDiscount', 'statusDiscount'])
            ->leftJoin('type_discounts', 'discounts.id_type_discount', '=', 'type_discounts.id')
            ->leftJoin('method_discounts', 'discounts.id_method_discount', '=', 'method_discounts.id')
            ->leftJoin('status_discounts', 'discounts.id_status_discount', '=', 'status_discounts.id')
            ->select('discounts.*');
        
        // Aplicar filtros según la opción seleccionada
        switch ($this->exportOption) {
            case 'current_page':
                $query->whereIn('discounts.id', $this->currentDiscountIds);
                break;
                
            case 'all':
                // Sin filtros adicionales, todos los pedidos
                break;
                
            case 'selected':
                if (empty($this->selected)) {
                    return collect();
                }
                $query->whereIn('discounts.id', $this->selected);
                break;
                
            case 'search':
                if (empty($this->search)) {
                    return collect();
                }
                $query->where(function($q) {
                    $q->whereHas('tipoDiscount', function($q2) {
                        $q2->where('name', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('items.product', function($q2) {
                        $q2->where('name', 'like', '%' . $this->search . '%');
                    })
                    ->orWhere('discounts.note', 'like', '%' . $this->search . '%');
                });
                break;
                
            case 'filtered':
                // Los filtros se aplicarán automáticamente después del switch
                // Esta opción simplemente no aplica restricciones adicionales
                break;
        }
        
        // Aplicar filtros activos del sistema
        $query->when($this->activeFilter === 'no_pagados', function($q) {
            $q->where('discounts.id_status_discount', '!=', 1);
        })
        ->when($this->activeFilter === 'no_preparados', function($q) {
            $q->where('discounts.id_status_discount', 2);
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



    protected function getViewType(): string
    {
        return 'discounts';
    }

    protected function applyFilterToQuery($query, $filter)
    {
        switch($filter['type']) {
            case 'tipo_producto':
                $query->where('id_type_discount', 1);
                break;
            case 'tipo_buy_x_get_y':
                $query->where('id_type_discount', 2);
                break;
            case 'tipo_pedido':
                $query->where('id_type_discount', 3);
                break;
            case 'tipo_envio':
                $query->where('id_type_discount', 4);
                break;
            case 'metodo_codigo':
                $query->where('id_method_discount', 1);
                break;
            case 'metodo_automatico':
                $query->where('id_method_discount', 2);
                break;
        }
        
        return $query;
    }

    protected function applyFilterGroupToQuery($query, $type, $filters)
    {
        switch($type) {
            case 'tipo_producto':
            case 'tipo_buy_x_get_y':
            case 'tipo_pedido':
            case 'tipo_envio':
            case 'metodo_codigo':
            case 'metodo_automatico':
                // Para estos filtros, aplicar el primero del grupo
                $this->applyFilterToQuery($query, reset($filters));
                break;
        }
        
        return $query;
    }

    public function deleteDiscount($id)
    {
        $discount = DiscountModel::find($id);
        if ($discount) {
            $discount->delete();
            session()->flash('message', 'Descuento eliminado exitosamente.');
        }
    }

    public function toggleStatus($id)
    {
        $discount = DiscountModel::find($id);
        if ($discount) {
            $discount->id_status_discount = $discount->id_status_discount == 1 ? 4 : 1;
            $discount->save();
        }
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

    public function activateSelected()
    {
        if (count($this->selected) > 0) {
            DiscountModel::whereIn('id', $this->selected)->update(['id_status_discount' => 1]);
            session()->flash('message', count($this->selected) . ' descuento(s) activado(s) exitosamente.');
            $this->selected = [];
            $this->selectAll = false;
        }
    }

    public function deactivateSelected()
    {
        if (count($this->selected) > 0) {
            DiscountModel::whereIn('id', $this->selected)->update(['id_status_discount' => 4]);
            session()->flash('message', count($this->selected) . ' descuento(s) desactivado(s) exitosamente.');
            $this->selected = [];
            $this->selectAll = false;
        }
    }

    public function deleteSelected()
    {
        if (count($this->selected) > 0) {
            DiscountModel::whereIn('id', $this->selected)->delete();
            session()->flash('message', count($this->selected) . ' descuento(s) eliminado(s) exitosamente.');
            $this->selected = [];
            $this->selectAll = false;
        }
    }

    public function render()
    {
        $columnMap = [
            'code_discount' => 'code_discount',
            'id_type_discount' => 'id_type_discount',
            'id_method_discount' => 'id_method_discount',
            'valor_discount' => 'valor_discount',
            'id_status_discount' => 'id_status_discount',
            'used_count' => 'used_count',
        ];
        
        $dbSortField = $columnMap[$this->sortField] ?? $this->sortField;
        
        $query = DiscountModel::query()
            ->when($this->search, function($query) {
                $query->where('code_discount', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterStatus !== 'all', function($query) {
                $query->where('id_status_discount', $this->filterStatus);
            })
            ->when($this->filterType !== 'all', function($query) {
                $query->where('id_type_discount', $this->filterType);
            })
            ->when($this->showOnlySelected && count($this->selected) > 0, function($query) {
                $query->whereIn('id', $this->selected);
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
            ->orderBy($dbSortField, $this->sortDirection);
        
        $discounts = $query->paginate($this->perPage);
        
        $this->currentDiscountIds = $discounts->pluck('id')->toArray();
        
        if ($this->showOnlySelected && count($this->selected) > 0) {
            $this->selected = array_map('intval', array_values($this->selected));
            $this->currentDiscountIds = array_map('intval', $this->currentDiscountIds);
        }

        return view('livewire.discount.discounts', [
            'discounts' => $discounts,
        ]);
    }
}
