<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Customer\Customers as CustomerModel;
use App\Livewire\Traits\HasSavedViews;

#[Layout('components.layouts.collapsable')]
class Customers extends Component
{
    use WithPagination, HasSavedViews;

    // Propiedades para la tabla
    public $search = '';
    public $perPage = 10;
    public $sortField = 'id';
    public $sortDirection = 'desc';
    public $selected = [];
    public $selectAll = false;
    public $currentCustomerIds = [];
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
            $allIds = $this->getAllCustomerIds();
            $this->selected = array_map('intval', $allIds);
        } else {
            $this->selected = [];
            $this->showOnlySelected = false;
        }
    }
    
    protected function getAllCustomerIds()
    {
        return CustomerModel::select('customers.id')
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('last_name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%')
                      ->orWhere('address', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->showOnlySelected && count($this->selected) > 0, function($query) {
                $query->whereIn('customers.id', $this->selected);
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
        $this->selectAll = count($this->selected) === count($this->currentCustomerIds) && count($this->currentCustomerIds) > 0;
        
        if (count($this->selected) === 0) {
            $this->showOnlySelected = false;
        }
    }

    public function handleSelectedUpdated($selected)
    {
        $this->selected = $selected;
        $this->selectAll = count($this->selected) === count($this->currentCustomerIds) && count($this->currentCustomerIds) > 0;
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
    
    public function exportCustomers()
    {
        $customers = $this->getCustomersForExport();
        
        if ($customers->isEmpty()) {
            session()->flash('error', 'No hay clientes para exportar');
            return;
        }
        
        $filename = 'clientes_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($customers) {
            $file = fopen('php://output', 'w');
            
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, [
                'ID',
                'Nombre',
                'Apellido',
                'Email',
                'Teléfono',
                'Dirección',
                'Pedidos',
                'Total gastado',
                'Acepta email',
                'Acepta mensajes',
            ], $this->exportFormat === 'csv' ? ',' : ';');
            
            foreach ($customers as $customer) {
                fputcsv($file, [
                    '#' . str_pad($customer->id, 4, '0', STR_PAD_LEFT),
                    $customer->name ?? '',
                    $customer->last_name ?? '',
                    $customer->email ?? '',
                    $customer->phone ?? '',
                    $customer->address ?? '',
                    $customer->orders_count ?? 0,
                    number_format($customer->total_spent ?? 0, 2) . ' L',
                    $customer->acepta_email ? 'Sí' : 'No',
                    $customer->acepta_mensajes ? 'Sí' : 'No',
                ], $this->exportFormat === 'csv' ? ',' : ';');
            }
            
            fclose($file);
        };
        
        $this->closeExportModal();
        
        return response()->stream($callback, 200, $headers);
    }
    
    protected function getCustomersForExport()
    {
        $query = CustomerModel::select('customers.*');
        
        switch ($this->exportOption) {
            case 'current_page':
                $query->whereIn('customers.id', $this->currentCustomerIds);
                break;
                
            case 'all':
                break;
                
            case 'selected':
                if (empty($this->selected)) {
                    return collect();
                }
                $query->whereIn('customers.id', $this->selected);
                break;
                
            case 'search':
                if (empty($this->search)) {
                    return collect();
                }
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('last_name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%')
                      ->orWhere('address', 'like', '%' . $this->search . '%');
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
        return 'customers';
    }

    /**
     * Eliminar clientes seleccionados
     */
    public function deleteSelected()
    {
        if (empty($this->selected)) {
            return;
        }
        
        CustomerModel::whereIn('id', $this->selected)->delete();

        $this->selected = [];
        $this->selectAll = false;
        
        session()->flash('message', 'Clientes eliminados correctamente');
    }
    
    protected function applyFilterToQuery($query, $filter)
    {
        switch($filter['type']) {
            case 'acepta_email_si':
                $query->where('customers.acepta_email', true);
                break;
            case 'acepta_email_no':
                $query->where('customers.acepta_email', false);
                break;
            case 'acepta_mensajes_si':
                $query->where('customers.acepta_mensajes', true);
                break;
            case 'acepta_mensajes_no':
                $query->where('customers.acepta_mensajes', false);
                break;
            case 'con_pedidos':
                $query->where('customers.orders_count', '>', 0);
                break;
            case 'sin_pedidos':
                $query->where(function($q) {
                    $q->where('customers.orders_count', 0)
                      ->orWhereNull('customers.orders_count');
                });
                break;
        }
        
        return $query;
    }

    protected function applyFilterGroupToQuery($query, $type, $filters)
    {
        switch($type) {
            case 'acepta_email_si':
            case 'acepta_email_no':
            case 'acepta_mensajes_si':
            case 'acepta_mensajes_no':
            case 'con_pedidos':
            case 'sin_pedidos':
                $this->applyFilterToQuery($query, reset($filters));
                break;
        }
        
        return $query;
    }

    public function render()
    {
        $columnMap = [
            'nombre' => 'customers.name',
            'email' => 'customers.email',
            'pedidos' => 'customers.orders_count',
            'total_gastado' => 'customers.total_spent',
        ];
        
        $dbSortField = $columnMap[$this->sortField] ?? ('customers.' . $this->sortField);
        
        $customers = CustomerModel::select('customers.*')
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('last_name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%')
                      ->orWhere('address', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->showOnlySelected && count($this->selected) > 0, function($query) {
                $query->whereIn('customers.id', $this->selected);
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

        $this->currentCustomerIds = $customers->pluck('id')->toArray();
        
        if ($this->showOnlySelected && count($this->selected) > 0) {
            $this->selected = array_map('intval', array_values($this->selected));
            $this->currentCustomerIds = array_map('intval', $this->currentCustomerIds);
        }

        return view('livewire.customer.customers', [
            'customers' => $customers,
        ]);
    }
}
