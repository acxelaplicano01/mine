<?php

namespace App\Livewire\Market;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Market\Markets as MarketModel;
use App\Models\Money\Moneda;
use App\Livewire\Traits\HasSavedViews;

#[Layout('components.layouts.collapsable')]
class Markets extends Component
{
    use WithPagination, HasSavedViews;

    // Propiedades para la tabla
    public $search = '';
    public $perPage = 10;
    public $sortField = 'id';
    public $sortDirection = 'desc';
    public $selected = [];
    public $selectAll = false;
    public $currentMarketIds = [];
    public $showOnlySelected = false;
    
    // Propiedades para exportación
    public $showExportModal = false;
    public $exportOption = 'current_page';
    public $exportFormat = 'csv';
    
    // Propiedades para modal de creación/edición
    public $showModal = false;
    public $isEditing = false;
    public $marketId = null;
    public $name = '';
    public $description = '';
    public $domain = '';
    public $id_moneda = null;
    public $id_catalogo = null;
    public $id_pais = null;
    public $id_tienda_online = null;
    public $id_status_market = 1;

    protected $listeners = [
        'selectedUpdated' => 'handleSelectedUpdated',
        'sortUpdated' => 'handleSortUpdated',
    ];

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'domain' => 'nullable|string|max:255',
        'id_moneda' => 'nullable|integer',
        'id_catalogo' => 'nullable|integer',
        'id_pais' => 'nullable|integer',
        'id_tienda_online' => 'nullable|integer',
        'id_status_market' => 'required|integer',
    ];

    public function updatedSelected()
    {
        $this->selectAll = !empty($this->selected) && count($this->selected) === count($this->currentMarketIds);
    }

    public function updatedSelectAll()
    {
        $this->selected = $this->selectAll ? $this->currentMarketIds : [];
    }

    protected function getViewType(): string
    {
        return 'markets';
    }

    protected function applyFilterToQuery($query, array $filter)
    {
        switch ($filter['type']) {
            case 'estado_activo':
                $query->where('id_status_market', 1);
                break;
            case 'estado_inactivo':
                $query->where('id_status_market', 0);
                break;
            case 'moneda':
                $query->where('id_moneda', $filter['value']);
                break;
        }
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

    public function handleSelectedUpdated($selected)
    {
        $this->selected = $selected;
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

    public function setFilter($filter)
    {
        $this->activeFilter = $filter;
        if (!str_starts_with($filter, 'custom_')) {
            $this->activeFilters = [];
            $this->showSearchBar = false;
            $this->search = '';
        }
        $this->resetPage();
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

    public function export()
    {
        $query = $this->getExportQuery();
        $markets = $query->get();
        
        if ($markets->isEmpty()) {
            session()->flash('warning', 'No hay datos para exportar');
            $this->closeExportModal();
            return;
        }

        $filename = $this->generateFilename();
        $csvData = $this->generateCsvData($markets);
        
        $this->closeExportModal();
        
        return response()->streamDownload(function() use ($csvData) {
            echo $csvData;
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function getExportQuery()
    {
        $query = MarketModel::query();

        // Aplicar filtros de búsqueda
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('domain', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        // Aplicar filtros predefinidos
        if ($this->activeFilter === 'activos') {
            $query->where('id_status_market', 1);
        } elseif ($this->activeFilter === 'inactivos') {
            $query->where('id_status_market', 0);
        }

        // Aplicar vistas guardadas
        if (str_starts_with($this->activeFilter, 'custom_')) {
            $this->applySavedViewFilters($query);
        }

        // Filtrar por selección si corresponde
        if ($this->exportOption === 'selected') {
            $query->whereIn('id', $this->selected);
        }

        return $query->orderBy($this->sortField, $this->sortDirection);
    }

    private function generateFilename()
    {
        $timestamp = now()->format('Y-m-d_His');
        return "mercados_{$timestamp}.csv";
    }

    private function generateCsvData($markets)
    {
        $headers = [
            'ID',
            'Nombre',
            'Descripción',
            'Dominio',
            'Moneda',
            'País',
            'Estado',
            'Fecha de creación',
        ];

        $csvData = "\xEF\xBB\xBF"; // BOM for UTF-8
        $csvData .= '"' . implode('","', $headers) . '"' . "\r\n";

        foreach ($markets as $market) {
            $row = [
                $market->id,
                $market->name ?? '',
                $market->description ?? '',
                $market->domain ?? '',
                $market->moneda->name ?? '',
                $market->id_pais ?? '',
                $market->id_status_market == 1 ? 'Activo' : 'Inactivo',
                $market->created_at?->format('d/m/Y H:i'),
            ];
            
            $csvData .= '"' . implode('","', array_map(function($field) {
                return str_replace('"', '""', $field);
            }, $row)) . '"' . "\r\n";
        }

        return $csvData;
    }

    public function create()
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $market = MarketModel::findOrFail($id);
        
        $this->marketId = $market->id;
        $this->name = $market->name;
        $this->description = $market->description;
        $this->domain = $market->domain;
        $this->id_moneda = $market->id_moneda;
        $this->id_catalogo = $market->id_catalogo;
        $this->id_pais = $market->id_pais;
        $this->id_tienda_online = $market->id_tienda_online;
        $this->id_status_market = $market->id_status_market;
        
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->isEditing) {
            $market = MarketModel::findOrFail($this->marketId);
            $market->update([
                'name' => $this->name,
                'description' => $this->description,
                'domain' => $this->domain,
                'id_moneda' => $this->id_moneda,
                'id_catalogo' => $this->id_catalogo,
                'id_pais' => $this->id_pais,
                'id_tienda_online' => $this->id_tienda_online,
                'id_status_market' => $this->id_status_market,
            ]);
            
            session()->flash('message', 'Mercado actualizado exitosamente.');
        } else {
            MarketModel::create([
                'name' => $this->name,
                'description' => $this->description,
                'domain' => $this->domain,
                'id_moneda' => $this->id_moneda,
                'id_catalogo' => $this->id_catalogo,
                'id_pais' => $this->id_pais,
                'id_tienda_online' => $this->id_tienda_online,
                'id_status_market' => $this->id_status_market,
            ]);
            
            session()->flash('message', 'Mercado creado exitosamente.');
        }

        $this->cancel();
    }

    public function delete($id)
    {
        $market = MarketModel::findOrFail($id);
        $market->delete();
        
        session()->flash('message', 'Mercado eliminado exitosamente.');
    }

    public function markAsStatus($status)
    {
        if (empty($this->selected)) {
            session()->flash('warning', 'No hay mercados seleccionados.');
            return;
        }

        MarketModel::whereIn('id', $this->selected)->update(['id_status_market' => $status]);
        
        $statusText = $status == 1 ? 'activos' : 'inactivos';
        session()->flash('message', count($this->selected) . " mercados marcados como {$statusText}.");
        
        $this->selected = [];
        $this->selectAll = false;
    }

    public function cancel()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->marketId = null;
        $this->name = '';
        $this->description = '';
        $this->domain = '';
        $this->id_moneda = null;
        $this->id_catalogo = null;
        $this->id_pais = null;
        $this->id_tienda_online = null;
        $this->id_status_market = 1;
        $this->isEditing = false;
        $this->resetErrorBag();
    }

    public function render()
    {
        $query = MarketModel::with(['moneda']);

        // Aplicar filtros de búsqueda
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('domain', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        // Aplicar filtros predefinidos
        if ($this->activeFilter === 'activos') {
            $query->where('id_status_market', 1);
        } elseif ($this->activeFilter === 'inactivos') {
            $query->where('id_status_market', 0);
        }

        // Aplicar vistas guardadas
        if (str_starts_with($this->activeFilter, 'custom_')) {
            $this->applySavedViewFilters($query);
        }

        // Aplicar filtros dinámicos
        foreach ($this->activeFilters as $filter) {
            $this->applyFilterToQuery($query, $filter);
        }

        // Filtrar por seleccionados si está activo
        if ($this->showOnlySelected) {
            $query->whereIn('id', $this->selected);
        }

        $markets = $query->orderBy($this->sortField, $this->sortDirection)
                        ->paginate($this->perPage);

        // Actualizar IDs de la página actual
        $this->currentMarketIds = $markets->pluck('id')->toArray();

        $monedas = Moneda::all();

        return view('livewire.market.markets', [
            'markets' => $markets,
            'monedas' => $monedas,
            'columns' => [
                ['key' => 'select', 'label' => '', 'sortable' => false],
                ['key' => 'id', 'label' => 'ID', 'sortable' => true],
                ['key' => 'name', 'label' => 'Nombre', 'sortable' => true],
                ['key' => 'domain', 'label' => 'Dominio', 'sortable' => true],
                ['key' => 'moneda', 'label' => 'Moneda', 'sortable' => false],
                ['key' => 'status', 'label' => 'Estado', 'sortable' => true, 'sortBy' => 'sortBy(\'id_status_market\')'],
                ['key' => 'created_at', 'label' => 'Fecha de creación', 'sortable' => true],
                ['key' => 'actions', 'label' => '', 'sortable' => false],
            ],
        ]);
    }
}
