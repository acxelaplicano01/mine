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
                    <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Órdenes de compra</h1>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">Administra las órdenes de compra a distribuidores</p>
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
                            <flux:menu.item>Imprimir órdenes</flux:menu.item>
                            <flux:menu.item>Marcar como recibido</flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>
                    <flux:button wire:click="create" icon="plus" variant="primary" size="sm">
                        Crear orden de compra
                    </flux:button>
                </div>
            </div>
        </div>
    </div>

    {{-- Contenido principal --}}
    <div class="px-2">
        {{-- Tabla de órdenes de compra --}}
        @php
            $columns = [
                ['key' => 'select', 'label' => '', 'sortable' => false],
                ['key' => 'id', 'label' => 'ID', 'sortable' => true],
                ['key' => 'fecha', 'label' => 'Fecha', 'sortable' => true],
                ['key' => 'referencia', 'label' => 'Referencia', 'sortable' => true],
                ['key' => 'distribuidor', 'label' => 'Distribuidor', 'sortable' => true],
                ['key' => 'estado', 'label' => 'Estado', 'sortable' => true],
                ['key' => 'total', 'label' => 'Total', 'sortable' => true],
                ['key' => 'fecha_estimada', 'label' => 'Fecha estimada', 'sortable' => true],
                ['key' => 'acciones', 'label' => 'Acciones', 'sortable' => false],
            ];
        @endphp

        <x-saved-views-table 
            view-name="ordenes_compra" 
            search-placeholder="Buscar órdenes de compra"
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
                    wire:click="setFilter('borrador')"
                    class="px-3 py-1.5 text-sm font-medium rounded transition-colors whitespace-nowrap {{ $activeFilter === 'borrador' ? 'bg-zinc-900 text-white dark:bg-zinc-700' : 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700' }}"
                >
                    En borrador
                </button>
                <button 
                    wire:click="setFilter('recibidos')"
                    class="px-3 py-1.5 text-sm font-medium rounded transition-colors whitespace-nowrap {{ $activeFilter === 'recibidos' ? 'bg-zinc-900 text-white dark:bg-zinc-700' : 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700' }}"
                >
                    Recibidos
                </button>
                <button 
                    wire:click="setFilter('solicitados')"
                    class="px-3 py-1.5 text-sm font-medium rounded transition-colors whitespace-nowrap {{ $activeFilter === 'solicitados' ? 'bg-zinc-900 text-white dark:bg-zinc-700' : 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700' }}"
                >
                    Solicitados
                </button>
                <button 
                    wire:click="setFilter('cancelados')"
                    class="px-3 py-1.5 text-sm font-medium rounded transition-colors whitespace-nowrap {{ $activeFilter === 'cancelados' ? 'bg-zinc-900 text-white dark:bg-zinc-700' : 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700' }}"
                >
                    Cancelados
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
                                {{-- Distribuidores --}}
                                <div class="mb-2">
                                    <flux:separator text="Distribuidor" />
                                    @foreach($distribuidores->take(10) as $distribuidor)
                                        <flux:menu.item wire:click="addFilter('distribuidor', {{ $distribuidor->id }}, 'Distribuidor: {{ $distribuidor->name ?? 'Sin nombre' }}')">
                                            {{ $distribuidor->name ?? 'Sin nombre' }}
                                        </flux:menu.item>
                                    @endforeach
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
                        <flux:menu.item>Pendiente</flux:menu.item>
                        <flux:menu.item>Recibido</flux:menu.item>
                        <flux:menu.item>Cancelado</flux:menu.item>
                    </flux:menu>
                </flux:dropdown>
            </x-slot>

            {{-- Contenido de la tabla --}}
            <x-slot name="desktop">
                @forelse($orders as $order)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50 {{ in_array($order->id, $selected) ? 'bg-lime-50 dark:bg-lime-900/20' : '' }}">
                        <td class="px-4 py-3">
                            <flux:checkbox wire:model.live.debounce.150ms="selected" value="{{ $order->id }}" />
                        </td>
                        <td class="px-4 py-3">
                            <a href="#" class="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400">
                                #{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }}
                            </a>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-zinc-900 dark:text-white">
                                {{ $order->created_at->isToday() ? 'Hoy a las ' . $order->created_at->format('H:i') : $order->created_at->format('d M, Y H:i') }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-zinc-900 dark:text-white">
                                {{ $order->numero_referencia ?? '—' }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-zinc-900 dark:text-white">
                                {{ $order->distribuidor->name ?? 'Sin distribuidor' }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $estadoBadge = [
                                    'borrador' => ['bg' => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200', 'label' => 'Borrador'],
                                    'solicitado' => ['bg' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200', 'label' => 'Solicitado'],
                                    'recibido' => ['bg' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200', 'label' => 'Recibido'],
                                    'cancelado' => ['bg' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200', 'label' => 'Cancelado'],
                                ];
                                $estado = $estadoBadge[$order->estado] ?? ['bg' => 'bg-gray-100 text-gray-800', 'label' => ucfirst($order->estado)];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $estado['bg'] }}">
                                {{ $estado['label'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                {{ number_format($order->total ?? 0, 2) }} L
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-zinc-900 dark:text-white">
                                {{ $order->fecha_llegada_estimada ? date('d M, Y', strtotime($order->fecha_llegada_estimada)) : '—' }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                @if($order->estado === 'borrador')
                                    <flux:button 
                                        size="xs" 
                                        wire:click="marcarComoPedido({{ $order->id }})" 
                                        variant="filled"
                                        class="bg-blue-600 hover:bg-blue-700 text-white"
                                    >
                                        Marcar como pedido
                                    </flux:button>
                                @elseif($order->estado === 'solicitado')
                                    <flux:button 
                                        size="xs" 
                                        wire:click="recibirInventario({{ $order->id }})" 
                                        variant="filled"
                                        class="bg-green-600 hover:bg-green-700 text-white"
                                    >
                                        Recibir inventario
                                    </flux:button>
                                @elseif($order->estado === 'recibido')
                                    <span class="text-xs text-zinc-500 dark:text-zinc-400">Completado</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                            No hay órdenes de compra registradas
                        </td>
                    </tr>
                @endforelse
            </x-slot>

            <x-slot name="footer">
                @if($orders->hasPages())
                    {{ $orders->links() }}
                @endif
            </x-slot>
        </x-saved-views-table>
    </div>

    {{-- Modal de exportación --}}
    <flux:modal wire:model="showExportModal" name="export-modal" class="min-w-[500px]">
        <div>
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Exportar órdenes de compra</h2>
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
                            label="Todas las órdenes de compra" />
                    
                        <flux:radio 
                            wire:model.live="exportOption" 
                            value="selected" 
                            label="Seleccionados: {{ count($selected) }} orden{{ count($selected) != 1 ? 'es' : '' }}"
                            :disabled="count($selected) === 0"
                        />
                        
                        <flux:radio 
                            wire:model.live="exportOption" 
                            value="search" 
                            label="Órdenes de búsqueda actual"
                            :disabled="empty($search)"
                        />
                        
                        <flux:radio 
                            wire:model.live="exportOption" 
                            value="filtered" 
                            label="Órdenes de la vista actual (con filtros)"
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
                    wire:click="exportOrders"
                    variant="primary"
                >
                    Exportar órdenes
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Modal de crear/editar --}}
    <flux:modal wire:model="showModal" name="order-modal" class="min-w-[600px]">
        <div>
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                    {{ $isEditing ? 'Editar' : 'Crear' }} orden de compra
                </h2>
            </div>
            
            <div class="px-6 py-4 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <flux:input 
                        wire:model="numero_referencia"
                        label="Número de referencia"
                        placeholder="REF-001"
                    />
                    
                    <flux:input 
                        wire:model="numero_guia"
                        label="Número de guía"
                        placeholder="GUIA-001"
                    />
                </div>

                <flux:input 
                    wire:model="fecha_llegada_estimada"
                    type="date"
                    label="Fecha estimada de llegada"
                />

                <flux:textarea 
                    wire:model="nota_al_distribuidor"
                    label="Nota al distribuidor"
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
