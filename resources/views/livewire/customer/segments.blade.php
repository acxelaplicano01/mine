<div class="min-h-screen">
    <div class="px-2">
        {{-- Mensajes --}}
        @if (session()->has('message'))
            <div class="px-4 sm:px-6 lg:px-8 py-4">
                <flux:callout dismissible variant="success" icon="check-circle" heading="{{ session('message') }}" />
            </div>
        @endif

        @if (session()->has('error'))
            <div class="px-4 sm:px-6 lg:px-8 py-4">
                <flux:callout dismissible variant="danger" icon="x-circle" heading="{{ session('error') }}" />
            </div>
        @endif

        {{-- Header --}}
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Segmentos de clientes</h1>
                <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">Organiza tus clientes en grupos para aplicar descuentos y promociones dirigidas</p>
            </div>
            <div class="flex items-center gap-3">
                <flux:button variant="ghost" size="sm" wire:click="syncAutomaticSegments" icon="arrow-path">
                    Sincronizar automáticos
                </flux:button>
                <flux:button variant="filled" size="sm" wire:click="openExportModal" icon="arrow-up-tray">
                    Exportar
                </flux:button>
                <flux:button wire:click="openCreateModal" variant="primary" size="sm" icon="plus">
                    Crear segmento
                </flux:button>
            </div>
        </div>

        {{-- Tabla de segmentos --}}
        @php
            $columns = [
                ['key' => 'select', 'label' => '', 'sortable' => false],
                ['key' => 'name', 'label' => 'Segmento', 'sortable' => true],
                ['key' => 'customers_count', 'label' => 'Clientes', 'sortable' => true],
                ['key' => 'description', 'label' => 'Descripción', 'sortable' => false],
                ['key' => 'created_at', 'label' => 'Creado', 'sortable' => true],
                ['key' => 'actions', 'label' => '', 'sortable' => false],
            ];
        @endphp

        <x-saved-views-table 
            view-name="segmentos" 
            search-placeholder="Buscar segmentos"
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
                    wire:click="$set('filterType', 'all')"
                    class="px-3 py-1.5 text-sm font-medium rounded transition-colors whitespace-nowrap {{ $filterType === 'all' ? 'bg-zinc-900 text-white dark:bg-zinc-700' : 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700' }}"
                >
                    Todos
                </button>
                <button 
                    wire:click="$set('filterType', 'manual')"
                    class="px-3 py-1.5 text-sm font-medium rounded transition-colors whitespace-nowrap {{ $filterType === 'manual' ? 'bg-zinc-900 text-white dark:bg-zinc-700' : 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700' }}"
                >
                    Manuales
                </button>
                <button 
                    wire:click="$set('filterType', 'automatic')"
                    class="px-3 py-1.5 text-sm font-medium rounded transition-colors whitespace-nowrap {{ $filterType === 'automatic' ? 'bg-zinc-900 text-white dark:bg-zinc-700' : 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700' }}"
                >
                    Automáticos
                </button>
            </x-slot>

            {{-- Dropdown de filtros --}}
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
                            <div class="mb-2">
                                <flux:separator text="Tipo de segmento" />
                                <flux:menu.item wire:click="addFilter('tipo_manual', null, 'Tipo: Manual')">Segmentos manuales</flux:menu.item>
                                <flux:menu.item wire:click="addFilter('tipo_automatico', null, 'Tipo: Automático')">Segmentos automáticos</flux:menu.item>
                            </div>
                        </div>
                    </flux:menu>
                </flux:dropdown>
            </x-slot>

            {{-- Acciones masivas --}}
            <x-slot name="bulkActions">
                <flux:button wire:click="deleteSelected" wire:confirm="¿Estás seguro de eliminar los segmentos seleccionados?" size="xs" variant="danger">
                    Eliminar seleccionados
                </flux:button>
            </x-slot>

            {{-- Contenido de la tabla --}}
            <x-slot name="desktop">
                @forelse($segments as $segment)
                    @php
                        $isAutomatic = in_array($segment->name, [
                            App\Services\CustomerSegmentService::SEGMENT_NO_PURCHASES,
                            App\Services\CustomerSegmentService::SEGMENT_AT_LEAST_ONE,
                            App\Services\CustomerSegmentService::SEGMENT_MULTIPLE,
                        ]);
                    @endphp
                    <tr 
                        class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50 {{ in_array($segment->id, $selected) ? 'bg-lime-50 dark:bg-lime-900/20' : '' }}"
                    >
                        <td class="px-4 py-3">
                            @if(!$isAutomatic)
                                <flux:checkbox wire:model.live="selected" value="{{ $segment->id }}" />
                            @endif
                        </td>
                        
                        <td class="px-3 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-lime-100 dark:bg-lime-900 rounded flex items-center justify-center">
                                    <svg class="w-6 h-6 text-lime-600 dark:text-lime-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-zinc-900 dark:text-white flex items-center gap-2">
                                        {{ $segment->name }}
                                        @if($isAutomatic)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                Automático
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        
                        <td class="px-3 py-4">
                            <button 
                                wire:click="openViewModal('{{ addslashes($segment->name) }}')"
                                class="text-sm font-semibold text-lime-600 dark:text-lime-400 hover:underline"
                            >
                                {{ $segment->customers_count }} cliente{{ $segment->customers_count != 1 ? 's' : '' }}
                            </button>
                        </td>
                        
                        <td class="px-3 py-4 text-sm text-zinc-600 dark:text-zinc-400">
                            {{ Str::limit($segment->description, 80) }}
                        </td>
                        
                        <td class="px-3 py-4 text-sm text-zinc-600 dark:text-zinc-400">
                            {{ \Carbon\Carbon::parse($segment->created_at)->format('d/m/Y') }}
                        </td>
                        
                        <td class="px-3 py-4 text-right">
                            <flux:dropdown position="left">
                                <flux:button size="xs" variant="ghost" icon="ellipsis-horizontal" />
                                
                                <flux:menu>
                                    <flux:menu.item wire:click="openViewModal('{{ addslashes($segment->name) }}')" icon="eye">
                                        Ver clientes
                                    </flux:menu.item>
                                    
                                    @if(!$isAutomatic)
                                        <flux:menu.item wire:click="openEditModal('{{ addslashes($segment->name) }}')" icon="pencil">
                                            Editar
                                        </flux:menu.item>
                                        
                                        <flux:separator />
                                        
                                        <flux:menu.item wire:click="openDeleteModal('{{ addslashes($segment->name) }}')" icon="trash" variant="danger">
                                            Eliminar
                                        </flux:menu.item>
                                    @endif
                                </flux:menu>
                            </flux:dropdown>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-zinc-500 dark:text-zinc-400">
                            No se encontraron segmentos
                        </td>
                    </tr>
                @endforelse
            </x-slot>

            {{-- Paginación --}}
            <x-slot name="footer">
                {{ $segments->links() }}
            </x-slot>
        </x-saved-views-table>
    </div>

    {{-- Modal de exportación --}}
    <flux:modal wire:model="showExportModal" name="export-modal" class="min-w-[500px]">
        <div>
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Exportar segmentos</h2>
            </div>
            
            <div class="px-6 py-4 space-y-6">
                <div>
                    <flux:radio.group label="Exportar">
                        <flux:radio 
                            wire:model.live="exportOption" 
                            value="current_page" 
                            label="Página actual" />
                        
                        <flux:radio 
                            wire:model.live="exportOption" 
                            value="all" 
                            label="Todos los segmentos" />
                    
                        <flux:radio 
                            wire:model.live="exportOption" 
                            value="selected" 
                            label="Seleccionados: {{ count($selected) }} segmento{{ count($selected) != 1 ? 's' : '' }}"
                            :disabled="count($selected) === 0"
                        />
                        
                        <flux:radio 
                            wire:model.live="exportOption" 
                            value="search" 
                            label="Segmentos de búsqueda actual"
                            :disabled="empty($search)"
                        />
                        
                        <flux:radio 
                            wire:model.live="exportOption" 
                            value="filtered" 
                            label="Segmentos de la vista actual (con filtros)"
                            :disabled="count($activeFilters) === 0"
                        />
                    </flux:radio.group>
                </div>
                
                <div>
                    <flux:radio.group label="Exportar como">
                        <flux:radio 
                            wire:model.live="exportFormat" 
                            value="csv" 
                            label="CSV para Excel, Numbers u otros programas"
                        />
                        
                        <flux:radio 
                            wire:model.live="exportFormat" 
                            value="plain_csv" 
                            label="Archivo CSV sin formato"
                        />
                    </flux:radio.group>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-700 flex justify-between items-center">
                <flux:button type="button" wire:click="closeExportModal" variant="ghost">
                    Cancelar
                </flux:button>
                <flux:button 
                    icon="arrow-up-tray"
                    type="button" 
                    wire:click="exportSegments"
                    variant="primary"
                >
                    Exportar segmentos
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Modal de creación/edición --}}
    <flux:modal wire:model="{{ $showCreateModal ? 'showCreateModal' : 'showEditModal' }}" name="create-edit-modal" class="min-w-[600px]">
        <div>
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                    {{ $segmentId ? 'Editar segmento' : 'Crear segmento' }}
                </h2>
            </div>
            
            <div class="px-6 py-4 space-y-6">
                <flux:input 
                    wire:model="name" 
                    label="Nombre del segmento" 
                    placeholder="Ej: Clientes VIP"
                    required
                />
                
                <flux:textarea 
                    wire:model="description" 
                    label="Descripción" 
                    placeholder="Descripción del segmento"
                    rows="3"
                />
                
                <div>
                    <flux:label>Clientes en este segmento</flux:label>
                    <div class="mt-2 space-y-2">
                        <flux:input 
                            wire:model.live.debounce.300ms="searchCustomers" 
                            placeholder="Buscar clientes..."
                            icon="magnifying-glass"
                        />
                        
                        <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg max-h-[300px] overflow-y-auto">
                            @forelse($customers as $customer)
                                <label class="flex items-center gap-3 px-4 py-2 hover:bg-zinc-50 dark:hover:bg-zinc-800 cursor-pointer border-b border-zinc-100 dark:border-zinc-800 last:border-0">
                                    <flux:checkbox 
                                        wire:model.live="selectedCustomers" 
                                        value="{{ $customer->id }}"
                                    />
                                    <div class="flex-1">
                                        <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                            {{ $customer->name }} {{ $customer->last_name }}
                                        </div>
                                        @if($customer->email)
                                            <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                {{ $customer->email }}
                                            </div>
                                        @endif
                                    </div>
                                </label>
                            @empty
                                <div class="px-4 py-8 text-center text-zinc-500 dark:text-zinc-400 text-sm">
                                    No se encontraron clientes
                                </div>
                            @endforelse
                        </div>
                        
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">
                            {{ count($selectedCustomers) }} cliente(s) seleccionado(s)
                        </p>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-700 flex justify-between items-center">
                <flux:button type="button" wire:click="{{ $segmentId ? 'closeEditModal' : 'closeCreateModal' }}" variant="ghost">
                    Cancelar
                </flux:button>
                <flux:button 
                    type="button" 
                    wire:click="{{ $segmentId ? 'update' : 'save' }}"
                    variant="primary"
                >
                    {{ $segmentId ? 'Actualizar' : 'Crear' }} segmento
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Modal de visualización de clientes --}}
    <flux:modal wire:model="showViewModal" name="view-modal" class="min-w-[700px]">
        <div>
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                    Clientes en: {{ $selectedSegment }}
                </h2>
            </div>
            
            <div class="px-6 py-4">
                <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg max-h-[500px] overflow-y-auto">
                    <table class="w-full">
                        <thead class="bg-zinc-50 dark:bg-zinc-900 sticky top-0">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-700 dark:text-zinc-300">Cliente</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-700 dark:text-zinc-300">Email</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-zinc-700 dark:text-zinc-300">Pedidos</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-white/5 divide-y divide-zinc-200 dark:divide-zinc-700">
                            @forelse($segmentCustomers as $customer)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                    <td class="px-4 py-3 text-sm font-medium text-zinc-900 dark:text-white">
                                        {{ $customer->name }} {{ $customer->last_name }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">
                                        {{ $customer->email ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400 text-right">
                                        {{ $customer->orders_count ?? 0 }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-8 text-center text-zinc-500 dark:text-zinc-400">
                                        No hay clientes en este segmento
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-700 flex justify-end">
                <flux:button type="button" wire:click="closeViewModal" variant="primary">
                    Cerrar
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Modal de confirmación de eliminación --}}
    <flux:modal wire:model="showDeleteModal" name="delete-modal" class="min-w-[500px]">
        <div>
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Eliminar segmento</h2>
            </div>
            
            <div class="px-6 py-4">
                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                    ¿Estás seguro de que deseas eliminar el segmento <strong>"{{ $segmentToDelete }}"</strong>?
                </p>
                <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-2">
                    Esta acción no se puede deshacer.
                </p>
            </div>

            <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-700 flex justify-between items-center">
                <flux:button type="button" wire:click="closeDeleteModal" variant="ghost">
                    Cancelar
                </flux:button>
                <flux:button 
                    type="button" 
                    wire:click="delete"
                    variant="danger"
                >
                    Eliminar segmento
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
