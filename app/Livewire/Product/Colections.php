<?php

namespace App\Livewire\Product;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Product\Collection\CollectionsPage;
use App\Models\Product\Collection\TypeCollection;
use App\Models\Product\Collection\StatusCollection;
use App\Livewire\Traits\HasSavedViews;

#[Layout('components.layouts.collapsable')]
class Colections extends Component
{
    use WithPagination, HasSavedViews;

    // Configuración de vistas guardadas
    protected function getViewType(): string
    {
        return 'colecciones';
    }

    /**
     * Aplicar filtro individual a la query
     */
    protected function applyFilterToQuery($query, array $filter)
    {
        switch ($filter['type']) {
            case 'activo':
                $query->where('id_status_collection', 1);
                break;
                
            case 'inactivo':
                $query->where('id_status_collection', 0);
                break;
                
            case 'manual':
                $query->where('id_tipo_collection', 1);
                break;
                
            case 'inteligente':
                $query->where('id_tipo_collection', 2);
                break;
        }
    }

    // Propiedades para la tabla
    public $search = '';
    public $perPage = 10;
    public $sortField = 'id';
    public $sortDirection = 'desc';
    public $selected = [];
    public $selectAll = false;
    public $currentCollectionIds = [];
    public $showOnlySelected = false;
    
    // Propiedades para exportación
    public $showExportModal = false;
    public $exportOption = 'current_page';
    public $exportFormat = 'csv';

    // Modal de eliminación
    public $showDeleteModal = false;
    public $deletingCollections = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'id'],
        'sortDirection' => ['except' => 'desc'],
        'activeFilter' => ['except' => 'todos'],
    ];

    protected $listeners = [
        'selectedUpdated' => 'handleSelectedUpdated',
        'sortUpdated' => 'handleSortUpdated',
    ];

    public function mount()
    {
        $this->loadSavedViews();
    }

    public function sortBy($field)
    {
        // Mapeo de columnas de visualización a columnas de BD
        $columnMap = [
            'nombre' => 'name',
            'tipo' => 'id_tipo_collection',
            'estado' => 'id_status_collection',
        ];

        // Convertir el campo de visualización al campo de BD
        $dbField = $columnMap[$field] ?? $field;

        if ($this->sortField === $dbField) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        
        $this->sortField = $dbField;
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $allIds = $this->getAllCollectionIds();
            $this->selected = array_map('intval', $allIds);
        } else {
            $this->selected = [];
            $this->showOnlySelected = false;
        }
    }

    protected function getAllCollectionIds()
    {
        return CollectionsPage::select('collections.id')
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->showOnlySelected && count($this->selected) > 0, function($query) {
                $query->whereIn('collections.id', $this->selected);
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
        $this->selectAll = count($this->selected) === count($this->currentCollectionIds) && count($this->currentCollectionIds) > 0;
        
        if (count($this->selected) === 0) {
            $this->showOnlySelected = false;
        }
    }

    public function handleSelectedUpdated($selected)
    {
        $this->selected = $selected;
        $this->selectAll = count($this->selected) === count($this->currentCollectionIds) && count($this->currentCollectionIds) > 0;
    }

    public function handleSortUpdated($sortField, $sortDirection)
    {
        $this->sortField = $sortField;
        $this->sortDirection = $sortDirection;
    }

    protected function applyFilterGroupToQuery($query, $type, $filters)
    {
        switch ($type) {
            case 'estado':
                $query->where(function($q) use ($filters) {
                    foreach ($filters as $filter) {
                        if ($filter['id'] === 'activo') {
                            $q->orWhere('id_status_collection', 1);
                        } elseif ($filter['id'] === 'inactivo') {
                            $q->orWhere('id_status_collection', 0);
                        }
                    }
                });
                break;
                
            case 'tipo':
                $query->where(function($q) use ($filters) {
                    foreach ($filters as $filter) {
                        if ($filter['id'] === 'manual') {
                            $q->orWhere('id_tipo_collection', 1);
                        } elseif ($filter['id'] === 'inteligente') {
                            $q->orWhere('id_tipo_collection', 2);
                        }
                    }
                });
                break;
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

    public function changeStatus($status)
    {
        if (count($this->selected) === 0) {
            session()->flash('error', 'No hay colecciones seleccionadas');
            return;
        }

        CollectionsPage::whereIn('id', $this->selected)->update([
            'id_status_collection' => $status
        ]);

        session()->flash('message', count($this->selected) . ' colecciones actualizadas correctamente');
        $this->selected = [];
        $this->selectAll = false;
    }

    public function confirmDelete()
    {
        if (count($this->selected) === 0) {
            session()->flash('error', 'No hay colecciones seleccionadas');
            return;
        }

        $this->deletingCollections = $this->selected;
        $this->showDeleteModal = true;
    }

    public function deleteSelected()
    {
        if (count($this->deletingCollections) === 0) {
            $this->showDeleteModal = false;
            return;
        }

        CollectionsPage::whereIn('id', $this->deletingCollections)->delete();

        session()->flash('message', count($this->deletingCollections) . ' colecciones eliminadas correctamente');
        
        $this->selected = [];
        $this->deletingCollections = [];
        $this->selectAll = false;
        $this->showDeleteModal = false;
    }

    public function cancelDelete()
    {
        $this->deletingCollections = [];
        $this->showDeleteModal = false;
    }

    public function render()
    {
        $collections = CollectionsPage::query()
            ->withCount('collectionProducts')
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->activeFilter === 'activo', function($query) {
                $query->where('id_status_collection', 1);
            })
            ->when($this->activeFilter === 'inactivo', function($query) {
                $query->where('id_status_collection', 0);
            })
            ->when($this->activeFilter === 'manual', function($query) {
                $query->where('id_tipo_collection', 1);
            })
            ->when($this->activeFilter === 'inteligente', function($query) {
                $query->where('id_tipo_collection', 2);
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
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $this->currentCollectionIds = $collections->pluck('id')->toArray();

        return view('livewire.producto.colections', [
            'collections' => $collections,
        ]);
    }
}
