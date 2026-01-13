<div class="min-h-screen">
    {{-- Header principal --}}
    <div>
        <div class="px-2 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-4">
                <div>
                   
                </div>
                <div class="flex items-center gap-3">
                    <flux:button variant="filled" size="sm">
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

    {{-- Mensaje de éxito --}}
    @if (session()->has('message'))
        <div class="px-4 sm:px-6 lg:px-8 pt-4">
            <flux:callout dismissible variant="success" icon="check-circle" heading="{{ session('message') }}" />
        </div>
    @endif

    {{-- Contenido principal --}}
    <div class="px-2 sm:px-6 lg:px-8">
        {{-- Tabla de pedidos --}}
        <x-saved-views-table 
            view-name="pedidos" 
            search-placeholder="Buscar todos los pedidos"
            save-button-text="Guardar vista de tabla"
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
        </x-saved-views-table>

            <table class="w-full bg-white dark:bg-white/5">
                <thead class="bg-zinc-50 dark:bg-zinc-900 border-x border-zinc-200 dark:border-zinc-700">
                    <tr>
                        <th class="w-12 px-4 py-3">
                            <input type="checkbox" class="rounded border-zinc-300 dark:border-zinc-600">
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-700 dark:text-zinc-300">
                            Pedido
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-700 dark:text-zinc-300">
                            <button class="flex items-center gap-1 hover:text-zinc-900 dark:hover:text-white">
                                Fecha
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-700 dark:text-zinc-300">Cliente</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-700 dark:text-zinc-300">Canal</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-700 dark:text-zinc-300">Total</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-700 dark:text-zinc-300">Estado del pago</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-700 dark:text-zinc-300">Estado de preparación del pedido</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-700 dark:text-zinc-300">Artículos</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-700 dark:text-zinc-300">Estado de la entrega</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-700 dark:text-zinc-300">Forma de entrega</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-700 dark:text-zinc-300">Etiquetas</th>
                    </tr>
                </thead>
                <tbody class="border-x border-zinc-200 dark:border-zinc-700 divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse($orders as $order)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                            {{-- Checkbox --}}
                            <td class="px-4 py-3">
                                <input type="checkbox" class="rounded border-zinc-300 dark:border-zinc-600">
                            </td>
                            
                            {{-- Pedido ID --}}
                            <td class="px-4 py-3">
                                <a href="#" class="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400">
                                    #{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }}
                                </a>
                            </td>

                            {{-- Fecha --}}
                            <td class="px-4 py-3">
                                <div class="text-sm text-zinc-900 dark:text-white">
                                    {{ $order->created_at->isToday() ? 'Hoy a las ' . $order->created_at->format('H:i') : $order->created_at->format('d M, Y H:i') }}
                                </div>
                            </td>

                            {{-- Cliente --}}
                            <td class="px-4 py-3">
                                <div class="text-sm text-zinc-900 dark:text-white">
                                    {{ $order->customer->name ?? 'Sin cliente' }}
                                </div>
                            </td>

                            {{-- Canal --}}
                            <td class="px-4 py-3">
                                <div class="text-sm text-zinc-500 dark:text-zinc-400">—</div>
                            </td>

                            {{-- Total --}}
                            <td class="px-4 py-3">
                                <div class="text-sm text-zinc-900 dark:text-white">
                                    {{ number_format($order->total_price, 2) }} L
                                </div>
                            </td>

                            {{-- Estado del pago --}}
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

                            {{-- Estado de preparación --}}
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

                            {{-- Artículos --}}
                            <td class="px-4 py-3">
                                <div class="text-sm text-zinc-900 dark:text-white">
                                    {{ $order->items->count() }} artículo{{ $order->items->count() != 1 ? 's' : '' }}
                                </div>
                            </td>

                            {{-- Estado de la entrega --}}
                            <td class="px-4 py-3">
                                <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $order->statusOrder?->id === 1 ? 'Entregado' : ($order->statusOrder?->id === 2 ? 'Pendiente' : '—') }}
                                </div>
                            </td>

                            {{-- Forma de entrega --}}
                            <td class="px-4 py-3">
                                <div class="text-sm text-zinc-900 dark:text-white">
                                    {{ $order->envio ? 'Envío' : 'Recogida' }}
                                </div>
                            </td>

                            {{-- Etiquetas --}}
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
                </tbody>
            </table>

            {{-- Paginación --}}
            @if($orders->hasPages())
                <div class="bg-white dark:bg-white/5 rounded-b-lg px-4 py-3 border border-zinc-200 dark:border-zinc-700">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
                          