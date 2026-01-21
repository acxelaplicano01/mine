<div class="min-h-screen">
    {{-- Mensaje de éxito --}}
    @if (session()->has('message'))
        <div class="px- sm:px-6 lg:px-8 py-4">
            <flux:callout dismissible variant="success" icon="check-circle" heading="{{ session('message') }}" />
        </div>
    @endif
    
    {{-- Mensaje de error --}}
    @if (session()->has('error'))
        <div class="px-4 sm:px-6 lg:px-8 py-4">
            <flux:callout dismissible variant="danger" icon="exclamation-triangle" heading="{{ session('error') }}" />
        </div>
    @endif

    {{-- Widget de alertas --}}
    <div class="px-2 sm:px-4 lg:px-2 mb-2">
        
        @php
            // Preferir propiedad proviniente del componente Livewire si existe
            $showLowStockWidget = $lowStockCount ?? null;

            if (is_null($showLowStockWidget)) {
                // Fallback: comprobar en BD si hay al menos un producto/variante con stock bajo
                $showLowStockWidget = \App\Models\Product\Products::doesntHave('variants')
                    ->whereHas('inventory', function($q){
                        $q->whereColumn('cantidad_inventario', '<=', 'umbral_aviso_inventario')
                          ->where('umbral_aviso_inventario', '>', 0)
                          ->where('cantidad_inventario', '>', 0);
                    })->exists()
                    || \App\Models\Product\VariantProduct::where('cantidad_inventario', '>', 0)
                        ->whereRaw('cantidad_inventario <= (SELECT umbral_aviso_inventario FROM inventories WHERE inventories.id = (SELECT id_inventory FROM products WHERE products.id = variant_products.product_id))')
                        ->exists();
            }
        @endphp

        @if($showLowStockWidget)
            <livewire:product.low-stock-alerts />
        @endif
        
    </div>
    
    {{-- Header principal --}}
    <div>
        <div class="px-2 sm:px-4 lg:px-2">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Gestión de Inventario</h1>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">Administra el stock de tus productos</p>
                </div>
                <div class="flex items-center gap-3">
                    <flux:button variant="filled" size="sm" wire:click="exportLowStock" icon="arrow-down-tray">
                        Exportar Stock Bajo
                    </flux:button>
                    @if($this->realChangesCount > 0)
                        <flux:button variant="primary" size="sm" wire:click="savePendingChanges" icon="check">
                            Guardar cambios ({{ $this->realChangesCount }})
                        </flux:button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Contenido principal --}}
    <div class="px-2">
        {{-- Tabla de inventario --}}
        @php
            $columns = [
                ['key' => 'select', 'label' => '', 'sortable' => false],
                ['key' => 'producto', 'label' => 'Producto', 'sortable' => true],
                ['key' => 'sku', 'label' => 'SKU', 'sortable' => false],
            ];
            
            if ($showThresholdColumn) {
                $columns[] = ['key' => 'umbral', 'label' => 'Umbral', 'sortable' => false];
            }
            
            if ($showStatusColumn) {
                $columns[] = ['key' => 'estado', 'label' => 'Estado', 'sortable' => false];
            }
            
            $columns = array_merge($columns, [
                ['key' => 'no_disponible', 'label' => 'No disponible', 'sortable' => false],
                ['key' => 'comprometido', 'label' => 'Comprometido', 'sortable' => false],
                ['key' => 'disponible', 'label' => 'Disponible', 'sortable' => false],
                ['key' => 'en_existencia', 'label' => 'En existencia', 'sortable' => true],
                ['key' => 'entrante', 'label' => 'Entrante', 'sortable' => false],
                ['key' => 'acciones', 'label' => '', 'sortable' => false],
            ]);
        @endphp

        <x-saved-views-table 
            view-name="inventarios" 
            search-placeholder="Buscar por nombre, SKU o código de barras..."
            save-button-text="Guardar vista de inventario"
            :columns="$columns"
            :sort-field="$sortField"
            :sort-direction="$sortDirection"
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
                    wire:click="setFilter('low-stock')"
                    class="px-3 py-1.5 text-sm font-medium rounded transition-colors whitespace-nowrap {{ $activeFilter === 'low-stock' ? 'bg-zinc-900 text-white dark:bg-zinc-700' : 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700' }}"
                >
                    Stock bajo
                </button>
                
                {{-- Filtros de seguimiento --}}
                <button 
                    wire:click="setFilter('tracked')"
                    class="px-3 py-1.5 text-sm font-medium rounded transition-colors whitespace-nowrap {{ $activeFilter === 'tracked' ? 'bg-zinc-900 text-white dark:bg-zinc-700' : 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700' }}"
                >
                    Con seguimiento
                </button>
                <button 
                    wire:click="setFilter('untracked')"
                    class="px-3 py-1.5 text-sm font-medium rounded transition-colors whitespace-nowrap {{ $activeFilter === 'untracked' ? 'bg-zinc-900 text-white dark:bg-zinc-700' : 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700' }}"
                >
                    Sin seguimiento
                </button>
                <flux:separator orientation="vertical" />
                {{-- Boton para mostrar la columna de umbral_aviso_inventario --}}
                <flux:dropdown>
                    <flux:button variant="ghost" size="sm" icon="view-columns">
                        Columnas
                    </flux:button>
                    
                    <flux:menu class="w-56">
                        <flux:menu.item>
                            <label class="flex items-center justify-between space-x-1 w-full cursor-pointer">
                                <span>Umbral de inventario</span>
                                <flux:switch wire:model.live="showThresholdColumn" />
                            </label>
                        </flux:menu.item>
                        <flux:menu.item>
                            <label class="flex items-center justify-between space-x-1 w-full cursor-pointer">
                                <span>Estado de inventario</span>
                                <flux:switch wire:model.live="showStatusColumn" />
                            </label>
                        </flux:menu.item>
                    </flux:menu>
                </flux:dropdown>

            </x-slot>

            {{-- Dropdown de filtros --}}
            <x-slot name="filtersDropdown">
                <flux:dropdown>
                    <flux:button icon:trailing="plus" size="xs">
                        Agregar filtro
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
                                {{-- Nivel de stock --}}
                                <div class="mb-2">
                                    <flux:separator text="Nivel de stock" />
                                    <flux:menu.item wire:click="addFilter('nivel_stock_sin_stock', null, 'Nivel de stock: Sin stock')">Sin stock</flux:menu.item>
                                    <flux:menu.item wire:click="addFilter('nivel_stock_stock_bajo', null, 'Nivel de stock: Stock bajo')">Stock bajo</flux:menu.item>
                                    <flux:menu.item wire:click="addFilter('nivel_stock_stock_normal', null, 'Nivel de stock: Stock normal')">Stock normal</flux:menu.item>
                                </div>
                                
                                {{-- Estado de seguimiento --}}
                                <div class="mb-2">
                                    <flux:separator text="Seguimiento de inventario" />
                                    <flux:menu.item wire:click="addFilter('seguimiento_con_seguimiento', null, 'Seguimiento: Con seguimiento')">Con seguimiento</flux:menu.item>
                                    <flux:menu.item wire:click="addFilter('seguimiento_sin_seguimiento', null, 'Seguimiento: Sin seguimiento')">Sin seguimiento</flux:menu.item>
                                </div>
                            </div>
                        </div>
                    </flux:menu>
                </flux:dropdown>
            </x-slot>

            <x-slot name="bulkActions">
                <flux:button wire:click="setUmbral" size="xs">
                    Establecer umbral
                </flux:button>

                <flux:button wire:click="exportSelected" size="xs" icon="plus">
                    Crear tranferencia
                </flux:button>

                <flux:button wire:click="exportSelected" size="xs" icon="plus">
                    Crear orden de compra
                </flux:button>
            </x-slot>

            {{-- Contenido de la tabla --}}
            <x-slot name="desktop">
                @forelse($items as $item)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors {{ in_array($item->id, $selected) ? 'bg-lime-50 dark:bg-lime-900/20' : '' }}">
                        {{-- Checkbox --}}
                        <td class="px-4 py-3">
                            <flux:checkbox wire:model.live.debounce.150ms="selected" value="{{ $item->id }}" />
                        </td>
                        
                        {{-- Producto --}}
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if($item->image)
                                    <img src="{{ $item->image }}" alt="{{ $item->name }}" class="w-10 h-10 rounded object-cover">
                                @else
                                    <div class="w-10 h-10 rounded bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-6 h-6 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                @endif
                                <div>
                                    <div class="text-sm font-medium text-zinc-900 dark:text-white">{{ $item->name }}</div>
                                    @if($item->variant_name)
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $item->variant_name }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- SKU --}}
                        <td class="px-4 py-3">
                            <span class="text-sm font-mono text-zinc-900 dark:text-white">{{ $item->sku }}</span>
                            @if($item->barcode)
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $item->barcode }}</div>
                            @endif
                        </td>

                        {{-- Umbral (columna opcional) --}}
                        @if($showThresholdColumn)
                            <td class="px-4 py-3 text-center">
                                <input 
                                    type="number" 
                                    wire:model.live="pendingChanges.{{ $item->id }}.threshold"
                                    class="w-20 px-2 py-1 text-sm text-center border border-zinc-300 dark:border-zinc-600 rounded bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                                    min="0"
                                />
                            </td>
                        @endif

                        {{-- Estado (columna opcional) --}}
                        @if($showStatusColumn)
                            <td class="px-4 py-3">
                                @php
                                    $status = 'normal';
                                    $statusClass = 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
                                    $statusLabel = 'Stock normal';
                                    
                                    if ($item->stock <= 0) {
                                        $status = 'sin-stock';
                                        $statusClass = 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
                                        $statusLabel = 'Sin stock';
                                    } elseif ($item->threshold > 0 && $item->stock <= $item->threshold) {
                                        $status = 'stock-bajo';
                                        $statusClass = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400';
                                        $statusLabel = 'Stock bajo';
                                    }
                                @endphp
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                        @endif

                        {{-- No disponible --}}
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                @if($item->no_disponible > 0 || count($item->no_disponible_motivos ?? []) > 0)
                                    {{-- Dropdown de motivos con flecha en el número --}}
                                    <flux:dropdown position="bottom" align="center">
                                        <flux:button icon:trailing="chevron-down" variant="subtle" size="sm">{{ $item->no_disponible }}</flux:button>
                                        <flux:menu class="w-75">
                                            <div class="p-3">
                                                <flux:heading size="sm" class="mb-2">Inventario no disponible</flux:heading>
                                                <div class="space-y-1">
                                                    @foreach($item->no_disponible_motivos as $motivo)
                                                        <div class="flex justify-between items-center py-1 text-sm {{ $motivo['cantidad'] > 0 ? '' : 'opacity-50' }}">
                                                            <span class="text-zinc-700 dark:text-zinc-300">{{ $motivo['motivo'] }}</span>
                                                            
                                                            {{-- Botón con modal usando teleport --}}
                                                            <div x-data="{ showModal: false, cantidad: 0 }">
                                                                <flux:button  @click.stop="showModal = true; cantidad = 0"  icon:trailing="chevron-down" variant="subtle" size="sm">{{ $motivo['cantidad'] }}</flux:button>
                                                                {{-- Modal usando teleport para sacarlo del DOM del dropdown --}}
                                                                <template x-teleport="body">
                                                                    <div 
                                                                        x-show="showModal" 
                                                                        x-cloak
                                                                        @click.self="showModal = false"
                                                                        class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50"
                                                                        style="display: none;"
                                                                    >
                                                                        <div @click.stop class="bg-white dark:bg-zinc-800 rounded-lg shadow-xl max-w-md w-full mx-4">
                                                                            <div class="p-4 space-y-3">
                                                                                <div class="flex justify-between items-center">
                                                                                    <flux:heading size="sm">{{ $motivo['motivo'] }}</flux:heading>
                                                                                    <button @click="showModal = false" class="text-zinc-400 hover:text-zinc-600 dark:text-zinc-500 dark:hover:text-zinc-300">
                                                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                                                        </svg>
                                                                                    </button>
                                                                                </div>
                                                                                
                                                                                <flux:field>
                                                                                    <flux:label>Cantidad (Disponible: {{ $motivo['cantidad'] }})</flux:label>
                                                                                    <flux:input 
                                                                                        type="number" 
                                                                                        x-model.number="cantidad"
                                                                                        placeholder="0"
                                                                                        min="0"
                                                                                        max="{{ $motivo['cantidad'] }}"
                                                                                    />
                                                                                </flux:field>
                                                                                
                                                                                <div class="space-y-2">
                                                                                    <flux:button 
                                                                                        size="sm"
                                                                                        variant="filled"
                                                                                        icon="plus"
                                                                                        class="w-full justify-start"
                                                                                        @click="$wire.agregarInventario('{{ $item->id }}', '{{ $motivo['motivo'] }}', cantidad).then(() => { showModal = false })"
                                                                                    >
                                                                                        Agregar inventario
                                                                                    </flux:button>
                                                                                    
                                                                                    <flux:button 
                                                                                        size="sm"
                                                                                        variant="filled"
                                                                                        icon="arrow-right"
                                                                                        class="w-full justify-start"
                                                                                        @click="$wire.moverADisponible('{{ $item->id }}', '{{ $motivo['motivo'] }}', cantidad).then(() => { showModal = false })"
                                                                                    >
                                                                                        Mover a Disponible
                                                                                    </flux:button>
                                                                                    
                                                                                    <flux:button 
                                                                                        size="sm"
                                                                                        variant="danger"
                                                                                        icon="trash"
                                                                                        class="w-full justify-start"
                                                                                        @click="$wire.eliminarInventarioNoDisponible('{{ $item->id }}', '{{ $motivo['motivo'] }}', cantidad).then(() => { showModal = false })"
                                                                                    >
                                                                                        Eliminar inventario
                                                                                    </flux:button>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </template>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </flux:menu>
                                    </flux:dropdown>
                                    
                                    {{-- Dropdown de acciones general (eliminado, ahora está en cada motivo) --}}
                                @else
                                    <span class="text-sm font-medium text-zinc-900 dark:text-white">{{ $item->no_disponible }}</span>
                                @endif
                            </div>
                        </td>

                        {{-- Comprometido --}}
                        <td class="px-4 py-3 text-center">
                            @if($item->comprometido > 0 && count($item->comprometido_detalle ?? []) > 0)
                                <flux:dropdown position="bottom" align="center">
                                    <flux:button icon:trailing="chevron-down" variant="subtle" size="sm">{{ $item->comprometido }}</flux:button>
                                    <flux:menu class="w-48">
                                        <div class="p-3">
                                            <flux:heading size="sm" class="mb-2">Pedidos</flux:heading>
                                            <div class="space-y-1">
                                                @foreach($item->comprometido_detalle as $detalle)
                                                    <div class="flex justify-between items-center py-1">
                                                        <a href="#" class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 font-medium">
                                                            #{{ str_pad($detalle['order_id'], 4, '0', STR_PAD_LEFT) }}
                                                        </a>
                                                        <span class="text-sm font-medium text-zinc-900 dark:text-white">{{ $detalle['quantity'] }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </flux:menu>
                                </flux:dropdown>
                            @else
                                <span class="text-sm font-medium text-zinc-900 dark:text-white">{{ $item->comprometido }}</span>
                            @endif
                        </td>

                        {{-- Disponible --}}
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <input 
                                    type="number" 
                                    wire:model.live="pendingChanges.{{ $item->id }}.disponible"
                                    class="w-20 px-2 py-1 text-sm text-center border border-zinc-300 dark:border-zinc-600 rounded bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                                />
                                <flux:dropdown position="top" align="start">
                                    <flux:button size="xs" variant="ghost" icon="chevron-down" inset="top bottom"></flux:button>
                                    
                                    <flux:menu class="min-w-80">
                                        <div class="p-4 space-y-3" @click.stop x-data="{ accion: $wire.dropdownValues['adjustAction_{{ $item->id }}'] || 'ajustar' }">
                                            <flux:heading size="sm">Ajustar disponible</flux:heading>
                                            
                                            <flux:field>
                                                <flux:label>Acción</flux:label>
                                                <flux:select 
                                                    wire:model="dropdownValues.adjustAction_{{ $item->id }}"
                                                    x-model="accion"
                                                    @change="$wire.set('dropdownValues.adjustReason_{{ $item->id }}', '')"
                                                >
                                                    <option value="ajustar">Ajustar disponible</option>
                                                    <option value="mover_no_disponible">Mover a no disponible</option>
                                                </flux:select>
                                            </flux:field>
                                            
                                            <flux:field>
                                                <flux:label>Ajustar por</flux:label>
                                                <flux:input type="number" wire:model="dropdownValues.adjustAmount_{{ $item->id }}" placeholder="0" />
                                            </flux:field>
                                            
                                            <flux:field>
                                                <flux:label>Motivo</flux:label>
                                                {{-- Select para Ajustar disponible --}}
                                                <div x-show="accion === 'ajustar'">
                                                    <flux:select wire:model="dropdownValues.adjustReason_{{ $item->id }}">
                                                        <option value="">Seleccionar...</option>
                                                        <option value="Corrección">Corrección</option>
                                                        <option value="Recuento">Recuento</option>
                                                        <option value="Recibido">Recibido</option>
                                                        <option value="Reposición por devolución">Reposición por devolución</option>
                                                        <option value="Dañado">Dañado</option>
                                                        <option value="Robo o pérdida">Robo o pérdida</option>
                                                        <option value="Promoción o donación">Promoción o donación</option>
                                                    </flux:select>
                                                </div>
                                                {{-- Select para Mover a no disponible --}}
                                                <div x-show="accion === 'mover_no_disponible'">
                                                    <flux:select wire:model="dropdownValues.adjustReason_{{ $item->id }}">
                                                        <option value="">Seleccionar...</option>
                                                        <option value="Dañado">Dañado</option>
                                                        <option value="Control de calidad">Control de calidad</option>
                                                        <option value="Existencias de seguridad">Existencias de seguridad</option>
                                                        <option value="Otro">Otro</option>
                                                    </flux:select>
                                                </div>
                                            </flux:field>
                                            
                                            <flux:field>
                                                <flux:label>Nota o comentario</flux:label>
                                                <flux:textarea 
                                                    wire:model="dropdownValues.adjustNotes_{{ $item->id }}" 
                                                    rows="2"
                                                    placeholder="Agrega información adicional..."
                                                />
                                            </flux:field>
                                            
                                            <flux:button 
                                                wire:click="applyAdjustment('{{ $item->id }}', 'disponible')"
                                                variant="primary" 
                                                size="sm"
                                                class="w-full"
                                            >
                                                Aplicar
                                            </flux:button>
                                        </div>
                                    </flux:menu>
                                </flux:dropdown>
                            </div>
                        </td>

                        {{-- En existencia --}}
                        <td class="px-4 py-3 text-center">
                            @php
                                $enExistencia = $item->no_disponible + $item->comprometido + $item->stock;
                            @endphp
                            <div class="flex items-center justify-center gap-1">
                                <span class="text-sm font-semibold text-zinc-900 dark:text-white">{{ $enExistencia }}</span>
                                <flux:dropdown position="top" align="center">
                                    <flux:button size="xs" variant="ghost" icon="chevron-down" inset="top bottom"></flux:button>
                                    
                                    <flux:menu class="min-w-80">
                                        <div class="p-4 space-y-3" @click.stop>
                                            <flux:heading size="sm">Ajustar existencia</flux:heading>
                                            
                                            <flux:field>
                                                <flux:label>Ajustar por</flux:label>
                                                <flux:input type="number" wire:model="dropdownValues.adjustAmountExist_{{ $item->id }}" placeholder="0" />
                                            </flux:field>
                                            
                                            <flux:field>
                                                <flux:label>Motivo</flux:label>
                                                <flux:select wire:model="dropdownValues.adjustReasonExist_{{ $item->id }}">
                                                    <option value="">Seleccionar...</option>
                                                    <option value="Corrección">Corrección</option>
                                                    <option value="Recuento">Recuento</option>
                                                    <option value="Recibido">Recibido</option>
                                                    <option value="Reposición por devolución">Reposición por devolución</option>
                                                    <option value="Dañado">Dañado</option>
                                                    <option value="Robo o pérdida">Robo o pérdida</option>
                                                    <option value="Promoción o donación">Promoción o donación</option>
                                                </flux:select>
                                            </flux:field>
                                            
                                            <flux:field>
                                                <flux:label>Nota o comentario</flux:label>
                                                <flux:textarea 
                                                    wire:model="dropdownValues.adjustNotesExist_{{ $item->id }}" 
                                                    rows="2"
                                                    placeholder="Agrega información adicional..."
                                                />
                                            </flux:field>
                                            
                                            <flux:button 
                                                wire:click="applyAdjustment('{{ $item->id }}', 'existencia')"
                                                variant="primary" 
                                                size="sm"
                                                class="w-full"
                                            >
                                                Aplicar
                                            </flux:button>
                                        </div>
                                    </flux:menu>
                                </flux:dropdown>
                            </div>
                        </td>

                        {{-- Entrante --}}
                        <td class="px-4 py-3 text-center">
                            <span class="text-sm font-medium text-zinc-900 dark:text-white">0</span>
                        </td>

                        {{-- Acciones --}}
                        <td class="px-4 py-3">
                           <flux:button 
                                wire:click="openHistoryModal('{{ $item->id }}')" 
                                variant="filled"
                                size="sm"
                            >
                                Historial
                            </flux:button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 9 + ($showThresholdColumn ? 1 : 0) + ($showStatusColumn ? 1 : 0) }}" class="px-4 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                            No se encontraron productos
                        </td>
                    </tr>
                @endforelse
            </x-slot>

            <x-slot name="footer">
                @if($items->hasPages())
                    {{ $items->links() }}
                @endif
            </x-slot>
        </x-saved-views-table>
    </div>

    {{-- Modal de ajuste de inventario --}}
    @if($showAdjustModal && $selectedProduct)
        <flux:modal name="adjust-inventory" wire:model="showAdjustModal">
            <form wire:submit="saveAdjustment">
                <div class="space-y-6">
                    <div>
                        <flux:heading size="lg">Ajustar Inventario</flux:heading>
                        <flux:subheading>
                            {{ $selectedProduct->name }}
                            @if($selectedProduct->variant_name)
                                <span class="text-zinc-500"> • {{ $selectedProduct->variant_name }}</span>
                            @endif
                        </flux:subheading>
                    </div>

                    <div>
                        <flux:field>
                            <flux:label>Stock actual</flux:label>
                            <div class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ $selectedProduct->stock ?? 0 }} unidades
                            </div>
                        </flux:field>
                    </div>

                    <flux:separator />

                    <flux:field>
                        <flux:label>Nueva cantidad</flux:label>
                        <flux:input 
                            wire:model="adjustQuantity" 
                            type="number" 
                            min="0"
                            required
                        />
                        <flux:error name="adjustQuantity" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Razón del ajuste *</flux:label>
                        <flux:select wire:model="adjustReason" required>
                            <option value="">Seleccionar razón...</option>
                            <option value="Recuento físico">Recuento físico</option>
                            <option value="Producto dañado">Producto dañado</option>
                            <option value="Producto perdido">Producto perdido</option>
                            <option value="Corrección de error">Corrección de error</option>
                            <option value="Reabastecimiento">Reabastecimiento</option>
                            <option value="Devolución de proveedor">Devolución de proveedor</option>
                            <option value="Otro">Otro</option>
                        </flux:select>
                        <flux:error name="adjustReason" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Notas adicionales</flux:label>
                        <flux:textarea 
                            wire:model="adjustNotes" 
                            rows="3"
                            placeholder="Agrega información adicional sobre este ajuste..."
                        />
                        <flux:error name="adjustNotes" />
                    </flux:field>

                    <div class="flex gap-2 justify-end">
                        <flux:button type="button" variant="ghost" wire:click="closeAdjustModal">
                            Cancelar
                        </flux:button>
                        <flux:button type="submit" variant="primary">
                            Guardar ajuste
                        </flux:button>
                    </div>
                </div>
            </form>
        </flux:modal>
    @endif

    {{-- Modal de historial --}}
    @if($showHistoryModal && $selectedProduct)
        <flux:modal name="history" wire:model="showHistoryModal" class="max-w-4xl">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Historial de Movimientos</flux:heading>
                    <flux:subheading>
                        {{ $selectedProduct->name }}
                        @if($selectedProduct->variant_name)
                            <span class="text-zinc-500"> • {{ $selectedProduct->variant_name }}</span>
                        @endif
                    </flux:subheading>
                </div>

                <div class="max-h-96 overflow-y-auto border border-zinc-200 dark:border-zinc-700 rounded-lg">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-900">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-700 dark:text-zinc-300">Fecha</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-700 dark:text-zinc-300">Tipo</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-700 dark:text-zinc-300">Cantidad</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-700 dark:text-zinc-300">Stock Anterior</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-700 dark:text-zinc-300">Stock Nuevo</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-700 dark:text-zinc-300">Razón</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-700 dark:text-zinc-300">Usuario</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                            @forelse($movements as $movement)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50">
                                    <td class="px-4 py-3">
                                        <div class="text-xs text-zinc-900 dark:text-white">
                                            {{ $movement->created_at->format('d/m/Y H:i') }}
                                        </div>
                                    </td>
                                    
                                    <td class="px-4 py-3">
                                        @php
                                            $typeColors = [
                                                'entrada' => 'green',
                                                'salida' => 'red',
                                                'ajuste' => 'blue',
                                                'devolucion' => 'yellow',
                                            ];
                                            $typeLabels = [
                                                'entrada' => 'Entrada',
                                                'salida' => 'Salida',
                                                'ajuste' => 'Ajuste',
                                                'devolucion' => 'Devolución',
                                            ];
                                        @endphp
                                        <flux:badge 
                                            :color="$typeColors[$movement->type] ?? 'gray'" 
                                            size="sm"
                                        >
                                            {{ $typeLabels[$movement->type] ?? $movement->type }}
                                        </flux:badge>
                                    </td>
                                    
                                    <td class="px-4 py-3">
                                        <span class="font-semibold text-zinc-900 dark:text-white">
                                            {{ $movement->type === 'entrada' ? '+' : '-' }}{{ $movement->quantity }}
                                        </span>
                                    </td>
                                    
                                    <td class="px-4 py-3 text-sm text-zinc-900 dark:text-white">{{ $movement->cantidad_anterior }}</td>
                                    <td class="px-4 py-3 text-sm text-zinc-900 dark:text-white">{{ $movement->cantidad_nueva }}</td>
                                    
                                    <td class="px-4 py-3">
                                        <div class="text-sm text-zinc-900 dark:text-white">{{ $movement->reason }}</div>
                                        @if($movement->notes)
                                            <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">{{ $movement->notes }}</div>
                                        @endif
                                    </td>
                                    
                                    <td class="px-4 py-3">
                                        <div class="text-xs text-zinc-600 dark:text-zinc-400">
                                            {{ $movement->user?->name ?? 'Sistema' }}
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                        Sin movimientos registrados
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-end">
                    <flux:button type="button" variant="ghost" wire:click="closeHistoryModal">
                        Cerrar
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    @endif

    {{-- Modal de umbral masivo --}}
    <flux:modal name="threshold-modal" wire:model="showThresholdModal" class="max-w-md">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Establecer umbral de inventario</flux:heading>
                <flux:subheading>
                    Se aplicará a {{ count($selected) }} producto(s) seleccionado(s)
                </flux:subheading>
            </div>

            <flux:field>
                <flux:label>Umbral de stock bajo</flux:label>
                <flux:input 
                    wire:model="bulkThreshold" 
                    type="number" 
                    min="0"
                    placeholder="Ej: 10"
                />
                <flux:description>
                    Se notificará cuando el stock sea igual o menor a este valor
                </flux:description>
                <flux:error name="bulkThreshold" />
            </flux:field>

            <div class="flex gap-2 justify-end">
                <flux:button type="button" variant="ghost" wire:click="closeThresholdModal">
                    Cancelar
                </flux:button>
                <flux:button type="button" variant="primary" wire:click="saveThresholdForSelected">
                    Establecer umbral
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
