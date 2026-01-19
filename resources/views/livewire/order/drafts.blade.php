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
                    <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Borradores</h1>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">Gestiona los pedidos en borrador antes de finalizarlos</p>
                </div>
                <div class="flex items-center gap-3">
                    <flux:button variant="filled" size="sm" wire:click="openExportModal" icon="arrow-up-tray">
                        Exportar
                    </flux:button>
                    <flux:button href="{{ route('orders_create') }}" icon="plus" variant="primary" size="sm">
                        Crear borrador
                    </flux:button>
                </div>
            </div>
        </div>
    </div>

    {{-- Contenido principal --}}
    <div class="px-2">
        {{-- Tabla de borradores --}}
        @php
            $columns = [
                ['key' => 'select', 'label' => '', 'sortable' => false],
                ['key' => 'id', 'label' => 'Borrador', 'sortable' => true],
                ['key' => 'fecha', 'label' => 'Fecha de creación', 'sortable' => true],
                ['key' => 'cliente', 'label' => 'Cliente', 'sortable' => true],
                ['key' => 'mercado', 'label' => 'Mercado', 'sortable' => true],
                ['key' => 'total', 'label' => 'Total', 'sortable' => true],
                ['key' => 'articulos', 'label' => 'Artículos', 'sortable' => true],
                ['key' => 'nota', 'label' => 'Nota', 'sortable' => false],
            ];
        @endphp

        <x-saved-views-table 
            view-name="borradores" 
            search-placeholder="Buscar borradores"
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
                                {{-- Estado del borrador --}}
                                <div class="mb-2">
                                    <flux:separator text="Estado del borrador" />
                                    <flux:menu.item wire:click="addFilter('con_cliente', null, 'Con cliente asignado')">Con cliente asignado</flux:menu.item>
                                    <flux:menu.item wire:click="addFilter('sin_cliente', null, 'Sin cliente asignado')">Sin cliente asignado</flux:menu.item>
                                    <flux:menu.item wire:click="addFilter('con_items', null, 'Con artículos')">Con artículos</flux:menu.item>
                                    <flux:menu.item wire:click="addFilter('sin_items', null, 'Sin artículos')">Sin artículos</flux:menu.item>
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

            {{-- Acciones masivas para borradores --}}
            <x-slot name="bulkActions">
                <flux:button 
                    wire:click="convertToOrders" 
                    icon="check-circle" 
                    size="xs"
                    variant="primary"
                >
                    Convertir en pedidos
                </flux:button>
                
                <flux:button 
                    wire:click="deleteSelected" 
                    wire:confirm="¿Estás seguro de que deseas eliminar los borradores seleccionados?"
                    icon="trash" 
                    size="xs"
                    variant="danger"
                >
                    Eliminar
                </flux:button>
            </x-slot>

            {{-- Contenido de la tabla --}}
            <x-slot name="desktop">
                @forelse($drafts as $draft)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50 {{ in_array($draft->id, $selected) ? 'bg-lime-50 dark:bg-lime-900/20' : '' }}">
                        <td class="px-4 py-3">
                            <flux:checkbox wire:model.live.debounce.150ms="selected" value="{{ $draft->id }}" />
                        </td>
                        <td class="px-4 py-3">
                            <a href="#" class="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400">
                                #{{ str_pad($draft->id, 4, '0', STR_PAD_LEFT) }}
                            </a>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-zinc-900 dark:text-white">
                                {{ $draft->created_at->isToday() ? 'Hoy a las ' . $draft->created_at->format('H:i') : $draft->created_at->format('d M, Y H:i') }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-zinc-900 dark:text-white">
                                @if($draft->customer)
                                    {{ $draft->customer->name }}
                                @else
                                    <span class="text-zinc-500 dark:text-zinc-400 italic">Sin asignar</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-zinc-900 dark:text-white">
                                {{ $draft->market?->name ?? '—' }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-zinc-900 dark:text-white">
                                @if($draft->total_price > 0)
                                    {{ number_format($draft->total_price, 2) }} L
                                @else
                                    <span class="text-zinc-500 dark:text-zinc-400">—</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-zinc-900 dark:text-white">
                                @if($draft->items->count() > 0)
                                    {{ $draft->items->count() }} artículo{{ $draft->items->count() != 1 ? 's' : '' }}
                                @else
                                    <span class="text-zinc-500 dark:text-zinc-400 italic">Sin artículos</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-zinc-700 dark:text-zinc-300 max-w-xs truncate">
                                {{ $draft->note ?? '—' }}
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-12 h-12 text-zinc-400 dark:text-zinc-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-2">No hay borradores</p>
                                <p class="text-xs text-zinc-400 dark:text-zinc-500">Crea un nuevo borrador para empezar</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </x-slot>

            <x-slot name="footer">
                @if($drafts->hasPages())
                    {{ $drafts->links() }}
                @endif
            </x-slot>
        </x-saved-views-table>
    </div>

    {{-- Modal de exportación --}}
    <flux:modal wire:model="showExportModal" name="export-modal" class="min-w-[500px]">
        <div>
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Exportar borradores</h2>
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
                            label="Todos los borradores" />
                    
                        <flux:radio 
                            wire:model.live="exportOption" 
                            value="selected" 
                            label="Seleccionados: {{ count($selected) }} borrador{{ count($selected) != 1 ? 'es' : '' }}"
                            :disabled="count($selected) === 0"
                        />
                        
                        <flux:radio 
                            wire:model.live="exportOption" 
                            value="search" 
                            label="Borradores de búsqueda actual"
                            :disabled="empty($search)"
                        />
                        
                        <flux:radio 
                            wire:model.live="exportOption" 
                            value="filtered" 
                            label="Borradores de la vista actual (con filtros)"
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
                    wire:click="exportDrafts"
                    variant="primary"
                >
                    Exportar borradores
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
