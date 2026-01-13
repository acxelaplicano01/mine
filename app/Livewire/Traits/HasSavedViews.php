<?php

namespace App\Livewire\Traits;

use App\Models\UserSavedView;

trait HasSavedViews
{
    // Propiedades para vistas guardadas
    public $savedTabs = [];
    public $showSaveTabModal = false;
    public $newTabName = '';
    public $showRenameTabModal = false;
    public $renamingTabId = null;
    public $renameTabName = '';
    public $activeFilter = 'todos';
    public $showSearchBar = false;
    public $search = '';
    public $activeFilters = [];
    public $filterSearch = '';

    /**
     * Define el tipo de vista para este componente
     * Debe ser sobrescrito en el componente que usa el trait
     */
    protected function getViewType(): string
    {
        return 'default';
    }

    /**
     * Cargar vistas guardadas desde la base de datos
     */
    protected function loadSavedViews()
    {
        $views = UserSavedView::forUser(auth()->id())
            ->ofType($this->getViewType())
            ->orderBy('sort_order')
            ->orderBy('created_at')
            ->get();
        
        $this->savedTabs = [];
        foreach ($views as $view) {
            $this->savedTabs[$view->id] = [
                'id' => $view->id,
                'name' => $view->name,
                'filters' => $view->filters ?? [],
                'search' => $view->search ?? ''
            ];
        }
    }

    /**
     * Toggle barra de búsqueda
     */
    public function toggleSearchBar()
    {
        $this->showSearchBar = !$this->showSearchBar;
        if (!$this->showSearchBar) {
            $this->search = '';
            $this->activeFilters = [];
        }
    }

    /**
     * Establecer filtro activo
     */
    public function setFilter($filter)
    {
        $this->activeFilter = $filter;
        $this->resetPage();
    }

    /**
     * Agregar filtro
     */
    public function addFilter($filterType, $filterValue = null, $filterLabel = null)
    {
        $filterId = uniqid();
        $this->activeFilters[$filterId] = [
            'type' => $filterType,
            'value' => $filterValue,
            'label' => $filterLabel ?? $filterType
        ];
        $this->resetPage();
    }

    /**
     * Remover filtro específico
     */
    public function removeFilter($filterId)
    {
        unset($this->activeFilters[$filterId]);
        $this->resetPage();
    }

    /**
     * Limpiar todos los filtros
     */
    public function clearAllFilters()
    {
        $this->activeFilters = [];
        $this->resetPage();
    }

    /**
     * Abrir modal para guardar vista
     */
    public function openSaveTabModal()
    {
        $this->showSaveTabModal = true;
        $this->newTabName = '';
    }

    /**
     * Cerrar modal de guardar vista
     */
    public function closeSaveTabModal()
    {
        $this->showSaveTabModal = false;
        $this->newTabName = '';
    }

    /**
     * Guardar vista actual
     */
    public function saveCurrentView()
    {
        if (trim($this->newTabName) === '') {
            return;
        }
        
        UserSavedView::create([
            'user_id' => auth()->id(),
            'view_type' => $this->getViewType(),
            'name' => $this->newTabName,
            'filters' => $this->activeFilters,
            'search' => $this->search
        ]);
        
        $this->loadSavedViews();
        $this->closeSaveTabModal();
        $this->showSearchBar = false;
    }

    /**
     * Cargar vista guardada
     */
    public function loadTab($tabId)
    {
        if (isset($this->savedTabs[$tabId])) {
            $tab = $this->savedTabs[$tabId];
            $this->activeFilters = $tab['filters'];
            $this->search = $tab['search'] ?? '';
            $this->activeFilter = 'custom_' . $tabId;
            $this->showSearchBar = false;
            $this->resetPage();
        }
    }

    /**
     * Eliminar vista guardada
     */
    public function deleteTab($tabId)
    {
        UserSavedView::where('id', $tabId)
            ->where('user_id', auth()->id())
            ->delete();
        
        $this->loadSavedViews();
        
        if ($this->activeFilter === 'custom_' . $tabId) {
            $this->activeFilter = 'todos';
            $this->activeFilters = [];
            $this->search = '';
        }
    }

    /**
     * Abrir modal para renombrar vista
     */
    public function openRenameTabModal($tabId)
    {
        if (isset($this->savedTabs[$tabId])) {
            $this->renamingTabId = $tabId;
            $this->renameTabName = $this->savedTabs[$tabId]['name'];
            $this->showRenameTabModal = true;
        }
    }

    /**
     * Cerrar modal de renombrar
     */
    public function closeRenameTabModal()
    {
        $this->showRenameTabModal = false;
        $this->renamingTabId = null;
        $this->renameTabName = '';
    }

    /**
     * Renombrar vista
     */
    public function renameTab()
    {
        if (trim($this->renameTabName) === '' || !$this->renamingTabId) {
            return;
        }
        
        UserSavedView::where('id', $this->renamingTabId)
            ->where('user_id', auth()->id())
            ->update(['name' => $this->renameTabName]);
        
        $this->loadSavedViews();
        $this->closeRenameTabModal();
    }

    /**
     * Duplicar vista
     */
    public function duplicateTab($tabId)
    {
        $originalView = UserSavedView::where('id', $tabId)
            ->where('user_id', auth()->id())
            ->first();
        
        if ($originalView) {
            UserSavedView::create([
                'user_id' => auth()->id(),
                'view_type' => $originalView->view_type,
                'name' => $originalView->name . ' (copia)',
                'filters' => $originalView->filters,
                'search' => $originalView->search
            ]);
            
            $this->loadSavedViews();
        }
    }

    /**
     * Aplicar filtros a la consulta
     * Este método debe ser llamado desde render() del componente
     */
    protected function applySavedViewFilters($query)
    {
        // Solo aplicar filtros si hay una vista custom activa
        if (count($this->activeFilters) > 0 && str_starts_with($this->activeFilter, 'custom_')) {
            foreach($this->activeFilters as $filter) {
                // Este método debe ser sobrescrito en el componente para lógica específica
                $this->applyFilterToQuery($query, $filter);
            }
        }

        return $query;
    }

    /**
     * Método placeholder para aplicar filtro específico
     * Debe ser sobrescrito en el componente que usa el trait
     */
    protected function applyFilterToQuery($query, $filter)
    {
        // Implementar en el componente específico
        return $query;
    }
}
