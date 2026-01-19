<div class="min-h-screen">
    {{-- Mensaje de éxito --}}
    @if (session()->has('message'))
        <div class="px-4 sm:px-6 lg:px-8 py-4">
            <flux:callout dismissible variant="success" icon="check-circle" heading="{{ session('message') }}" />
        </div>
    @endif

    {{-- Mensaje de error --}}
    @if (session()->has('error'))
        <div class="px-4 sm:px-6 lg:px-8 py-4">
            <flux:callout dismissible variant="danger" icon="exclamation-circle" heading="{{ session('error') }}" />
        </div>
    @endif

    {{-- Header principal --}}
    <div>
        <div class="px-2 sm:px-4 lg:px-2">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Carritos abandonados</h1>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">
                        Clientes que agregaron productos al carrito pero no completaron la compra
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <flux:button variant="filled" size="sm" wire:click="openExportModal" icon="arrow-up-tray">
                        Exportar
                    </flux:button>
                </div>
            </div>
        </div>
    </div>

    {{-- Contenido principal --}}
    <div class="px-2">
        {{-- Tabla de carritos abandonados --}}
        @php
            $columns = [
                ['key' => 'select', 'label' => '', 'sortable' => false],
                ['key' => 'id', 'label' => 'ID', 'sortable' => true],
                ['key' => 'fecha', 'label' => 'Fecha', 'sortable' => true],
                ['key' => 'cliente', 'label' => 'Cliente/Usuario', 'sortable' => true],
                ['key' => 'email', 'label' => 'Email', 'sortable' => false],
                ['key' => 'producto', 'label' => 'Producto', 'sortable' => true],
                ['key' => 'cantidad', 'label' => 'Cantidad', 'sortable' => false],
                ['key' => 'total', 'label' => 'Total', 'sortable' => true],
                ['key' => 'mercado', 'label' => 'Mercado', 'sortable' => true],
                ['key' => 'email_enviado', 'label' => 'Email enviado', 'sortable' => false],
            ];
        @endphp

        <x-saved-views-table 
            view-name="carritos abandonados" 
            search-placeholder="Buscar carritos abandonados"
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
                                {{-- Estado del email --}}
                                <div class="mb-2">
                                    <flux:separator text="Estado del correo" />
                                    <flux:menu.item wire:click="addFilter('email_enviado', null, 'Email enviado')">Email enviado</flux:menu.item>
                                    <flux:menu.item wire:click="addFilter('email_no_enviado', null, 'Email no enviado')">Email no enviado</flux:menu.item>
                                </div>

                                {{-- Estado del usuario --}}
                                <div class="mb-2">
                                    <flux:separator text="Estado del usuario" />
                                    <flux:menu.item wire:click="addFilter('con_usuario', null, 'Con usuario asignado')">Con usuario asignado</flux:menu.item>
                                    <flux:menu.item wire:click="addFilter('sin_usuario', null, 'Sin usuario asignado')">Sin usuario asignado</flux:menu.item>
                                </div>

                                {{-- Estado del cliente --}}
                                <div class="mb-2">
                                    <flux:separator text="Estado del cliente" />
                                    <flux:menu.item wire:click="addFilter('con_cliente', null, 'Con cliente asignado')">Con cliente asignado</flux:menu.item>
                                    <flux:menu.item wire:click="addFilter('sin_cliente', null, 'Sin cliente asignado')">Sin cliente asignado</flux:menu.item>
                                </div>
                                
                                {{-- Usuarios --}}
                                <div class="mb-2">
                                    <flux:separator text="Usuario" />
                                    @foreach($users->take(10) as $user)
                                        <flux:menu.item wire:click="addFilter('usuario', {{ $user->id }}, 'Usuario: {{ $user->name }}')">
                                            {{ $user->name }}
                                        </flux:menu.item>
                                    @endforeach
                                </div>

                                {{-- Clientes --}}
                                <div class="mb-2">
                                    <flux:separator text="Cliente" />
                                    @foreach($customers->take(10) as $customer)
                                        <flux:menu.item wire:click="addFilter('cliente', {{ $customer->id }}, 'Cliente: {{ $customer->nombre }}')">
                                            {{ $customer->nombre }}
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
                <flux:button 
                    wire:click="sendRecoveryEmails" 
                    icon="envelope" 
                    size="xs"
                    variant="primary"
                >
                    Enviar email de recuperación
                </flux:button>
                
                <flux:button 
                    wire:click="deleteSelected" 
                    wire:confirm="¿Estás seguro de que deseas eliminar los carritos abandonados seleccionados?"
                    icon="trash" 
                    size="xs"
                    variant="danger"
                >
                    Eliminar
                </flux:button>
            </x-slot>

            {{-- Contenido de la tabla --}}
            <x-slot name="desktop">
                @forelse($abandonedOrders as $order)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50 {{ in_array($order->id, $selected) ? 'bg-lime-50 dark:bg-lime-900/20' : '' }}">
                        <td class="px-4 py-3">
                            <flux:checkbox wire:model.live.debounce.150ms="selected" value="{{ $order->id }}" />
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                #{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-zinc-900 dark:text-white">
                                {{ $order->created_at->isToday() ? 'Hoy a las ' . $order->created_at->format('H:i') : $order->created_at->format('d M, Y H:i') }}
                            </div>
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $order->created_at->diffForHumans() }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-zinc-900 dark:text-white">
                                @if($order->customer)
                                    {{ $order->customer->nombre }}
                                    @if($order->customer->email)
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ $order->customer->email }}
                                        </div>
                                    @endif
                                @elseif($order->user)
                                    {{ $order->user->name }}
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                        {{ $order->user->email }}
                                    </div>
                                @else
                                    <span class="text-zinc-500 dark:text-zinc-400 italic">Sin asignar</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-zinc-700 dark:text-zinc-300">
                                {{ $order->user?->email ?? '—' }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-zinc-900 dark:text-white">
                                {{ $order->product?->name ?? 'Sin producto' }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-zinc-900 dark:text-white">
                                {{ $order->quantity }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-zinc-900 dark:text-white">
                                @if($order->total_price > 0)
                                    {{ number_format($order->total_price, 2) }} L
                                @else
                                    <span class="text-zinc-500 dark:text-zinc-400">—</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-zinc-900 dark:text-white">
                                {{ $order->market?->name ?? '—' }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            @if($order->email_sent_at)
                                <div class="inline-flex items-center gap-2 px-2 py-1 bg-green-100 dark:bg-green-900/30 rounded">
                                    <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-xs text-green-700 dark:text-green-300">
                                        {{ $order->email_sent_at->format('d/m/Y') }}
                                    </span>
                                </div>
                            @else
                                <span class="text-sm text-zinc-500 dark:text-zinc-400">No enviado</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-4 py-8 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-12 h-12 text-zinc-400 dark:text-zinc-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-2">No hay carritos abandonados</p>
                                <p class="text-xs text-zinc-400 dark:text-zinc-500">Los carritos abandonados aparecerán aquí cuando los clientes agreguen productos pero no completen la compra</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </x-slot>

            <x-slot name="footer">
                @if($abandonedOrders->hasPages())
                    {{ $abandonedOrders->links() }}
                @endif
            </x-slot>
        </x-saved-views-table>
    </div>

    {{-- Modal de exportación --}}
    <flux:modal wire:model="showExportModal" name="export-modal" class="min-w-[500px]">
        <div>
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Exportar carritos abandonados</h2>
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
                            label="Todos los carritos abandonados" />
                    
                        <flux:radio 
                            wire:model.live="exportOption" 
                            value="selected" 
                            label="Seleccionados: {{ count($selected) }} carrito{{ count($selected) != 1 ? 's' : '' }}"
                            :disabled="count($selected) === 0"
                        />
                        
                        <flux:radio 
                            wire:model.live="exportOption" 
                            value="search" 
                            label="Carritos de búsqueda actual"
                            :disabled="empty($search)"
                        />
                        
                        <flux:radio 
                            wire:model.live="exportOption" 
                            value="filtered" 
                            label="Carritos de la vista actual (con filtros)"
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
                    wire:click="exportAbandonedOrders"
                    variant="primary"
                >
                    Exportar carritos
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
