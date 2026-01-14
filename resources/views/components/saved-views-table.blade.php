@props([
    'viewName' => 'tabla',
    'searchPlaceholder' => 'Buscar...',
    'saveButtonText' => 'Guardar vista de tabla',
    'columns' => [],
    'sortField' => '',
    'sortDirection' => 'asc',
    'showMobile' => false,
    'selectAll' => false,
    'selected' => [],
])

<div class="bg-white dark:bg-zinc-800 rounded-t-lg rounded-b-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden">
    {{-- Fila con filtros / búsqueda --}}
    <div class="border border-zinc-200 dark:border-zinc-700">
        @if($this->showSearchBar)
            {{-- Barra de búsqueda expandida --}}
            <div class="px-4 py-3">
                <div class="flex items-center gap-3 mb-3">
                    <div class="flex-1">
                        <flux:input 
                            wire:model.live.debounce.300ms="search"
                            :placeholder="$searchPlaceholder"
                            icon="magnifying-glass"
                            class="w-full"
                            autofocus
                        />
                    </div>
                    <flux:button wire:click="toggleSearchBar" variant="ghost">
                        Cancelar
                    </flux:button>
                    <flux:button 
                        wire:click="openSaveTabModal"
                        :disabled="(method_exists($this, 'getActiveViewId') && $this->getActiveViewId()) 
                            ? (method_exists($this, 'hasUnsavedChanges') && !$this->hasUnsavedChanges())
                            : (count($this->activeFilters) === 0)"
                    >
                        @if(method_exists($this, 'getActiveViewId') && $this->getActiveViewId())
                            Guardar
                        @else
                            {{ $saveButtonText }}
                        @endif
                    </flux:button>
                </div>
                
                {{-- Botón agregar filtro y filtros activos --}}
                <div class="flex items-center gap-2">
                    {{-- Dropdown de filtros (slot personalizable) --}}
                    {{ $filtersDropdown ?? '' }}
                    
                    {{-- Filtros activos --}}
                    @if(count($this->activeFilters) > 0)
                        @foreach($this->activeFilters as $filterId => $filter)
                            <div class="inline-flex items-center gap-2 px-3 py-1 bg-zinc-100 dark:bg-zinc-700 rounded text-sm text-zinc-700 dark:text-zinc-300">
                                <span>{{ $filter['label'] }}</span>
                                <button wire:click="removeFilter('{{ $filterId }}')" class="text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        @endforeach
                        <button wire:click="clearAllFilters" class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400">
                            Borrar todo
                        </button>
                    @endif
                </div>
            </div>
        @else
            {{-- Tabs de filtros --}}
            <div class="px-4 py-2">
                <div class="flex items-center gap-2 overflow-x-auto">
                    <flux:button wire:click="toggleSearchBar" icon="magnifying-glass" variant="filled" size="sm" class="flex-shrink-0">
                        Buscar {{ $viewName }}...
                    </flux:button>
                    
                    <div class="flex gap-1 flex-shrink-0">
                        {{-- Tabs predefinidos (slot personalizable) --}}
                        {{ $predefinedTabs }}
                        
                        {{-- Tabs guardados --}}
                        @foreach($this->savedTabs as $tab)
                            @if($this->activeFilter === 'custom_' . $tab['id'])
                                {{-- Tab activo con dropdown --}}
                                <flux:dropdown position="bottom" align="end">
                                    <button class="px-3 py-1.5 text-sm font-medium rounded transition-colors whitespace-nowrap inline-flex items-center gap-1 bg-zinc-900 text-white dark:bg-zinc-700">
                                        <span>{{ $tab['name'] }}</span>
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                    <flux:menu class="w-48">
                                        <flux:menu.item wire:click="openRenameTabModal('{{ $tab['id'] }}')" icon="pencil">
                                            Cambiar nombre de vista
                                        </flux:menu.item>
                                        <flux:menu.item wire:click="duplicateTab('{{ $tab['id'] }}')" icon="document-duplicate">
                                            Duplicar vista
                                        </flux:menu.item>
                                        <flux:menu.separator />
                                        <flux:menu.item wire:click="deleteTab('{{ $tab['id'] }}')" icon="trash" variant="danger">
                                            Eliminar vista
                                        </flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            @else
                                {{-- Tab inactivo, solo clic para activar --}}
                                <button 
                                    wire:click="loadTab('{{ $tab['id'] }}')"
                                    class="px-3 py-1.5 text-sm font-medium rounded transition-colors whitespace-nowrap text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700"
                                >
                                    {{ $tab['name'] }}
                                </button>
                            @endif
                        @endforeach
                    </div>
                    
                    {{-- Dropdown de filtros (slot personalizable para modo tabs) --}}
                    {{ $filtersDropdownCompact ?? '' }}
                </div>
            </div>
        @endif
    </div>

    {{-- Tabla integrada --}}
    <div class="overflow-x-auto">
        <table class="min-w-full border-x border-zinc-200 dark:border-zinc-700 divide-y divide-zinc-200 dark:divide-zinc-700">
            <thead class="bg-zinc-50 dark:bg-zinc-900">
                @if(count($selected) > 0)
                    {{-- Barra de acciones cuando hay elementos seleccionados --}}
                    <tr class="h-[52px]">
                        <th colspan="{{ count($columns) }}" class="px-4">
                            <div class="flex items-center justify-between h-[52px]">
                                <div class="flex items-center gap-4">
                                    <flux:checkbox wire:model.live="selectAll"  />
                                    <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                        {{ count($selected) }} seleccionado{{ count($selected) != 1 ? 's' : '' }}
                                    </span>
                                    
                                    <div class="flex items-center gap-2">
                                        <flux:button size="xs" wire:click="printDeliveryNotes">
                                            Imprimir
                                        </flux:button>
                                        
                                        <flux:dropdown>
                                            <flux:button icon:trailing="chevron-down" size="xs">
                                                Marcar como
                                            </flux:button>
                                            
                                            <flux:menu class="min-w-40">
                                                <flux:menu.item wire:click="markAsStatus('no_preparado')">
                                                    No preparado
                                                </flux:menu.item>
                                                <flux:menu.item wire:click="markAsStatus('en_preparacion')">
                                                    En preparación
                                                </flux:menu.item>
                                                <flux:menu.item wire:click="markAsStatus('preparado')">
                                                    Preparado
                                                </flux:menu.item>
                                                <flux:menu.item wire:click="markAsStatus('en_espera')">
                                                    En espera
                                                </flux:menu.item>
                                            </flux:menu>
                                        </flux:dropdown>
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-2">
                                    <label class="flex items-center gap-2 text-xs text-zinc-700 dark:text-zinc-300 cursor-pointer">
                                        <flux:switch wire:model.live="showOnlySelected" />
                                        <span>Mostrar sólo seleccionados</span>
                                    </label>
                                </div>
                            </div>
                        </th>
                    </tr>
                @else
                    {{-- Encabezados normales de la tabla --}}
                <tr class="h-[52px]">
                    @foreach ($columns as $index => $column)
                        <th scope="col"
                            class="px-4 text-left text-xs font-medium text-zinc-700 dark:text-zinc-300 {{ isset($column['sortable']) && $column['sortable'] ? 'cursor-pointer group' : '' }}"
                            @if(isset($column['sortable']) && $column['sortable'])
                                wire:click="{{ $column['sortBy'] ?? 'sortBy(\''.$column['key'].'\')' }}"
                            @endif
                        >
                            <div class="flex items-center">
                                @if($column['key'] === 'select')
                                    <flux:checkbox wire:model.live="selectAll" />
                                @else
                                    {{ $column['label'] }}
                                    @if(isset($column['sortable']) && $column['sortable'])
                                        @if($sortField === $column['key'])
                                            {{-- Mostrar flecha activa según dirección --}}
                                            @if($sortDirection === 'asc')
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-3 ml-1">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5 12 3m0 0 7.5 7.5M12 3v18" />
                                                </svg>
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-3 ml-1">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5 12 21m0 0-7.5-7.5M12 21V3" />
                                                </svg>
                                            @endif
                                        @else
                                            {{-- Mostrar flecha en hover para columnas sortables --}}
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-3 ml-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15 12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                                            </svg>
                                        @endif
                                    @endif
                                @endif
                            </div>
                        </th>
                    @endforeach
                </tr>
                @endif
            </thead>
            <tbody class="divide-y divide-zinc-200 bg-white dark:bg-zinc-800 dark:divide-zinc-700">
                {{ $desktop ?? $slot }}
            </tbody>
        </table>
    </div>

    {{-- Footer con paginación --}}
    <div class="bg-white dark:bg-white/5 rounded-b-lg px-4 py-3 border border-zinc-200 dark:border-zinc-700">
        {{ $footer ?? '' }}
    </div>
    
    {{-- Modal para guardar vista personalizada --}}
    <flux:modal wire:model="showSaveTabModal" class="min-w-[400px]">
        <div>
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Guardar vista</h2>
            </div>
            
            <div class="px-6 py-4">
                <flux:input 
                    wire:model.live="newTabName"
                    label="Nombre de la vista"
                    placeholder="Ej: Pedidos urgentes"
                    class="w-full"
                />
                <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-2">
                    Esta vista guardará los filtros activos para acceder rápidamente después.
                </p>
            </div>

            <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-700 flex justify-end gap-3">
                <flux:button type="button" wire:click="closeSaveTabModal" variant="ghost">
                    Cancelar
                </flux:button>
                <flux:button 
                    type="button" 
                    wire:click="saveCurrentView"
                    variant="primary"
                >
                    Guardar vista
                </flux:button>
            </div>
        </div>
    </flux:modal>
    
    {{-- Modal para renombrar vista --}}
    <flux:modal wire:model="showRenameTabModal" class="min-w-[400px]">
        <div>
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Cambiar nombre de vista</h2>
            </div>
            
            <div class="px-6 py-4">
                <flux:input 
                    wire:model.live="renameTabName"
                    label="Nombre de la vista"
                    placeholder="Ej: Pedidos urgentes"
                    class="w-full"
                />
            </div>

            <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-700 flex justify-end gap-3">
                <flux:button type="button" wire:click="closeRenameTabModal" variant="ghost">
                    Cancelar
                </flux:button>
                <flux:button 
                    type="button" 
                    wire:click="renameTab"
                    variant="primary"
                >
                    Guardar cambios
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
