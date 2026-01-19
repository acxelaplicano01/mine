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
                    <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Clientes</h1>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">Administra los clientes de tu tienda</p>
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
                            <flux:menu.item wire:click="deleteSelected" :disabled="count($selected) === 0">
                                Eliminar seleccionados
                            </flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>
                    <flux:button href="#" icon="plus" variant="primary" size="sm">
                        Agregar cliente
                    </flux:button>
                </div>
            </div>
        </div>
    </div>

    {{-- Contenido principal --}}
    <div class="px-2">
        {{-- Tabla de clientes --}}
        @php
            $columns = [
                ['key' => 'select', 'label' => '', 'sortable' => false],
                ['key' => 'id', 'label' => 'ID', 'sortable' => true],
                ['key' => 'nombre', 'label' => 'Cliente', 'sortable' => true],
                ['key' => 'email', 'label' => 'Email', 'sortable' => true],
                ['key' => 'phone', 'label' => 'Teléfono', 'sortable' => false],
                ['key' => 'pedidos', 'label' => 'Pedidos', 'sortable' => true],
                ['key' => 'total_gastado', 'label' => 'Total gastado', 'sortable' => true],
                ['key' => 'acepta_email', 'label' => 'Email marketing', 'sortable' => false],
                ['key' => 'acepta_mensajes', 'label' => 'SMS marketing', 'sortable' => false],
            ];
        @endphp

        <x-saved-views-table 
            view-name="clientes" 
            search-placeholder="Buscar todos los clientes"
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
                                {{-- Email marketing --}}
                                <div class="mb-2">
                                    <flux:separator text="Email marketing" />
                                    <flux:menu.item wire:click="addFilter('acepta_email_si', null, 'Acepta email: Sí')">
                                        Acepta email
                                    </flux:menu.item>
                                    <flux:menu.item wire:click="addFilter('acepta_email_no', null, 'Acepta email: No')">
                                        No acepta email
                                    </flux:menu.item>
                                </div>
                                
                                {{-- SMS marketing --}}
                                <div class="mb-2">
                                    <flux:separator text="SMS marketing" />
                                    <flux:menu.item wire:click="addFilter('acepta_mensajes_si', null, 'Acepta SMS: Sí')">
                                        Acepta SMS
                                    </flux:menu.item>
                                    <flux:menu.item wire:click="addFilter('acepta_mensajes_no', null, 'Acepta SMS: No')">
                                        No acepta SMS
                                    </flux:menu.item>
                                </div>
                                
                                {{-- Pedidos --}}
                                <div class="mb-2">
                                    <flux:separator text="Pedidos" />
                                    <flux:menu.item wire:click="addFilter('con_pedidos', null, 'Con pedidos')">
                                        Con pedidos
                                    </flux:menu.item>
                                    <flux:menu.item wire:click="addFilter('sin_pedidos', null, 'Sin pedidos')">
                                        Sin pedidos
                                    </flux:menu.item>
                                </div>
                            </div>
                        </div>
                    </flux:menu>
                </flux:dropdown>
            </x-slot>

            {{-- Acciones masivas para clientes --}}
            <x-slot name="bulkActions">
                <flux:button wire:click="deleteSelected" size="xs" variant="danger">
                    Eliminar
                </flux:button>
            </x-slot>

            {{-- Contenido de la tabla --}}
            <x-slot name="desktop">
                @forelse($customers as $customer)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50 {{ in_array($customer->id, $selected) ? 'bg-lime-50 dark:bg-lime-900/20' : '' }}">
                        <td class="px-4 py-3">
                            <flux:checkbox wire:model.live.debounce.150ms="selected" value="{{ $customer->id }}" />
                        </td>
                        <td class="px-4 py-3">
                            <a href="#" class="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400">
                                #{{ str_pad($customer->id, 4, '0', STR_PAD_LEFT) }}
                            </a>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-zinc-900 dark:text-white font-medium">
                                {{ $customer->name }} {{ $customer->last_name }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-zinc-900 dark:text-white">
                                {{ $customer->email ?? '—' }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-zinc-900 dark:text-white">
                                {{ $customer->phone ?? '—' }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-zinc-900 dark:text-white">
                                {{ $customer->orders_count ?? 0 }} pedido{{ ($customer->orders_count ?? 0) != 1 ? 's' : '' }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-zinc-900 dark:text-white">
                                {{ number_format($customer->total_spent ?? 0, 2) }} L
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                @if($customer->acepta_email)
                                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-green-100 dark:bg-green-900/30 rounded text-xs text-green-700 dark:text-green-400">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Sí
                                    </span>
                                @else
                                    <span class="text-sm text-zinc-500 dark:text-zinc-400">No</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                @if($customer->acepta_mensajes)
                                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-green-100 dark:bg-green-900/30 rounded text-xs text-green-700 dark:text-green-400">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Sí
                                    </span>
                                @else
                                    <span class="text-sm text-zinc-500 dark:text-zinc-400">No</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                            No hay clientes registrados
                        </td>
                    </tr>
                @endforelse
            </x-slot>

            <x-slot name="footer">
                @if($customers->hasPages())
                    {{ $customers->links() }}
                @endif
            </x-slot>
        </x-saved-views-table>
    </div>

    {{-- Modal de exportación --}}
    <flux:modal wire:model="showExportModal" name="export-modal" class="min-w-[500px]">
        <div>
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Exportar clientes</h2>
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
                            label="Todos los clientes" />
                    
                        <flux:radio 
                            wire:model.live="exportOption" 
                            value="selected" 
                            label="Seleccionados: {{ count($selected) }} cliente{{ count($selected) != 1 ? 's' : '' }}"
                            :disabled="count($selected) === 0"
                        />
                        
                        <flux:radio 
                            wire:model.live="exportOption" 
                            value="search" 
                            label="Clientes de búsqueda actual"
                            :disabled="empty($search)"
                        />
                        
                        <flux:radio 
                            wire:model.live="exportOption" 
                            value="filtered" 
                            label="Clientes de la vista actual (con filtros)"
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
                    wire:click="exportCustomers"
                    variant="primary"
                >
                    Exportar clientes
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
