<?php

namespace App\Livewire\Product;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Product\Transfer\Transfers as TransferModel;
use App\Models\Product\Transfer\StatusTransfer;
use App\Models\Product\Products;
use App\Models\PointSale\Branch\Branches;
use App\Livewire\Traits\HasSavedViews;

#[Layout('components.layouts.collapsable')]
class Transfers extends Component
{
    use WithPagination, HasSavedViews;

    // Propiedades para la tabla
    public $search = '';
    public $perPage = 10;
    public $sortField = 'id';
    public $sortDirection = 'desc';
    public $selected = [];
    public $selectAll = false;
    public $currentTransferIds = [];
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
    public $transferId;
    public $id_sucursal_origen;
    public $id_sucursal_destino;
    public $fecha_envio_creacion;
    public $id_product;
    public $cantidad;
    public $nombre_referencia;
    public $nota_interna;
    public $id_status_transfer;

    // Propiedades de control
    public $isEditing = false;
    public $showModal = false;
    public $showFilterDropdown = false;

    protected $rules = [
        'id_product' => 'required|exists:products,id',
        'cantidad' => 'required|integer|min:1',
        'nombre_referencia' => 'nullable|string',
        'nota_interna' => 'nullable|string',
        'id_status_transfer' => 'required',
    ];

