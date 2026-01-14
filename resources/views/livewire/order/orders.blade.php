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
                            <flux:menu.item>Archivar</flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>
                    <flux:button href="{{ route('orders_create') }}" variant="primary" size="sm">
                        Crear pedido
                    </flux:button>
                </div>
            </div>
        </div>
    </div>

    {{-- Contenido principal --}}
    <div class="px-2 ">
        {{-- Tabla de pedidos --}}
            @php
                $columns = [
                    ['key' => 'select', 'label' => '', 'sortable' => false],
                    ['key' => 'id', 'label' => 'Pedido', 'sortable' => true],
                    ['key' => 'fecha', 'label' => 'Fecha', 'sortable' => true],
                    ['key' => 'cliente', 'label' => 'Cliente', 'sortable' => true],
                    ['key' => 'canal', 'label' => 'Canal', 'sortable' => true],
                    ['key' => 'total', 'label' => 'Total', 'sortable' => true],
                    ['key' => 'estado_pago', 'label' => 'Estado del pago', 'sortable' => true],
                    ['key' => 'estado_preparacion', 'label' => 'Estado de preparación', 'sortable' => true],
                    ['key' => 'articulos', 'label' => 'Artículos', 'sortable' => true],
                    ['key' => 'estado_entrega', 'label' => 'Estado de la entrega', 'sortable' => true],
                    ['key' => 'forma_entrega', 'label' => 'Forma de entrega', 'sortable' => true],
                    ['key' => 'etiquetas', 'label' => 'Etiquetas', 'sortable' => false],
                ];
            @endphp

        <x-saved-views-table 
            view-name="pedidos" 
            search-placeholder="Buscar todos los pedidos"
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
                                {{-- Estado del pedido --}}
                                <div class="mb-2">
                                    <flux:separator text="Estado del pedido" />
                                    <flux:menu.item wire:click="addFilter('estado_pedido_abierto', null, 'Estado del pedido: Abierto')">Abierto</flux:menu.item>
                                    <flux:menu.item wire:click="addFilter('estado_pedido_archivado', null, 'Estado del pedido: Archivado')">Archivado</flux:menu.item>
                                    <flux:menu.item wire:click="addFilter('estado_pedido_cancelado', null, 'Estado del pedido: Cancelado')">Cancelado</flux:menu.item>
                                </div>
                                
                                {{-- Estado del pago --}}
                                <div class="mb-2">
                                    <flux:separator text="Estado del pago" />
                                    <flux:menu.item wire:click="addFilter('estado_pago_pagado', null, 'Estado del pago: Pagado')">Pagado</flux:menu.item>
                                    <flux:menu.item wire:click="addFilter('estado_pago_pendiente', null, 'Estado del pago: Pendiente')">Pendiente</flux:menu.item>
                                    <flux:menu.item wire:click="addFilter('estado_pago_no_pagado', null, 'Estado del pago: No pagado')">No pagado</flux:menu.item>
                                    <flux:menu.item wire:click="addFilter('estado_pago_reembolsado', null, 'Estado del pago: Reembolsado')">Reembolsado</flux:menu.item>
                                    <flux:menu.item wire:click="addFilter('estado_pago_parcialmente_pagado', null, 'Estado del pago: Parcialmente pagado')">Parcialmente pagado</flux:menu.item>
                                </div>
                                
                                {{-- Estado de preparación --}}
                                <div class="mb-2">
                                    <flux:separator text="Estado de preparación" />
                                    <flux:menu.item wire:click="addFilter('estado_preparacion_no_preparado', null, 'Estado de preparación: No preparado')">No preparado</flux:menu.item>
                                    <flux:menu.item wire:click="addFilter('estado_preparacion_parcialmente_preparado', null, 'Estado de preparación: Parcialmente preparado')">Parcialmente preparado</flux:menu.item>
                                    <flux:menu.item wire:click="addFilter('estado_preparacion_preparado', null, 'Estado de preparación: Preparado')">Preparado</flux:menu.item>
                                </div>
                                
                                {{-- Estado de entrega --}}
                                <div class="mb-2">
                                    <flux:separator text="Estado de la entrega" />
                                    <flux:menu.item wire:click="addFilter('estado_entrega_pendiente', null, 'Estado de la entrega: Pendiente')">Pendiente</flux:menu.item>
                                    <flux:menu.item wire:click="addFilter('estado_entrega_en_transito', null, 'Estado de la entrega: En tránsito')">En tránsito</flux:menu.item>
                                    <flux:menu.item wire:click="addFilter('estado_entrega_entregado', null, 'Estado de la entrega: Entregado')">Entregado</flux:menu.item>
                                </div>
                                
                                {{-- Clientes --}}
                                <div class="mb-2">
                                    <flux:separator text="Cliente" />
                                    @foreach($customers->take(10) as $customer)
                                        <flux:menu.item wire:click="addFilter('cliente', {{ $customer->id }}, 'Cliente: {{ $customer->name }}')">
                                            {{ $customer->name }}
                                        </flux:menu.item>
                                    @endforeach
                                </div>
                                
                                {{-- Productos --}}
                                <div class="mb-2">
                                    <flux:separator text="Producto" />
                                    @foreach($products->take(10) as $product)
                                        <flux:menu.item wire:click="addFilter('producto', {{ $product->id }}, 'Producto: {{ $product->name }}')">{{ $product->name }}</flux:menu.item>
                                    @endforeach
                                </div>
                            </div>
                        </div>
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
                                    {{ $order->customer->name ?? 'Sin cliente' }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm text-zinc-900 dark:text-white">
                                    {{ $order->market?->name ?? '—' }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm text-zinc-900 dark:text-white">
                                    {{ number_format($order->total_price, 2) }} L
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full 
                                        @if($order->statusOrder?->id === 1) bg-green-500
                                        @elseif($order->statusOrder?->id === 2) bg-yellow-500
                                        @elseif($order->statusOrder?->id === 3) bg-red-500
                                        @else bg-zinc-500
                                        @endif">
                                    </span>
                                    <span class="text-sm text-zinc-900 dark:text-white">
                                        {{ $order->statusOrder?->name ?? 'Sin estado' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @if($order->statusPreparedOrder)
                                    <div class="inline-flex items-center gap-2 px-2 py-1 
                                        @if($order->statusPreparedOrder->id === 1) bg-green-100 dark:bg-green-900/30
                                        @elseif($order->statusPreparedOrder->id === 2) bg-yellow-100 dark:bg-yellow-900/30
                                        @elseif($order->statusPreparedOrder->id === 3) bg-blue-100 dark:bg-blue-900/30
                                        @else bg-zinc-100 dark:bg-zinc-900/30
                                        @endif rounded">
                                        <span class="w-2 h-2 rounded-full 
                                            @if($order->statusPreparedOrder->id === 1) bg-green-500
                                            @elseif($order->statusPreparedOrder->id === 2) bg-yellow-500
                                            @elseif($order->statusPreparedOrder->id === 3) bg-blue-500
                                            @else bg-zinc-500
                                            @endif">
                                        </span>
                                        <span class="text-sm text-zinc-900 dark:text-white">
                                            {{ $order->statusPreparedOrder->name }}
                                        </span>
                                    </div>
                                @else
                                    <span class="text-sm text-zinc-500 dark:text-zinc-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm text-zinc-900 dark:text-white">
                                    {{ $order->items->count() }} artículo{{ $order->items->count() != 1 ? 's' : '' }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $order->statusOrder?->id === 1 ? 'Entregado' : ($order->statusOrder?->id === 2 ? 'Pendiente' : '—') }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm text-zinc-900 dark:text-white">
                                    {{ $order->envio ? 'Envío' : 'Recogida' }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm text-zinc-500 dark:text-zinc-400">—</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="px-4 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                No hay pedidos registrados
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
        <flux:modal wire:model="showExportModal" name="export-modal" class="min-w-[500px]">
            <div>
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Exportar pedidos</h2>
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
                                label="Todos los pedidos" />
                        
                            <flux:radio 
                                wire:model.live="exportOption" 
                                value="selected" 
                                label="Seleccionados: {{ count($selected) }} pedido{{ count($selected) != 1 ? 's' : '' }}"
                                :disabled="count($selected) === 0"
                            />
                            
                            <flux:radio 
                                wire:model.live="exportOption" 
                                value="search" 
                                label="Pedidos de búsqueda actual"
                                :disabled="empty($search)"
                            />
                            
                            <flux:radio 
                                wire:model.live="exportOption" 
                                value="filtered" 
                                label="Pedidos de la vista actual (con filtros)"
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
                        Exportar pedidos
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    </div> 
</div>
                          