<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Customer\Segments as ModelSegments;
use App\Models\Customer\Customers;
use App\Services\CustomerSegmentService;
use App\Livewire\Traits\HasSavedViews;
use Illuminate\Support\Facades\DB;

#[Layout('components.layouts.collapsable')]
class Segments extends Component
{
    use WithPagination, HasSavedViews;

    // Propiedades para la tabla
    public $search = '';
    public $perPage = 10;
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $selected = [];
    public $selectAll = false;
    public $currentSegmentIds = [];
    public $showOnlySelected = false;
    
    // Propiedades para filtros
    public $filterType = 'all'; // all, manual, automatic
    public $exportOption = 'current_page';
    public $exportFormat = 'csv';
    
    // Modales
    public $showDeleteModal = false;
    public $segmentToDelete = null;
    public $showExportModal = false;
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showViewModal = false;
    public $selectedSegment = null;
    
    // Formulario de creación/edición
    public $segmentId = null;
    public $name = '';
    public $description = '';
    public $selectedCustomers = [];
    public $searchCustomers = '';

    protected $listeners = [
        'selectedUpdated' => 'handleSelectedUpdated',
        'sortUpdated' => 'handleSortUpdated',
    ];

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string|max:1000',
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
            $allIds = $this->getAllSegmentIds();
            $this->selected = array_map('intval', $allIds);
        } else {
            $this->selected = [];
            $this->showOnlySelected = false;
        }
    }

    protected function getAllSegmentIds()
    {
        return DB::table('customer_segments')
            ->select('id')
            ->when($this->search, function($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterType === 'automatic', function($query) {
                $query->whereIn('name', [
                    CustomerSegmentService::SEGMENT_NO_PURCHASES,
                    CustomerSegmentService::SEGMENT_AT_LEAST_ONE,
                    CustomerSegmentService::SEGMENT_MULTIPLE,
                ]);
            })
            ->when($this->filterType === 'manual', function($query) {
                $query->whereNotIn('name', [
                    CustomerSegmentService::SEGMENT_NO_PURCHASES,
                    CustomerSegmentService::SEGMENT_AT_LEAST_ONE,
                    CustomerSegmentService::SEGMENT_MULTIPLE,
                ]);
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
            ->whereNull('deleted_at')
            ->distinct()
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
        $this->selectAll = count($this->selected) === count($this->currentSegmentIds) && count($this->currentSegmentIds) > 0;
        
        if (count($this->selected) === 0) {
            $this->showOnlySelected = false;
        }
    }

    public function handleSelectedUpdated($selected)
    {
        $this->selected = $selected;
        $this->selectAll = count($this->selected) === count($this->currentSegmentIds) && count($this->currentSegmentIds) > 0;
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

    // Modales
    public function openExportModal()
    {
        $this->showExportModal = true;
    }

    public function closeExportModal()
    {
        $this->showExportModal = false;
    }

    public function openCreateModal()
    {
        $this->reset(['segmentId', 'name', 'description', 'selectedCustomers']);
        $this->showCreateModal = true;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->reset(['segmentId', 'name', 'description', 'selectedCustomers']);
    }

    public function openEditModal($segmentName)
    {
        $segment = DB::table('customer_segments')
            ->where('name', $segmentName)
            ->whereNull('deleted_at')
            ->first();
            
        if ($segment) {
            $this->segmentId = $segment->id;
            $this->name = $segment->name;
            $this->description = $segment->description;
            
            // Cargar clientes asignados a este segmento
            $customers = DB::table('customer_segments')
                ->where('name', $segmentName)
                ->whereNotNull('id_customer')
                ->whereNull('deleted_at')
                ->pluck('id_customer')
                ->toArray();
                
            $this->selectedCustomers = $customers;
            $this->showEditModal = true;
        }
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->reset(['segmentId', 'name', 'description', 'selectedCustomers']);
    }

    public function openViewModal($segmentName)
    {
        $this->selectedSegment = $segmentName;
        $this->showViewModal = true;
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->selectedSegment = null;
    }

    public function openDeleteModal($segmentName)
    {
        $this->segmentToDelete = $segmentName;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->segmentToDelete = null;
    }

    // CRUD Operations
    public function save()
    {
        $this->validate();
        
        // Verificar que no sea un segmento automático
        $automaticSegments = [
            CustomerSegmentService::SEGMENT_NO_PURCHASES,
            CustomerSegmentService::SEGMENT_AT_LEAST_ONE,
            CustomerSegmentService::SEGMENT_MULTIPLE,
        ];
        
        if (in_array($this->name, $automaticSegments)) {
            session()->flash('error', 'No puedes crear un segmento con este nombre. Es un segmento automático del sistema.');
            return;
        }
        
        DB::beginTransaction();
        try {
            // Verificar si ya existe un segmento con este nombre
            $existingSegment = DB::table('customer_segments')
                ->where('name', $this->name)
                ->whereNull('deleted_at')
                ->exists();
                
            if ($existingSegment) {
                session()->flash('error', 'Ya existe un segmento con este nombre.');
                DB::rollBack();
                return;
            }
            
            // Crear el segmento para cada cliente seleccionado
            foreach ($this->selectedCustomers as $customerId) {
                ModelSegments::create([
                    'name' => $this->name,
                    'description' => $this->description,
                    'id_customer' => $customerId,
                ]);
            }
            
            // Si no hay clientes seleccionados, crear solo el registro maestro
            if (empty($this->selectedCustomers)) {
                ModelSegments::create([
                    'name' => $this->name,
                    'description' => $this->description,
                    'id_customer' => null,
                ]);
            }
            
            DB::commit();
            session()->flash('message', 'Segmento creado exitosamente.');
            $this->closeCreateModal();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al crear el segmento: ' . $e->getMessage());
        }
    }

    public function update()
    {
        $this->validate();
        
        // Verificar que no sea un segmento automático
        $automaticSegments = [
            CustomerSegmentService::SEGMENT_NO_PURCHASES,
            CustomerSegmentService::SEGMENT_AT_LEAST_ONE,
            CustomerSegmentService::SEGMENT_MULTIPLE,
        ];
        
        $originalName = DB::table('customer_segments')
            ->where('id', $this->segmentId)
            ->value('name');
            
        if (in_array($originalName, $automaticSegments)) {
            session()->flash('error', 'No puedes editar un segmento automático del sistema.');
            return;
        }
        
        DB::beginTransaction();
        try {
            // Eliminar todas las asignaciones existentes de este segmento
            DB::table('customer_segments')
                ->where('name', $originalName)
                ->delete();
            
            // Crear nuevas asignaciones
            foreach ($this->selectedCustomers as $customerId) {
                ModelSegments::create([
                    'name' => $this->name,
                    'description' => $this->description,
                    'id_customer' => $customerId,
                ]);
            }
            
            if (empty($this->selectedCustomers)) {
                ModelSegments::create([
                    'name' => $this->name,
                    'description' => $this->description,
                    'id_customer' => null,
                ]);
            }
            
            DB::commit();
            session()->flash('message', 'Segmento actualizado exitosamente.');
            $this->closeEditModal();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al actualizar el segmento: ' . $e->getMessage());
        }
    }

    public function delete()
    {
        if (!$this->segmentToDelete) {
            return;
        }
        
        // Verificar que no sea un segmento automático
        $automaticSegments = [
            CustomerSegmentService::SEGMENT_NO_PURCHASES,
            CustomerSegmentService::SEGMENT_AT_LEAST_ONE,
            CustomerSegmentService::SEGMENT_MULTIPLE,
        ];
        
        if (in_array($this->segmentToDelete, $automaticSegments)) {
            session()->flash('error', 'No puedes eliminar un segmento automático del sistema.');
            $this->closeDeleteModal();
            return;
        }
        
        try {
            DB::table('customer_segments')
                ->where('name', $this->segmentToDelete)
                ->update(['deleted_at' => now()]);
            
            session()->flash('message', 'Segmento eliminado exitosamente.');
            $this->closeDeleteModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar el segmento: ' . $e->getMessage());
        }
    }

    public function deleteSelected()
    {
        if (empty($this->selected)) {
            return;
        }
        
        try {
            // Obtener los nombres de los segmentos seleccionados
            $segmentNames = DB::table('customer_segments')
                ->whereIn('id', $this->selected)
                ->whereNull('deleted_at')
                ->distinct()
                ->pluck('name')
                ->toArray();
            
            // Verificar que no haya segmentos automáticos
            $automaticSegments = [
                CustomerSegmentService::SEGMENT_NO_PURCHASES,
                CustomerSegmentService::SEGMENT_AT_LEAST_ONE,
                CustomerSegmentService::SEGMENT_MULTIPLE,
            ];
            
            $automaticsToDelete = array_intersect($segmentNames, $automaticSegments);
            if (!empty($automaticsToDelete)) {
                session()->flash('error', 'No puedes eliminar segmentos automáticos del sistema.');
                return;
            }
            
            // Eliminar los segmentos seleccionados
            DB::table('customer_segments')
                ->whereIn('name', $segmentNames)
                ->update(['deleted_at' => now()]);
            
            $count = count($segmentNames);
            session()->flash('message', "{$count} segmento(s) eliminado(s) exitosamente.");
            $this->selected = [];
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar segmentos: ' . $e->getMessage());
        }
    }

    public function syncAutomaticSegments()
    {
        try {
            CustomerSegmentService::syncAllAutomaticSegments();
            session()->flash('message', 'Segmentos automáticos sincronizados exitosamente.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al sincronizar segmentos: ' . $e->getMessage());
        }
    }

    // Exportación
    public function exportSegments()
    {
        $segments = $this->getSegmentsForExport();
        
        if ($segments->isEmpty()) {
            session()->flash('error', 'No hay segmentos para exportar');
            return;
        }
        
        $filename = 'segmentos_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($segments) {
            $file = fopen('php://output', 'w');
            
            // BOM para UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Encabezados
            fputcsv($file, [
                'Segmento', 'Descripción', 'Clientes', 'Tipo', 'Creado',
            ], $this->exportFormat === 'csv' ? ',' : ';');
            
            // Datos
            foreach ($segments as $segment) {
                $isAutomatic = in_array($segment->name, [
                    CustomerSegmentService::SEGMENT_NO_PURCHASES,
                    CustomerSegmentService::SEGMENT_AT_LEAST_ONE,
                    CustomerSegmentService::SEGMENT_MULTIPLE,
                ]);
                
                fputcsv($file, [
                    $segment->name,
                    $segment->description,
                    $segment->customers_count,
                    $isAutomatic ? 'Automático' : 'Manual',
                    $segment->created_at,
                ], $this->exportFormat === 'csv' ? ',' : ';');
            }
            
            fclose($file);
        };
        
        $this->closeExportModal();
        
        return response()->stream($callback, 200, $headers);
    }

    protected function getSegmentsForExport()
    {
        $query = DB::table('customer_segments')
            ->select('name', 'description', 'created_at', DB::raw('COUNT(id_customer) as customers_count'))
            ->whereNull('deleted_at');
        
        switch ($this->exportOption) {
            case 'current_page':
                $query->whereIn('id', $this->currentSegmentIds);
                break;
                
            case 'all':
                break;
                
            case 'selected':
                if (empty($this->selected)) {
                    return collect();
                }
                $query->whereIn('id', $this->selected);
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
        
        $query->when($this->filterType === 'automatic', function($q) {
            $q->whereIn('name', [
                CustomerSegmentService::SEGMENT_NO_PURCHASES,
                CustomerSegmentService::SEGMENT_AT_LEAST_ONE,
                CustomerSegmentService::SEGMENT_MULTIPLE,
            ]);
        })
        ->when($this->filterType === 'manual', function($q) {
            $q->whereNotIn('name', [
                CustomerSegmentService::SEGMENT_NO_PURCHASES,
                CustomerSegmentService::SEGMENT_AT_LEAST_ONE,
                CustomerSegmentService::SEGMENT_MULTIPLE,
            ]);
        });
        
        return $query->groupBy('name', 'description', 'created_at')->get();
    }

    protected function getViewType(): string
    {
        return 'segments';
    }

    protected function applyFilterToQuery($query, $filter)
    {
        switch($filter['type']) {
            case 'tipo_automatico':
                $query->whereIn('name', [
                    CustomerSegmentService::SEGMENT_NO_PURCHASES,
                    CustomerSegmentService::SEGMENT_AT_LEAST_ONE,
                    CustomerSegmentService::SEGMENT_MULTIPLE,
                ]);
                break;
            case 'tipo_manual':
                $query->whereNotIn('name', [
                    CustomerSegmentService::SEGMENT_NO_PURCHASES,
                    CustomerSegmentService::SEGMENT_AT_LEAST_ONE,
                    CustomerSegmentService::SEGMENT_MULTIPLE,
                ]);
                break;
        }
        
        return $query;
    }

    protected function applyFilterGroupToQuery($query, $type, $filters)
    {
        switch($type) {
            case 'tipo_automatico':
            case 'tipo_manual':
                $query->where(function($q) use ($filters) {
                    foreach($filters as $filter) {
                        $this->applyFilterToQuery($q, $filter);
                    }
                });
                break;
        }
    }

    public function render()
    {
        // Obtener segmentos agrupados por nombre con conteo de clientes únicos
        $segments = DB::table('customer_segments')
            ->select(
                DB::raw('MIN(id) as id'),
                'name',
                'description',
                DB::raw('MIN(created_at) as created_at'),
                DB::raw('COUNT(DISTINCT id_customer) as customers_count')
            )
            ->when($this->search, function($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterType === 'automatic', function($query) {
                $query->whereIn('name', [
                    CustomerSegmentService::SEGMENT_NO_PURCHASES,
                    CustomerSegmentService::SEGMENT_AT_LEAST_ONE,
                    CustomerSegmentService::SEGMENT_MULTIPLE,
                ]);
            })
            ->when($this->filterType === 'manual', function($query) {
                $query->whereNotIn('name', [
                    CustomerSegmentService::SEGMENT_NO_PURCHASES,
                    CustomerSegmentService::SEGMENT_AT_LEAST_ONE,
                    CustomerSegmentService::SEGMENT_MULTIPLE,
                ]);
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
            ->whereNull('deleted_at')
            ->groupBy('name', 'description')
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
        
        $this->currentSegmentIds = $segments->pluck('id')->toArray();
        
        // Obtener clientes para el formulario
        $customers = Customers::query()
            ->when($this->searchCustomers, function($query) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->searchCustomers . '%')
                      ->orWhere('email', 'like', '%' . $this->searchCustomers . '%');
                });
            })
            ->limit(50)
            ->get();
        
        // Si hay un segmento seleccionado para ver, cargar sus clientes
        $segmentCustomers = [];
        if ($this->selectedSegment) {
            $segmentCustomers = DB::table('customer_segments')
                ->join('customers', 'customer_segments.id_customer', '=', 'customers.id')
                ->where('customer_segments.name', $this->selectedSegment)
                ->whereNotNull('customer_segments.id_customer')
                ->whereNull('customer_segments.deleted_at')
                ->select('customers.*')
                ->get();
        }

        return view('livewire.customer.segments', [
            'segments' => $segments,
            'customers' => $customers,
            'segmentCustomers' => $segmentCustomers,
        ]);
    }
}
