<div class="min-h-screen">
    {{-- Mensaje de éxito --}}
    @if (session()->has('message'))
        <div class="px-4 sm:px-6 lg:px-8 py-4">
            <flux:callout dismissible variant="success" icon="check-circle" heading="{{ session('message') }}" />
        </div>
    @endif

    {{-- Header principal --}}
    <div>
        <div class="px-2 sm:px-4 lg:px-2">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Transferencias</h1>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">Administra las transferencias de productos entre sucursales</p>
                </div>
                <div class="flex items-center gap-3">
                    <flux:button variant="filled" size="sm" wire:click="openExportModal" icon="arrow-up-tray">
                        Exportar
                    </flux:button>
                    <flux:dropdown>
                        <flux:button variant="filled" size="sm" icon-trailing="chevron-down">
                            Más acciones
                        </flux:button>
                        <flux:menu>
                            <flux:menu.item>Exportar seleccionados</flux:menu.item>
                            <flux:menu.item>Imprimir etiquetas</flux:menu.item>
                            <flux:menu.item>Marcar como completado</flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>
                    <flux:button wire:click="create" icon="plus" variant="primary" size="sm">
                        Crear transferencia
                    </flux:button>
                </div>
            </div>
        </div>
    </div>

    {{-- Contenido principal --}}
    <div class="px-2">
        {{-- Tabla de transferencias --}}
        @php
            $columns = [
                ['key' => 'select', 'label' => '', 'sortable' => false],
                ['key' => 'id', 'label' => 'ID', 'sortable' => true],
                ['key' => 'fecha', 'label' => 'Fecha', 'sortable' => true],
                ['key' => 'referencia', 'label' => 'Referencia', 'sortable' => true],
                ['key' => 'producto', 'label' => 'Producto', 'sortable' => true],
                ['key' => 'cantidad', 'label' => 'Cantidad', 'sortable' => true],
                ['key' => 'origen', 'label' => 'Origen', 'sortable' => false],
                ['key' => 'destino', 'label' => 'Destino', 'sortable' => false],
                ['key' => 'estado', 'label' => 'Estado', 'sortable' => true],
                ['key' => 'acciones', 'label' => 'Acciones', 'sortable' => false],
            ];
        @endphp

        <x-saved-views-table 
            view-name="transferencias" 
            search-placeholder="Buscar transferencias"
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
                    wire:click="setFilter('pendientes')"
                    class="px-3 py-1.5 text-sm font-medium rounded transition-colors whitespace-nowrap {{ $activeFilter === 'pendientes' ? 'bg-zinc-900 text-white dark:bg-zinc-700' : 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700' }}"
                >
                    Pendientes
                </button>
                <button 
                    wire:click="setFilter('en_transito')"
                    class="px-3 py-1.5 text-sm font-medium rounded transition-colors whitespace-nowrap {{ $activeFilter === 'en_transito' ? 'bg-zinc-900 text-white dark:bg-zinc-700' : 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700' }}"
                >
                    En tránsito
                </button>
                <button 
                    wire:click="setFilter('completados')"
                    class="px-3 py-1.5 text-sm font-medium rounded transition-colors whitespace-nowrap {{ $activeFilter === 'completados' ? 'bg-zinc-900 text-white dark:bg-zinc-700' : 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700' }}"
                >
                    Completados
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
                                    <flux:menu.item wire:click="addFilter('estado_pendiente', null, 'Estado: Pendiente')">
                                        Pendiente
                                    </flux:menu.item>
                                    <flux:menu.item wire:click="addFilter('estado_en_transito', null, 'Estado: En tránsito')">
                                        En tránsito
                                    </flux:menu.item>
                                    <flux:menu.item wire:click="addFilter('estado_completado', null, 'Estado: Completado')">
                                        Completado
                                    </flux:menu.item>
                                </div>
                                
                                {{-- Productos --}}
                                <div class="mb-2">
                                    <flux:separator text="Producto" />
                                    @foreach($products->take(10) as $product)
                                        <flux:menu.item wire:click="addFilter('producto', {{ $product->id }}, 'Producto: {{ $product->name }}')">
                                            {{ $product->name }}
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
                <flux:button size="xs" icon="printer">
                    Imprimir
                </flux:button>
                
                <flux:dropdown>
                    <flux:button icon:trailing="chevron-down" size="xs">
                        Marcar como
                    </flux:button>
                    
                    <flux:menu class="min-w-40">
                        @foreach($statuses as $status)
                            <flux:menu.item wire:click="markAsStatus({{ $status->id }})">
                                {{ $status->name }}
                            </flux:menu.item>
                        @endforeach
                    </flux:menu>
                </flux:dropdown>
            </x-slot>

            {{-- Contenido de la tabla --}}
            <x-slot name="desktop">
                @forelse($transfers as $transfer)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50 {{ in_array($transfer->id, $selected) ? 'bg-lime-50 dark:bg-lime-900/20' : '' }}">
                        <td class="px-4 py-3">
                            <flux:checkbox wire:model.live.debounce.150ms="selected" value="{{ $transfer->id }}" />
                        </td>
                        <td class="px-4 py-3">
                            <a href="#" class="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400">
                                #{{ str_pad($transfer->id, 4, '0', STR_PAD_LEFT) }}
                            </a>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-zinc-900 dark:text-white">
                                {{ $transfer->created_at->isToday() ? 'Hoy a las ' . $transfer->created_at->format('H:i') : $transfer->created_at->format('d M, Y H:i') }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-zinc-900 dark:text-white">
                                {{ $transfer->nombre_referencia ?? '—' }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-zinc-900 dark:text-white">
                                {{ $transfer->product->name ?? 'Sin producto' }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                {{ $transfer->cantidad }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-zinc-900 dark:text-white">
                                {{ $transfer->sucursalOrigen->nombre ?? '—' }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-zinc-900 dark:text-white">
                                {{ $transfer->sucursalDestino->nombre ?? '—' }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            @if($transfer->status)
                                <div class="inline-flex items-center gap-2 px-2 py-1 
                                    @if($transfer->status->id === 1) bg-yellow-100 dark:bg-yellow-900/30
                                    @elseif($transfer->status->id === 2) bg-blue-100 dark:bg-blue-900/30
                                    @elseif($transfer->status->id === 3) bg-green-100 dark:bg-green-900/30
                                    @else bg-zinc-100 dark:bg-zinc-900/30
                                    @endif rounded">
                                    <span class="w-2 h-2 rounded-full 
                                        @if($transfer->status->id === 1) bg-yellow-500
                                        @elseif($transfer->status->id === 2) bg-blue-500
                                        @elseif($transfer->status->id === 3) bg-green-500
                                        @else bg-zinc-500
                                        @endif">
                                    </span>
                                    <span class="text-sm text-zinc-900 dark:text-white">
                                        {{ $transfer->status->name }}
                                    </span>
                                </div>
                            @else
                                <span class="text-sm text-zinc-500 dark:text-zinc-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <flux:button size="xs" icon="pencil" wire:click="edit({{ $transfer->id }})" variant="ghost">
                                    Editar
                                </flux:button>
                                <flux:button size="xs" icon="trash" wire:click="delete({{ $transfer->id }})" variant="ghost" class="text-red-600">
                                    Eliminar
                                </flux:button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-4 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                            No hay transferencias registradas
                        </td>
                    </tr>
                @endforelse
            </x-slot>

            <x-slot name="footer">
                @if($transfers->hasPages())
                    {{ $transfers->links() }}
                @endif
            </x-slot>
        </x-saved-views-table>
    </div>

    {{-- Modal de exportación --}}
    <flux:modal wire:model="showExportModal" name="export-modal" class="min-w-[500px]">
        <div>
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Exportar transferencias</h2>
            </div>
            
            <div class="px-6 py-4 space-y-6">
                {{-- Opciones de exportación --}}
                <div>
                    <flux:radio.group label="Exportar">
                        <flux:radio 
                            wire:model.live="exportOption" 
                            value="current_page" 
                            label="Página actual" />
                        
                        <flux:radio 
                            wire:model.live="exportOption" 
                            value="all" 
                            label="Todas las transferencias" />
                    
                        <flux:radio 
                            wire:model.live="exportOption" 
                            value="selected" 
                            label="Seleccionados: {{ count($selected) }} transferencia{{ count($selected) != 1 ? 's' : '' }}"
                            :disabled="count($selected) === 0"
                        />
                        
                        <flux:radio 
                            wire:model.live="exportOption" 
                            value="search" 
                            label="Transferencias de búsqueda actual"
                            :disabled="empty($search)"
                        />
                        
                        <flux:radio 
                            wire:model.live="exportOption" 
                            value="filtered" 
                            label="Transferencias de la vista actual (con filtros)"
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

            <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-700 flex justify-between items-center">
                <flux:button type="button" wire:click="closeExportModal" variant="ghost">
                    Cancelar
                </flux:button>
                <flux:button 
                    icon="arrow-up-tray"
                    type="button" 
                    wire:click="exportTransfers"
                    variant="primary"
                >
                    Exportar transferencias
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Modal de crear/editar --}}
    <flux:modal wire:model="showModal" name="transfer-modal" class="min-w-[600px]">
        <div>
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                    {{ $isEditing ? 'Editar' : 'Crear' }} transferencia
                </h2>
            </div>
            
            <div class="px-6 py-4 space-y-4">
                <flux:input 
                    wire:model="nombre_referencia"
                    label="Referencia"
                    placeholder="TRANS-001"
                />

                <div class="grid grid-cols-2 gap-4">
                    <flux:select 
                        wire:model="id_sucursal_origen"
                        label="Sucursal origen"
                        placeholder="Seleccionar..."
                    >
                        @foreach($sucursales as $sucursal)
                            <flux:select.option value="{{ $sucursal->id }}">
                                {{ $sucursal->nombre }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    
                    <flux:select 
                        wire:model="id_sucursal_destino"
                        label="Sucursal destino"
                        placeholder="Seleccionar..."
                    >
                        @foreach($sucursales as $sucursal)
                            <flux:select.option value="{{ $sucursal->id }}">
                                {{ $sucursal->nombre }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                <flux:select 
                    wire:model="id_product"
                    label="Producto"
                    placeholder="Seleccionar producto..."
                >
                    @foreach($products as $product)
                        <flux:select.option value="{{ $product->id }}">
                            {{ $product->name }}
                        </flux:select.option>
                    @endforeach
                </flux:select>

                <div class="grid grid-cols-2 gap-4">
                    <flux:input 
                        wire:model="cantidad"
                        type="number"
                        label="Cantidad"
                        placeholder="1"
                        min="1"
                    />
                    
                    <flux:input 
                        wire:model="fecha_envio_creacion"
                        type="date"
                        label="Fecha de envío"
                    />
                </div>

                <flux:select 
                    wire:model="id_status_transfer"
                    label="Estado"
                >
                    @foreach($statuses as $status)
                        <flux:select.option value="{{ $status->id }}">
                            {{ $status->name }}
                        </flux:select.option>
                    @endforeach
                </flux:select>

                <flux:textarea 
                    wire:model="nota_interna"
                    label="Nota interna"
                    placeholder="Observaciones..."
                    rows="4"
                />
            </div>

            <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-700 flex justify-between items-center">
                <flux:button type="button" wire:click="cancel" variant="ghost">
                    Cancelar
                </flux:button>
                <flux:button 
                    type="button" 
                    wire:click="{{ $isEditing ? 'update' : 'store' }}"
                    variant="primary"
                >
                    {{ $isEditing ? 'Actualizar' : 'Crear' }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
