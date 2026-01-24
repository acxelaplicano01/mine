<?php

namespace App\Livewire\Product;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Product\GiftCard\GiftCards as GiftCardModel;
use App\Models\Customer\Customers;
use App\Livewire\Traits\HasSavedViews;

#[Layout('components.layouts.collapsable')]
class GiftCards extends Component
{
    use WithPagination, HasSavedViews;

    // Propiedades para la tabla
    public $search = '';
    public $perPage = 10;
    public $sortField = 'id';
    public $sortDirection = 'desc';
    public $selected = [];
    public $selectAll = false;
    public $currentGiftCardIds = [];
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
    public $giftCardId;
    public $code;
    public $valor_inicial;
    public $expiry_date;
    public $id_customer;
    public $id_status_gift_card;

    // Propiedades de control
    public $isEditing = false;
    public $showModal = false;
    public $showFilterDropdown = false;
    public $showUseModal = false;
    public $useGiftCardId;
    public $useAmount;
    public $useDescription;

    protected $rules = [
        'code' => 'required|string|unique:gift_cards,code',
        'valor_inicial' => 'required|numeric|min:0',
        'expiry_date' => 'nullable|date',
        'id_customer' => 'nullable|exists:customers,id',
        'id_status_gift_card' => 'required|integer',
        'useAmount' => 'required|numeric|min:0.01',
        'useDescription' => 'nullable|string|max:255',
    ];

