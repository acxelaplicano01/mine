<div class="min-h-screen">
    {{-- Header --}}
    <div class="">
        <div class="px-2 sm:px-4 lg:px-2">
            <div class="flex justify-between items-center mb-4">
               <div>
                    <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Mercados</h1>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">Administra los mercados de tu tienda</p>
                </div>
                <div class="flex items-center gap-3">
                    <flux:button variant="filled" size="sm" wire:click="openExportModal" icon="arrow-up-tray">
                        Exportar
                    </flux:button>
                    <flux:button wire:click="create" icon="plus" variant="primary" size="sm">
                        Nuevo mercado
                    </flux:button>
                </div>
            </div>
        </div>
    </div>

    {{-- Contenido principal --}}
    <div class="px-2">
        {{-- Tabla con saved views --}}
        @php
            $columns = [
                ['key' => 'select', 'label' => '', 'sortable' => false],
                ['key' => 'id', 'label' => 'ID', 'sortable' => true],
                ['key' => 'name', 'label' => 'Nombre', 'sortable' => true],
                ['key' => 'domain', 'label' => 'Dominio', 'sortable' => true],
                ['key' => 'moneda', 'label' => 'Moneda', 'sortable' => false],
                ['key' => 'status', 'label' => 'Estado', 'sortable' => true, 'sortBy' => 'sortBy(\'id_status_market\')'],
                ['key' => 'created_at', 'label' => 'Fecha de creación', 'sortable' => true],
                ['key' => 'actions', 'label' => '', 'sortable' => false],
            ];
        @endphp

        <x-saved-views-table 
            view-name="mercados" 
            search-placeholder="Buscar mercados"
            save-button-text="Guardar vista de tabla"
            :columns="$columns"
            :sort-field="$sortField"
            :sort-direction="$sortDirection"
            :show-mobile="true"
            :select-all="$selectAll"
            :selected="$selected"
        >
            {{-- Tabs predefinidos --}}
            <x-slot name="predefinedTabs">
                <button 
                    wire:click="setFilter('todos')"
                    class="px-3 py-1.5 text-sm font-medium rounded transition-colors whitespace-nowrap {{ $activeFilter === 'todos' ? 'bg-zinc-900 text-white dark:bg-zinc-700' : 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700' }}"
                >
                    Todos
                </button>
                <button 
                    wire:click="setFilter('activos')"
                    class="px-3 py-1.5 text-sm font-medium rounded transition-colors whitespace-nowrap {{ $activeFilter === 'activos' ? 'bg-zinc-900 text-white dark:bg-zinc-700' : 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700' }}"
                >
                    Activos
                </button>
                <button 
                    wire:click="setFilter('inactivos')"
                    class="px-3 py-1.5 text-sm font-medium rounded transition-colors whitespace-nowrap {{ $activeFilter === 'inactivos' ? 'bg-zinc-900 text-white dark:bg-zinc-700' : 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700' }}"
                >
                    Inactivos
                </button>
            </x-slot>

            {{-- Dropdown de filtros (modo búsqueda) --}}
            <x-slot name="filtersDropdown">
                <flux:dropdown class="flex-shrink-0">
                    <flux:button size="sm" class="px-3 py-1.5 text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 rounded transition-colors whitespace-nowrap flex items-center gap-1">
                        Agregar filtro
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </flux:button>
                    
                    <flux:menu class="w-80">
                        <div class="p-3">
                            <flux:input 
                                wire:model.live.debounce.300ms="filterSearch"
                                placeholder="Buscar..."
                                icon="magnifying-glass"
                                class="mb-2"
                            />
                            
                            <div class="max-h-96 overflow-y-auto">
                                {{-- Estado --}}
                                <div class="mb-2">
                                    <flux:separator text="Estado" />
                                    <flux:menu.item wire:click="addFilter('estado_activo', null, 'Estado: Activo')">
                                        Activo
                                    </flux:menu.item>
                                    <flux:menu.item wire:click="addFilter('estado_inactivo', null, 'Estado: Inactivo')">
                                        Inactivo
                                    </flux:menu.item>
                                </div>
                                
                                {{-- Monedas --}}
                                <div class="mb-2">
                                    <flux:separator text="Moneda" />
                                    @foreach($monedas as $moneda)
                                        <flux:menu.item wire:click="addFilter('moneda', {{ $moneda->id }}, 'Moneda: {{ $moneda->name }}')">
                                            {{ $moneda->name }}
                                        </flux:menu.item>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </flux:menu>
                </flux:dropdown>
            </x-slot>

            {{-- Acciones masivas --}}
            <x-slot name="bulkActions">
                <flux:dropdown>
                    <flux:button size="xs" icon="ellipsis-horizontal" variant="ghost">
                        Acciones
                    </flux:button>
                    
                    <flux:menu>
                        <flux:menu.item wire:click="markAsStatus(1)" icon="check-circle">
                            Marcar como activos
                        </flux:menu.item>
                        <flux:menu.item wire:click="markAsStatus(0)" icon="x-circle">
                            Marcar como inactivos
                        </flux:menu.item>
                    </flux:menu>
                </flux:dropdown>
            </x-slot>

            {{-- Contenido de la tabla --}}
            <x-slot name="desktop">
                @forelse($markets as $market)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50 {{ in_array($market->id, $selected) ? 'bg-lime-50 dark:bg-lime-900/20' : '' }}">
                        <td class="px-4 py-3">
                            <flux:checkbox wire:model.live.debounce.150ms="selected" value="{{ $market->id }}" />
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                #{{ str_pad($market->id, 4, '0', STR_PAD_LEFT) }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                {{ $market->name }}
                            </div>
                            @if($market->description)
                                <div class="text-xs text-zinc-500 dark:text-zinc-400 truncate max-w-xs">
                                    {{ $market->description }}
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-zinc-900 dark:text-white">
                                {{ $market->domain ?? '—' }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-zinc-900 dark:text-white">
                                {{ $market->moneda->nombre ?? '—' }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <flux:badge size="sm" :color="$market->id_status_market == 1 ? 'green' : 'red'" variant="soft">
                                {{ $market->id_status_market == 1 ? 'Activo' : 'Inactivo' }}
                            </flux:badge>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-zinc-900 dark:text-white">
                                {{ $market->created_at?->format('d M, Y') }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <flux:button variant="ghost" size="xs" wire:click="edit({{ $market->id }})" icon="pencil-square">
                                    Editar
                                </flux:button>
                                <flux:button variant="ghost" size="xs" wire:click="delete({{ $market->id }})" icon="trash" onclick="confirm('¿Estás seguro?') || event.stopImmediatePropagation()">
                                    Eliminar
                                </flux:button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-zinc-500 dark:text-zinc-400">
                            No se encontraron mercados
                        </td>
                    </tr>
                @endforelse
            </x-slot>

            {{-- Vista móvil --}}
            <x-slot name="mobile">
                @forelse($markets as $market)
                    <div class="p-4 border-b border-zinc-200 dark:border-zinc-700 {{ in_array($market->id, $selected) ? 'bg-lime-50 dark:bg-lime-900/20' : '' }}">
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex items-center gap-3">
                                <flux:checkbox wire:model.live.debounce.150ms="selected" value="{{ $market->id }}" />
                                <div>
                                    <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                        {{ $market->name }}
                                    </div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                        #{{ str_pad($market->id, 4, '0', STR_PAD_LEFT) }}
                                    </div>
                                </div>
                            </div>
                            <flux:badge size="sm" :color="$market->id_status_market == 1 ? 'green' : 'red'" variant="soft">
                                {{ $market->id_status_market == 1 ? 'Activo' : 'Inactivo' }}
                            </flux:badge>
                        </div>
                        
                        <div class="space-y-1 text-xs text-zinc-600 dark:text-zinc-400">
                            @if($market->domain)
                                <div>
                                    <strong>Dominio:</strong> {{ $market->domain }}
                                </div>
                            @endif
                            @if($market->moneda)
                                <div>
                                    <strong>Moneda:</strong> {{ $market->moneda->nombre }}
                                </div>
                            @endif
                            @if($market->description)
                                <div>
                                    <strong>Descripción:</strong> {{ Str::limit($market->description, 50) }}
                                </div>
                            @endif
                            <div>
                                <strong>Creado:</strong> {{ $market->created_at?->format('d M, Y') }}
                            </div>
                        </div>
                        
                        <div class="flex justify-end gap-2 mt-3 pt-3 border-t border-zinc-200 dark:border-zinc-700">
                            <flux:button variant="ghost" size="xs" wire:click="edit({{ $market->id }})" icon="pencil-square">
                                Editar
                            </flux:button>
                            <flux:button variant="ghost" size="xs" wire:click="delete({{ $market->id }})" icon="trash" onclick="confirm('¿Estás seguro?') || event.stopImmediatePropagation()">
                                Eliminar
                            </flux:button>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-zinc-500 dark:text-zinc-400">
                        No se encontraron mercados
                    </div>
                @endforelse
            </x-slot>
        </x-saved-views-table>
    </div>

    {{-- Modal de creación/edición --}}
    <flux:modal wire:model="showModal" class="max-w-lg">
        <div class="bg-white dark:bg-zinc-800 rounded-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <flux:heading size="lg">
                    {{ $isEditing ? 'Editar Mercado' : 'Nuevo Mercado' }}
                </flux:heading>
                <flux:button wire:click="cancel" variant="ghost" size="sm" icon="x-mark" />
            </div>

            <div class="space-y-4">
                <div>
                    <flux:input wire:model="name" label="Nombre" placeholder="Ej: Tienda Principal" required />
                    <flux:error name="name" />
                </div>

                <div>
                    <flux:textarea wire:model="description" label="Descripción" placeholder="Descripción del mercado..." rows="3" />
                    <flux:error name="description" />
                </div>

                <div>
                    <flux:input wire:model="domain" label="Dominio" placeholder="Ej: tienda.com" />
                    <flux:error name="domain" />
                </div>

                <div>
                    <flux:select wire:model="id_moneda" label="Moneda" placeholder="Seleccionar moneda">
                        <flux:select.option value="">Seleccionar moneda</flux:select.option>
                        @foreach($monedas as $moneda)
                            <flux:select.option value="{{ $moneda->id }}">{{ $moneda->nombre }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="id_moneda" />
                </div>

                <div>
                    <flux:select wire:model="id_status_market" label="Estado" required>
                        <flux:select.option value="1">Activo</flux:select.option>
                        <flux:select.option value="0">Inactivo</flux:select.option>
                    </flux:select>
                    <flux:error name="id_status_market" />
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <flux:button type="button" wire:click="cancel" variant="ghost">
                    Cancelar
                </flux:button>
                <flux:button type="button" wire:click="save" variant="primary">
                    {{ $isEditing ? 'Actualizar' : 'Crear' }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Modal de exportación --}}
    <flux:modal wire:model="showExportModal" class="max-w-lg">
        <div class="bg-white dark:bg-zinc-800 rounded-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <flux:heading size="lg">Exportar mercados</flux:heading>
                <flux:button wire:click="closeExportModal" variant="ghost" size="sm" icon="x-mark" />
            </div>

            <div class="space-y-4">
                {{-- Opciones de exportación --}}
                <div>
                    <flux:radio.group label="¿Qué mercados exportar?">
                        <flux:radio 
                            wire:model.live="exportOption" 
                            value="current_page" 
                            label="Página actual"
                        />
                        
                        <flux:radio 
                            wire:model.live="exportOption" 
                            value="all" 
                            label="Todos los mercados"
                        />
                        
                        <flux:radio 
                            wire:model.live="exportOption" 
                            value="selected" 
                            label="Mercados seleccionados"
                            :disabled="count($selected) === 0"
                        />
                        
                        <flux:radio 
                            wire:model.live="exportOption" 
                            value="search" 
                            label="Mercados de búsqueda actual"
                            :disabled="empty($search)"
                        />
                        
                        <flux:radio 
                            wire:model.live="exportOption" 
                            value="filtered" 
                            label="Mercados de la vista actual (con filtros)"
                            :disabled="count($activeFilters) === 0 && empty($activeFilter)"
                        />
                    </flux:radio.group>
                </div>
                
                {{-- Formato de exportación --}}
                <div>
                    <flux:radio.group label="Exportar como">
                        <flux:radio 
                            wire:model.live="exportFormat" 
                            value="csv" 
                            label="CSV para Excel, Numbers u otros programas de hojas de cálculo"
                        />
                        
                        <flux:radio 
                            wire:model.live="exportFormat" 
                            value="plain_csv" 
                            label="Archivo CSV sin formato"
                        />
                    </flux:radio.group>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <flux:button type="button" wire:click="closeExportModal" variant="ghost">
                    Cancelar
                </flux:button>
                <flux:button type="button" wire:click="export" variant="primary">
                    Exportar
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
