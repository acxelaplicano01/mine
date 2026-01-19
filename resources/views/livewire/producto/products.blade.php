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
                    <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Productos</h1>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">Administra los productos de tu tienda</p>
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
                            <flux:menu.separator />
                            <flux:menu.item wire:click="changeStatus(1)" :disabled="count($selected) === 0">
                                Marcar como activo
                            </flux:menu.item>
                            <flux:menu.item wire:click="changeStatus(0)" :disabled="count($selected) === 0">
                                Marcar como inactivo
                            </flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>
                    <flux:button href="#" icon="plus" variant="primary" size="sm">
                        Agregar producto
                    </flux:button>
                </div>
            </div>
        </div>
    </div>

    {{-- Contenido principal --}}
    <div class="px-2">
        {{-- Tabla de productos --}}
        @php
            $columns = [
                ['key' => 'select', 'label' => '', 'sortable' => false],
                ['key' => 'id', 'label' => 'ID', 'sortable' => true],
                ['key' => 'imagen', 'label' => 'Imagen', 'sortable' => false],
                ['key' => 'nombre', 'label' => 'Producto', 'sortable' => true],
                ['key' => 'estado', 'label' => 'Estado', 'sortable' => true],
                ['key' => 'stock', 'label' => 'Inventario', 'sortable' => false],
                ['key' => 'precio', 'label' => 'Precio', 'sortable' => true],
                ['key' => 'costo', 'label' => 'Costo', 'sortable' => true],
                ['key' => 'beneficio', 'label' => 'Beneficio', 'sortable' => true],
                ['key' => 'margen', 'label' => 'Margen', 'sortable' => true],
                ['key' => 'variantes', 'label' => 'Variantes', 'sortable' => false],
            ];
        @endphp

        <x-saved-views-table 
            view-name="productos" 
            search-placeholder="Buscar todos los productos"
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
                                {{-- Estado del producto --}}
                                <div class="mb-2">
                                    <flux:separator text="Estado del producto" />
                                    <flux:menu.item wire:click="addFilter('activo', null, 'Estado: Activo')">
                                        Activo
                                    </flux:menu.item>
                                    <flux:menu.item wire:click="addFilter('inactivo', null, 'Estado: Inactivo')">
                                        Inactivo
                                    </flux:menu.item>
                                </div>
                                
                                {{-- Inventario --}}
                                <div class="mb-2">
                                    <flux:separator text="Inventario" />
                                    <flux:menu.item wire:click="addFilter('con_stock', null, 'Con stock')">
                                        Con stock
                                    </flux:menu.item>
                                    <flux:menu.item wire:click="addFilter('sin_stock', null, 'Sin stock')">
                                        Sin stock / Agotado
                                    </flux:menu.item>
                                </div>
                                
                                {{-- Impuestos --}}
                                <div class="mb-2">
                                    <flux:separator text="Impuestos" />
                                    <flux:menu.item wire:click="addFilter('cobrar_impuestos_si', null, 'Cobrar impuestos: Sí')">
                                        Cobra impuestos
                                    </flux:menu.item>
                                    <flux:menu.item wire:click="addFilter('cobrar_impuestos_no', null, 'Cobrar impuestos: No')">
                                        No cobra impuestos
                                    </flux:menu.item>
                                </div>
                            </div>
                        </div>
                    </flux:menu>
                </flux:dropdown>
            </x-slot>

            {{-- Acciones masivas para productos --}}
            <x-slot name="bulkActions">
                <flux:dropdown>
                    <flux:button icon:trailing="chevron-down" size="xs">
                        Marcar como
                    </flux:button>
                    <flux:menu class="min-w-40">
                        <flux:menu.item wire:click="changeStatus(1)">
                            Activo
                        </flux:menu.item>
                        <flux:menu.item wire:click="changeStatus(0)">
                            Inactivo
                        </flux:menu.item>
                    </flux:menu>
                </flux:dropdown>
                
                <flux:button wire:click="deleteSelected" size="xs" variant="danger">
                    Eliminar
                </flux:button>
            </x-slot>

            {{-- Contenido de la tabla --}}
            <x-slot name="desktop">
                @forelse($products as $product)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50 {{ in_array($product->id, $selected) ? 'bg-lime-50 dark:bg-lime-900/20' : '' }}">
                        <td class="px-4 py-3">
                            <flux:checkbox wire:model.live.debounce.150ms="selected" value="{{ $product->id }}" />
                        </td>
                        <td class="px-4 py-3">
                            <a href="#" class="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400">
                                #{{ str_pad($product->id, 4, '0', STR_PAD_LEFT) }}
                            </a>
                        </td>
                        <td class="px-4 py-3">
                            <div class="w-10 h-10 rounded bg-zinc-100 dark:bg-zinc-700 flex items-center justify-center overflow-hidden">
                                @if(isset($product->multimedia) && is_array($product->multimedia) && count($product->multimedia) > 0)
                                    <img src="{{ $product->multimedia[0] }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                @else
                                    <svg class="w-5 h-5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-zinc-900 dark:text-white font-medium">
                                {{ $product->name }}
                            </div>
                            @if($product->description)
                                <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 line-clamp-1">
                                    {{ $product->description }}
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($product->id_status_product == 1)
                                <span class="inline-flex items-center gap-1 px-2 py-1 bg-green-100 dark:bg-green-900/30 rounded text-xs text-green-700 dark:text-green-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                    Activo
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-1 bg-zinc-100 dark:bg-zinc-700 rounded text-xs text-zinc-600 dark:text-zinc-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-zinc-400"></span>
                                    Inactivo
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-zinc-900 dark:text-white">
                                @if($product->inventory)
                                    {{ $product->inventory->cantidad_inventario ?? 0 }} en stock
                                @else
                                    <span class="text-zinc-500 dark:text-zinc-400">—</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-zinc-900 dark:text-white">
                                {{ number_format($product->price_unitario ?? 0, 2) }} L
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-zinc-900 dark:text-white">
                                {{ number_format($product->costo ?? 0, 2) }} L
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-zinc-900 dark:text-white">
                                {{ number_format($product->beneficio ?? 0, 2) }} L
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-zinc-900 dark:text-white">
                                {{ number_format($product->margen_beneficio ?? 0, 2) }}%
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-zinc-900 dark:text-white">
                                @if($product->variants && $product->variants->count() > 0)
                                    {{ $product->variants->count() }} variante{{ $product->variants->count() != 1 ? 's' : '' }}
                                @else
                                    <span class="text-zinc-500 dark:text-zinc-400">—</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="px-4 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                            No hay productos registrados
                        </td>
                    </tr>
                @endforelse
            </x-slot>

            <x-slot name="footer">
                @if($products->hasPages())
                    {{ $products->links() }}
                @endif
            </x-slot>
        </x-saved-views-table>
    </div>

    {{-- Modal de exportación --}}
    <flux:modal wire:model="showExportModal" name="export-modal" class="min-w-[500px]">
        <div>
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Exportar productos</h2>
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
                            label="Todos los productos" />
                    
                        <flux:radio 
                            wire:model.live="exportOption" 
                            value="selected" 
                            label="Seleccionados: {{ count($selected) }} producto{{ count($selected) != 1 ? 's' : '' }}"
                            :disabled="count($selected) === 0"
                        />
                        
                        <flux:radio 
                            wire:model.live="exportOption" 
                            value="search" 
                            label="Productos de búsqueda actual"
                            :disabled="empty($search)"
                        />
                        
                        <flux:radio 
                            wire:model.live="exportOption" 
                            value="filtered" 
                            label="Productos de la vista actual (con filtros)"
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
                    wire:click="exportProducts"
                    variant="primary"
                >
                    Exportar productos
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>