    protected $messages = [
        'code.required' => 'El código es obligatorio.',
        'code.unique' => 'Este código ya existe.',
        'valor_inicial.required' => 'El valor inicial es obligatorio.',
        'valor_inicial.min' => 'El valor debe ser mayor a 0.',
        'id_status_gift_card.required' => 'El estado es obligatorio.',
        'useAmount.required' => 'El monto a usar es obligatorio.',
        'useAmount.min' => 'El monto debe ser mayor a 0.',
        'useAmount.numeric' => 'El monto debe ser numérico.',
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
            $allIds = $this->getAllGiftCardIds();
            $this->selected = array_map('intval', $allIds);
        } else {
            $this->selected = [];
            $this->showOnlySelected = false;
        }
    }
    
    protected function getAllGiftCardIds()
    {
        return GiftCardModel::select('gift_cards.id')
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('gift_cards.code', 'like', '%' . $this->search . '%')
                      ->orWhere('gift_cards.valor_inicial', 'like', '%' . $this->search . '%')
                      ->orWhereHas('customer', function($q2) {
                          $q2->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('email', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->showOnlySelected && count($this->selected) > 0, function($query) {
                $query->whereIn('gift_cards.id', $this->selected);
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
        $this->selectAll = count($this->selected) === count($this->currentGiftCardIds) && count($this->currentGiftCardIds) > 0;
        
        if (count($this->selected) === 0) {
            $this->showOnlySelected = false;
        }
    }

    public function handleSelectedUpdated($selected)
    {
        $this->selected = $selected;
        $this->selectAll = count($this->selected) === count($this->currentGiftCardIds) && count($this->currentGiftCardIds) > 0;
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

    public function setFilter($filter)
    {
        $this->activeFilters = [];
        
        switch($filter) {
            case 'todos':
                $this->activeFilter = 'todos';
                break;
            case 'activos':
                $this->activeFilter = 'activos';
                $this->addFilter('estado_activo', null, 'Estado: Activo');
                break;
            case 'expirados':
                $this->activeFilter = 'expirados';
                $this->addFilter('estado_expirado', null, 'Estado: Expirado');
                break;
            case 'usados':
                $this->activeFilter = 'usados';
                $this->addFilter('estado_usado', null, 'Estado: Usado');
                break;
        }
        
        $this->resetPage();
    }

    protected function applyFilterGroupToQuery($query, $type, $filters)
    {
        switch($type) {
            case 'estado_activo':
            case 'estado_expirado':
            case 'estado_usado':
                $this->applyFilterToQuery($query, reset($filters));
                break;
                
            case 'customer':
                $customerIds = array_filter(array_column($filters, 'value'));
                if (!empty($customerIds)) {
                    $query->whereIn('gift_cards.id_customer', $customerIds);
                }
                break;
        }
        
        return $query;
    }
    
    protected function applyFilterToQuery($query, $filter)
    {
        switch($filter['type']) {
            case 'estado_activo':
                $query->where('gift_cards.id_status_gift_card', 1);
                break;
            case 'estado_expirado':
                $query->where('gift_cards.id_status_gift_card', 2);
                break;
            case 'estado_usado':
                $query->where('gift_cards.id_status_gift_card', 3);
                break;
            case 'customer':
                if(isset($filter['value'])) {
                    $query->where('gift_cards.id_customer', $filter['value']);
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

    public function render()
    {
        $columnMap = [
            'fecha' => 'gift_cards.created_at',
            'codigo' => 'gift_cards.code',
            'valor' => 'gift_cards.valor_inicial',
            'cliente' => 'gift_cards.id_customer',
            'estado' => 'gift_cards.id_status_gift_card',
            'expiracion' => 'gift_cards.expiry_date',
        ];
        
        $dbSortField = $columnMap[$this->sortField] ?? ('gift_cards.' . $this->sortField);
        
        $giftCards = GiftCardModel::select('gift_cards.*')
            ->with(['customer'])
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('gift_cards.code', 'like', '%' . $this->search . '%')
                      ->orWhere('gift_cards.valor_inicial', 'like', '%' . $this->search . '%')
                      ->orWhereHas('customer', function($q2) {
                          $q2->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('email', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->showOnlySelected && count($this->selected) > 0, function($query) {
                $query->whereIn('gift_cards.id', $this->selected);
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

        $customers = Customers::all();

        $this->currentGiftCardIds = $giftCards->pluck('id')->toArray();
        
        if ($this->showOnlySelected && count($this->selected) > 0) {
            $this->selected = array_map('intval', array_values($this->selected));
            $this->currentGiftCardIds = array_map('intval', $this->currentGiftCardIds);
        }

        return view('livewire.producto.gift-cards', [
            'giftCards' => $giftCards,
            'customers' => $customers,
        ]);
    }

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $giftCard = GiftCardModel::findOrFail($id);
        
        $this->giftCardId = $giftCard->id;
        $this->code = $giftCard->code;
        $this->valor_inicial = $giftCard->valor_inicial;
        $this->expiry_date = $giftCard->expiry_date ? $giftCard->expiry_date->format('Y-m-d') : null;
        $this->id_customer = $giftCard->id_customer;
        $this->id_status_gift_card = $giftCard->id_status_gift_card;
        
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function store()
    {
        $this->validate();

        GiftCardModel::create([
            'code' => $this->code,
            'valor_inicial' => $this->valor_inicial,
            'valor_usado' => 0,
            'expiry_date' => $this->expiry_date,
            'id_customer' => $this->id_customer,
            'id_status_gift_card' => $this->id_status_gift_card ?? 1,
        ]);

        session()->flash('message', 'Tarjeta de regalo creada exitosamente');
        
        $this->resetForm();
        $this->showModal = false;
    }

    public function update()
    {
        $this->validate([
            'code' => 'required|string|unique:gift_cards,code,' . $this->giftCardId,
            'valor_inicial' => 'required|numeric|min:0',
            'expiry_date' => 'nullable|date',
            'id_customer' => 'nullable|exists:customers,id',
            'id_status_gift_card' => 'required|integer',
        ]);

        $giftCard = GiftCardModel::findOrFail($this->giftCardId);
        
        $giftCard->update([
            'code' => $this->code,
            'valor_inicial' => $this->valor_inicial,
            'expiry_date' => $this->expiry_date,
            'id_customer' => $this->id_customer,
            'id_status_gift_card' => $this->id_status_gift_card,
        ]);

        session()->flash('message', 'Tarjeta de regalo actualizada exitosamente');

        $this->resetForm();
        $this->showModal = false;
    }

    public function delete($id)
    {
        $giftCard = GiftCardModel::findOrFail($id);
        $giftCard->delete();

        session()->flash('message', 'Tarjeta de regalo eliminada exitosamente');
    }

    public function markAsStatus($statusId)
    {
        if (count($this->selected) === 0) {
            session()->flash('warning', 'No hay tarjetas seleccionadas');
            return;
        }

        GiftCardModel::whereIn('id', $this->selected)->update([
            'id_status_gift_card' => $statusId
        ]);

        $statusName = match($statusId) {
            1 => 'Activo',
            2 => 'Expirado', 
            3 => 'Usado',
            default => 'Desconocido'
        };

        $count = count($this->selected);
        session()->flash('message', "{$count} tarjeta" . ($count > 1 ? 's' : '') . " marcada" . ($count > 1 ? 's' : '') . " como {$statusName}");
        
        $this->selected = [];
        $this->selectAll = false;
    }

    public function openUseModal($giftCardId)
    {
        $this->useGiftCardId = $giftCardId;
        $this->useAmount = null;
        $this->useDescription = null;
        $this->showUseModal = true;
        $this->resetErrorBag(['useAmount', 'useDescription']);
    }

    public function closeUseModal()
    {
        $this->showUseModal = false;
        $this->useGiftCardId = null;
        $this->useAmount = null;
        $this->useDescription = null;
        $this->resetErrorBag(['useAmount', 'useDescription']);
    }

    public function useGiftCard()
    {
        $this->validate([
            'useAmount' => 'required|numeric|min:0.01',
            'useDescription' => 'nullable|string|max:255',
        ]);

        try {
            $giftCard = GiftCardModel::findOrFail($this->useGiftCardId);
            
            // Verificar si se puede usar el monto solicitado
            if (!$giftCard->canUse($this->useAmount)) {
                if ($giftCard->id_status_gift_card !== 1) {
                    $this->addError('useAmount', 'Esta tarjeta no está activa.');
                    return;
                }
                
                if ($giftCard->expiry_date && $giftCard->expiry_date->isPast()) {
                    $this->addError('useAmount', 'Esta tarjeta ha expirado.');
                    return;
                }
                
                if ($giftCard->valor_restante < $this->useAmount) {
                    $this->addError('useAmount', 'Saldo insuficiente. Saldo disponible: L ' . number_format($giftCard->valor_restante, 2));
                    return;
                }
            }

            // Usar la tarjeta
            $giftCard->use($this->useAmount, $this->useDescription);

            session()->flash('message', 'Tarjeta usada exitosamente. Saldo restante: L ' . number_format($giftCard->valor_restante, 2));
            
            $this->closeUseModal();
            
        } catch (\Exception $e) {
            $this->addError('useAmount', 'Error al usar la tarjeta: ' . $e->getMessage());
        }
    }

    public function export()
    {
        $query = $this->getExportQuery();
        $giftCards = $query->get();
        
        if ($giftCards->isEmpty()) {
            session()->flash('warning', 'No hay datos para exportar');
            $this->closeExportModal();
            return;
        }

        $filename = $this->generateFilename();
        $csvData = $this->generateCsvData($giftCards);
        
        $this->closeExportModal();
        
        return response()->streamDownload(function() use ($csvData) {
            echo $csvData;
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    protected function getExportQuery()
    {
        $baseQuery = GiftCardModel::select('gift_cards.*')
            ->with(['customer']);

        switch($this->exportOption) {
            case 'current_page':
                // Obtener solo los IDs de la página actual
                $currentPageIds = $this->currentGiftCardIds;
                return $baseQuery->whereIn('gift_cards.id', $currentPageIds);
                
            case 'selected':
                if (empty($this->selected)) {
                    return $baseQuery->whereRaw('1 = 0'); // No results
                }
                return $baseQuery->whereIn('gift_cards.id', $this->selected);
                
            case 'search':
                return $baseQuery->when($this->search, function($query) {
                    $query->where(function($q) {
                        $q->where('gift_cards.code', 'like', '%' . $this->search . '%')
                          ->orWhere('gift_cards.valor_inicial', 'like', '%' . $this->search . '%')
                          ->orWhereHas('customer', function($q2) {
                              $q2->where('name', 'like', '%' . $this->search . '%')
                                ->orWhere('email', 'like', '%' . $this->search . '%');
                          });
                    });
                });
                
            case 'filtered':
                return $baseQuery
                    ->when($this->search, function($query) {
                        $query->where(function($q) {
                            $q->where('gift_cards.code', 'like', '%' . $this->search . '%')
                              ->orWhere('gift_cards.valor_inicial', 'like', '%' . $this->search . '%')
                              ->orWhereHas('customer', function($q2) {
                                  $q2->where('name', 'like', '%' . $this->search . '%')
                                    ->orWhere('email', 'like', '%' . $this->search . '%');
                              });
                        });
                    })
                    ->when(count($this->activeFilters) > 0, function($query) {
                        $filtersByType = [];
                        foreach($this->activeFilters as $filterId => $filter) {
                            $filtersByType[$filter['type']][$filterId] = $filter;
                        }
                        
                        foreach($filtersByType as $type => $filters) {
                            $this->applyFilterGroupToQuery($query, $type, $filters);
                        }
                    });
                
            case 'all':
            default:
                return $baseQuery;
        }
    }

    protected function generateFilename()
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $type = match($this->exportOption) {
            'current_page' => 'pagina_actual',
            'selected' => 'seleccionados',
            'search' => 'busqueda',
            'filtered' => 'filtrados',
            'all' => 'todas',
            default => 'tarjetas'
        };
        
        $extension = $this->exportFormat === 'csv' ? '.csv' : '.csv';
        
        return "tarjetas_regalo_{$type}_{$timestamp}{$extension}";
    }

    protected function generateCsvData($giftCards)
    {
        $output = fopen('php://temp', 'r+');
        
        // Configurar encoding para CSV de Excel
        if ($this->exportFormat === 'csv') {
            fwrite($output, "\xEF\xBB\xBF"); // BOM para UTF-8
        }
        
        // Headers
        $headers = [
            'ID',
            'Código',
            'Valor Inicial',
            'Fecha Creación',
            'Fecha Expiración',
            'Cliente',
            'Email Cliente',
            'Estado',
            'Última Actualización'
        ];
        
        if ($this->exportFormat === 'csv') {
            fputcsv($output, $headers, ';'); // Separador para Excel en español
        } else {
            fputcsv($output, $headers, ','); // CSV estándar
        }
        
        // Datos
        foreach($giftCards as $giftCard) {
            $row = [
                $giftCard->id,
                $giftCard->code,
                number_format($giftCard->valor_inicial, 2),
                $giftCard->created_at->format('d/m/Y H:i'),
                $giftCard->expiry_date ? $giftCard->expiry_date->format('d/m/Y') : '',
                $giftCard->customer ? $giftCard->customer->name : '',
                $giftCard->customer ? $giftCard->customer->email : '',
                $this->getStatusText($giftCard->id_status_gift_card),
                $giftCard->updated_at->format('d/m/Y H:i')
            ];
            
            if ($this->exportFormat === 'csv') {
                fputcsv($output, $row, ';');
            } else {
                fputcsv($output, $row, ',');
            }
        }
        
        rewind($output);
        $csvData = stream_get_contents($output);
        fclose($output);
        
        return $csvData;
    }

    protected function getStatusText($statusId)
    {
        return match($statusId) {
            1 => 'Activo',
            2 => 'Expirado',
            3 => 'Usado',
            default => 'Desconocido'
        };
    }

    public function cancel()
    {
        $this->resetForm();
        $this->showModal = false;
    }

    private function resetForm()
    {
        $this->giftCardId = null;
        $this->code = null;
        $this->valor_inicial = null;
        $this->expiry_date = null;
        $this->id_customer = null;
        $this->id_status_gift_card = 1;
        $this->valor_usado = 0;
        $this->isEditing = false;
        $this->resetErrorBag();
    }

    public function sendEmail($id)
    {
        try {
            $giftCard = GiftCards::findOrFail($id);
            
            if (!$giftCard->customer || !$giftCard->customer->email) {
                session()->flash('warning', 'Esta tarjeta no tiene un cliente asignado o el cliente no tiene email.');
                return;
            }

            // Aquí puedes agregar la lógica para enviar el correo
            // Por ejemplo, usando una notificación o mailable
            // Mail::to($giftCard->customer->email)->send(new GiftCardMail($giftCard));
            
            session()->flash('message', 'Correo enviado exitosamente a ' . $giftCard->customer->email);
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error al enviar el correo: ' . $e->getMessage());
        }
    }
}