    protected $messages = [
        'id_product.required' => 'El producto es obligatorio.',
        'cantidad.required' => 'La cantidad es obligatoria.',
        'cantidad.min' => 'La cantidad debe ser al menos 1.',
        'id_status_transfer.required' => 'El estado es obligatorio.',
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
            $allIds = $this->getAllTransferIds();
            $this->selected = array_map('intval', $allIds);
        } else {
            $this->selected = [];
            $this->showOnlySelected = false;
        }
    }
    
    protected function getAllTransferIds()
    {
        return TransferModel::select('transfers.id')
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('transfers.nombre_referencia', 'like', '%' . $this->search . '%')
                      ->orWhere('transfers.nota_interna', 'like', '%' . $this->search . '%')
                      ->orWhereHas('product', function($q2) {
                          $q2->where('name', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->showOnlySelected && count($this->selected) > 0, function($query) {
                $query->whereIn('transfers.id', $this->selected);
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
        $this->selectAll = count($this->selected) === count($this->currentTransferIds) && count($this->currentTransferIds) > 0;
        
        if (count($this->selected) === 0) {
            $this->showOnlySelected = false;
        }
    }

    public function handleSelectedUpdated($selected)
    {
        $this->selected = $selected;
        $this->selectAll = count($this->selected) === count($this->currentTransferIds) && count($this->currentTransferIds) > 0;
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
    
    public function exportTransfers()
    {
        $transfers = $this->getTransfersForExport();
        
        if ($transfers->isEmpty()) {
            session()->flash('error', 'No hay transferencias para exportar');
            return;
        }
        
        $filename = 'transferencias_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($transfers) {
            $file = fopen('php://output', 'w');
            
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, [
                'ID',
                'Referencia',
                'Producto',
                'Cantidad',
                'Origen',
                'Destino',
                'Estado',
                'Fecha',
            ], $this->exportFormat === 'csv' ? ',' : ';');
            
            foreach ($transfers as $transfer) {
                fputcsv($file, [
                    '#' . str_pad($transfer->id, 4, '0', STR_PAD_LEFT),
                    $transfer->nombre_referencia ?? '—',
                    $transfer->product->name ?? 'Sin producto',
                    $transfer->cantidad,
                    $transfer->sucursalOrigen->nombre ?? '—',
                    $transfer->sucursalDestino->nombre ?? '—',
                    $transfer->status->name ?? '—',
                    $transfer->created_at->format('d/m/Y H:i'),
                ], $this->exportFormat === 'csv' ? ',' : ';');
            }
            
            fclose($file);
        };
        
        $this->closeExportModal();
        
        return response()->stream($callback, 200, $headers);
    }
    
    protected function getTransfersForExport()
    {
        $query = TransferModel::with(['product', 'status', 'sucursalOrigen', 'sucursalDestino']);
        
        switch ($this->exportOption) {
            case 'current_page':
                $query->whereIn('transfers.id', $this->currentTransferIds);
                break;
                
            case 'all':
                break;
                
            case 'selected':
                if (empty($this->selected)) {
                    return collect();
                }
                $query->whereIn('transfers.id', $this->selected);
                break;
                
            case 'search':
                if (empty($this->search)) {
                    return collect();
                }
                $query->where(function($q) {
                    $q->where('nombre_referencia', 'like', '%' . $this->search . '%')
                      ->orWhere('nota_interna', 'like', '%' . $this->search . '%');
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
        return 'transfers';
    }

    public function markAsStatus($statusId)
    {
        if (empty($this->selected)) {
            return;
        }

        TransferModel::whereIn('id', $this->selected)
            ->update(['id_status_transfer' => $statusId]);

        $this->selected = [];
        $this->selectAll = false;
        
        session()->flash('message', 'Transferencias actualizadas correctamente');
    }

    protected function applyFilterToQuery($query, $filter)
    {
        switch($filter['type']) {
            case 'estado_pendiente':
                $query->where('transfers.id_status_transfer', 1);
                break;
            case 'estado_en_transito':
                $query->where('transfers.id_status_transfer', 2);
                break;
            case 'estado_completado':
                $query->where('transfers.id_status_transfer', 3);
                break;
            case 'producto':
                if(isset($filter['value'])) {
                    $query->where('transfers.id_product', $filter['value']);
                }
                break;
        }
        
        return $query;
    }

    protected function applyFilterGroupToQuery($query, $type, $filters)
    {
        switch($type) {
            case 'estado_pendiente':
            case 'estado_en_transito':
            case 'estado_completado':
                $this->applyFilterToQuery($query, reset($filters));
                break;
                
            case 'producto':
                $productIds = array_filter(array_column($filters, 'value'));
                if (!empty($productIds)) {
                    $query->whereIn('transfers.id_product', $productIds);
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

    public function setFilter($filter)
    {
        // Limpiar filtros actuales
        $this->activeFilters = [];
        
        // Aplicar el filtro seleccionado
        switch($filter) {
            case 'todos':
                $this->activeFilter = 'todos';
                break;
            case 'pendientes':
                $this->activeFilter = 'pendientes';
                $this->addFilter('estado_pendiente', null, 'Estado: Pendiente');
                break;
            case 'en_transito':
                $this->activeFilter = 'en_transito';
                $this->addFilter('estado_en_transito', null, 'Estado: En tránsito');
                break;
            case 'completados':
                $this->activeFilter = 'completados';
                $this->addFilter('estado_completado', null, 'Estado: Completado');
                break;
        }
        
        // Reset pagination
        $this->resetPage();
    }

    public function render()
    {
        $columnMap = [
            'fecha' => 'transfers.created_at',
            'referencia' => 'transfers.nombre_referencia',
            'producto' => 'transfers.id_product',
            'cantidad' => 'transfers.cantidad',
            'estado' => 'transfers.id_status_transfer',
        ];
        
        $dbSortField = $columnMap[$this->sortField] ?? ('transfers.' . $this->sortField);
        
        $transfers = TransferModel::select('transfers.*')
            ->with(['product', 'status', 'sucursalOrigen', 'sucursalDestino'])
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('transfers.nombre_referencia', 'like', '%' . $this->search . '%')
                      ->orWhere('transfers.nota_interna', 'like', '%' . $this->search . '%')
                      ->orWhereHas('product', function($q2) {
                          $q2->where('name', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->showOnlySelected && count($this->selected) > 0, function($query) {
                $query->whereIn('transfers.id', $this->selected);
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
        $statuses = StatusTransfer::all();
        $sucursales = Branches::all();

        $this->currentTransferIds = $transfers->pluck('id')->toArray();
        
        if ($this->showOnlySelected && count($this->selected) > 0) {
            $this->selected = array_map('intval', array_values($this->selected));
            $this->currentTransferIds = array_map('intval', $this->currentTransferIds);
        }

        return view('livewire.producto.transfer.transfers', [
            'transfers' => $transfers,
            'products' => $products,
            'statuses' => $statuses,
            'sucursales' => $sucursales,
        ]);
    }

    public function create()
    {
        return redirect()->route('transfers_create');
    }

    public function edit($id)
    {
        $transfer = TransferModel::findOrFail($id);
        
        $this->transferId = $transfer->id;
        $this->id_sucursal_origen = $transfer->id_sucursal_origen;
        $this->id_sucursal_destino = $transfer->id_sucursal_destino;
        $this->fecha_envio_creacion = $transfer->fecha_envio_creacion;
        $this->id_product = $transfer->id_product;
        $this->cantidad = $transfer->cantidad;
        $this->nombre_referencia = $transfer->nombre_referencia;
        $this->nota_interna = $transfer->nota_interna;
        $this->id_status_transfer = $transfer->id_status_transfer;
        
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function store()
    {
        $this->validate();

        TransferModel::create([
            'id_sucursal_origen' => $this->id_sucursal_origen,
            'id_sucursal_destino' => $this->id_sucursal_destino,
            'fecha_envio_creacion' => $this->fecha_envio_creacion ?? now(),
            'id_product' => $this->id_product,
            'cantidad' => $this->cantidad,
            'nombre_referencia' => $this->nombre_referencia,
            'nota_interna' => $this->nota_interna,
            'id_status_transfer' => $this->id_status_transfer,
        ]);

        session()->flash('message', 'Transferencia creada exitosamente');
        
        $this->resetForm();
        $this->showModal = false;
    }

    public function update()
    {
        $this->validate();

        $transfer = TransferModel::findOrFail($this->transferId);
        
        $transfer->update([
            'id_sucursal_origen' => $this->id_sucursal_origen,
            'id_sucursal_destino' => $this->id_sucursal_destino,
            'fecha_envio_creacion' => $this->fecha_envio_creacion,
            'id_product' => $this->id_product,
            'cantidad' => $this->cantidad,
            'nombre_referencia' => $this->nombre_referencia,
            'nota_interna' => $this->nota_interna,
            'id_status_transfer' => $this->id_status_transfer,
        ]);

        session()->flash('message', 'Transferencia actualizada exitosamente');

        $this->resetForm();
        $this->showModal = false;
    }

    public function delete($id)
    {
        $transfer = TransferModel::findOrFail($id);
        $transfer->delete();

        session()->flash('message', 'Transferencia eliminada exitosamente');
    }

    public function cancel()
    {
        $this->resetForm();
        $this->showModal = false;
    }

    private function resetForm()
    {
        $this->transferId = null;
        $this->id_sucursal_origen = null;
        $this->id_sucursal_destino = null;
        $this->fecha_envio_creacion = null;
        $this->id_product = null;
        $this->cantidad = 1;
        $this->nombre_referencia = null;
        $this->nota_interna = null;
        $this->id_status_transfer = 1;
        $this->isEditing = false;
        $this->resetErrorBag();
    }
}
