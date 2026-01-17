<div class="min-h-screen">
    <div class=" px-2">
        {{-- Mensaje de éxito --}}
        @if (session()->has('message'))
            <div class="px-4 sm:px-6 lg:px-8 py-4">
                <flux:callout dismissible variant="success" icon="check-circle" heading="{{ session('message') }}" />
            </div>
        @endif
        {{-- Header --}}
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Descuentos</h1>
                <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">Administra los descuentos de tu tienda</p>
            </div>
            <div class="flex items-center gap-3">
                    <flux:button variant="filled" size="sm" wire:click="openExportModal" icon="arrow-up-tray">
                        Exportar
                    </flux:button>
                   {{--<flux:dropdown>
                        <flux:button variant="filled" size="sm" icon-trailing="chevron-down">
                            Más acciones
                        </flux:button>
                        <flux:menu>
                            <flux:menu.item>Exportar seleccionados</flux:menu.item>
                            <flux:menu.item>Imprimir etiquetas</flux:menu.item>
                            <flux:menu.item>Archivar</flux:menu.item>
                        </flux:menu>
                    </flux:dropdown> --}} 
                    <flux:button href="{{ route('discounts_create') }}" variant="primary" size="sm" icon="plus">
                        Crear descuento
                    </flux:button>
                </div>
        </div>
        {{-- Tabla de descuentos --}}
         @php
                $columns = [
                    ['key' => 'select', 'label' => '', 'sortable' => false],
                    ['key' => 'code_discount', 'label' => 'Código y descripción', 'sortable' => true],
                    ['key' => 'id_type_discount', 'label' => 'Tipo', 'sortable' => true],
                    ['key' => 'id_method_discount', 'label' => 'Método', 'sortable' => true],
                    ['key' => 'valor_discount', 'label' => 'Valor', 'sortable' => true],
                    ['key' => 'id_status_discount', 'label' => 'Estado', 'sortable' => true],
                    ['key' => 'used_count', 'label' => 'Usos', 'sortable' => true],
                ];
        @endphp
        <x-saved-views-table 
            view-name="descuentos" 
            search-placeholder="Buscar descuentos"
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
                    wire:click="$set('filterStatus', 'all')"
                    class="px-3 py-1.5 text-sm font-medium rounded transition-colors whitespace-nowrap {{ $filterStatus === 'all' ? 'bg-zinc-900 text-white dark:bg-zinc-700' : 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700' }}"
                >
                    Todos
                </button>
                <button 
                    wire:click="$set('filterStatus', '1')"
                    class="px-3 py-1.5 text-sm font-medium rounded transition-colors whitespace-nowrap {{ $filterStatus === '1' ? 'bg-zinc-900 text-white dark:bg-zinc-700' : 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700' }}"
                >
                    Activos
                </button>
                <button 
                    wire:click="$set('filterStatus', '4')"
                    class="px-3 py-1.5 text-sm font-medium rounded transition-colors whitespace-nowrap {{ $filterStatus === '4' ? 'bg-zinc-900 text-white dark:bg-zinc-700' : 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700' }}"
                >
                    Inactivos
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
                            {{-- Tipo de descuento --}}
                            <div class="mb-2">
                                <flux:separator text="Tipo de descuento" />
                                <flux:menu.item wire:click="addFilter('tipo_producto', null, 'Tipo: Descuento en productos')">Descuento en productos</flux:menu.item>
                                <flux:menu.item wire:click="addFilter('tipo_buy_x_get_y', null, 'Tipo: Buy X Get Y')">Buy X Get Y</flux:menu.item>
                                <flux:menu.item wire:click="addFilter('tipo_pedido', null, 'Tipo: Descuento en el pedido')">Descuento en el pedido</flux:menu.item>
                                <flux:menu.item wire:click="addFilter('tipo_envio', null, 'Tipo: Envío gratis')">Envío gratis</flux:menu.item>
                            </div>
                            
                            {{-- Método --}}
                            <div class="mb-2">
                                <flux:separator text="Método" />
                                <flux:menu.item wire:click="addFilter('metodo_codigo', null, 'Método: Código de descuento')">Código de descuento</flux:menu.item>
                                <flux:menu.item wire:click="addFilter('metodo_automatico', null, 'Método: Automático')">Automático</flux:menu.item>
                            </div>
                        </div>
                    </flux:menu>
                </flux:dropdown>
            </x-slot>

            {{-- Acciones masivas para descuentos --}}
            <x-slot name="bulkActions">
                <flux:button wire:click="activateSelected" size="xs" class="bg-green-600 hover:bg-green-700 text-white">
                    Activar
                </flux:button>
                
                <flux:button wire:click="deactivateSelected" size="xs" variant="filled" class="bg-zinc-600 hover:bg-zinc-700 text-white">
                    Desactivar
                </flux:button>
                
                <flux:button wire:click="deleteSelected" wire:confirm="¿Estás seguro de eliminar los descuentos seleccionados?" size="xs" variant="danger">
                    Eliminar
                </flux:button>
            </x-slot>

            {{-- Contenido de la tabla --}}
            <x-slot name="desktop">
                @forelse($discounts as $discount)
                    <tr 
                        class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50 {{ in_array($discount->id, $selected) ? 'bg-lime-50 dark:bg-lime-900/20' : '' }}"
                    >
                        <td class="px-4 py-3">
                            <flux:checkbox wire:model.live="selected" value="{{ $discount->id }}" />
                        </td>
                        
                        <td class="px-3 py-4">
                            <div>
                                @if($discount->code_discount)
                                    <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                        {{ $discount->code_discount }}
                                    </div>
                                @endif
                                <div class="text-sm text-zinc-600 dark:text-zinc-400">
                                    {{ Str::limit($discount->description, 50) }}
                                </div>
                            </div>
                        </td>
                        
                        <td class="px-3 py-4 text-sm text-zinc-600 dark:text-zinc-400">
                            @switch($discount->id_type_discount)
                                @case(1) Producto @break
                                @case(2) Buy X Get Y @break
                                @case(3) Pedido @break
                                @case(4) Envío gratis @break
                            @endswitch
                        </td>
                        
                        <td class="px-3 py-4">
                            @if($discount->id_method_discount == 1)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    Código
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    Automático
                                </span>
                            @endif
                        </td>
                        
                        <td class="px-3 py-4 text-sm font-medium text-zinc-900 dark:text-white">
                            {{ $discount->valor_discount }}{{ $discount->discount_value_type === 'percentage' ? '%' : ' L' }}
                        </td>
                        
                        <td class="px-3 py-4">
                            <button 
                                wire:click="toggleStatus({{ $discount->id }})"
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $discount->id_status_discount == 1 ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-zinc-100 text-zinc-800 dark:bg-zinc-900 dark:text-zinc-200' }}"
                            >
                                {{ $discount->id_status_discount == 1 ? 'Activo' : 'Inactivo' }}
                            </button>
                        </td>
                        
                        <td class="px-3 py-4 text-sm text-zinc-600 dark:text-zinc-400">
                            {{ $discount->used_count ?? 0 }} / {{ $discount->usage_limit ? $discount->usage_limit : '∞' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-zinc-500 dark:text-zinc-400">
                            No se encontraron descuentos
                        </td>
                    </tr>
                @endforelse
            </x-slot>

            {{-- Paginación --}}
            <x-slot name="footer">
                {{ $discounts->links() }}
            </x-slot>
        </x-saved-views-table>
    </div>
     <flux:modal wire:model="showExportModal" name="export-modal" class="min-w-[500px]">
            <div>
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Exportar descuentos</h2>
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
                                label="Todos los descuentos" />
                        
                            <flux:radio 
                                wire:model.live="exportOption" 
                                value="selected" 
                                label="Seleccionados: {{ count($selected) }} descuento{{ count($selected) != 1 ? 's' : '' }}"
                                :disabled="count($selected) === 0"
                            />
                            
                            <flux:radio 
                                wire:model.live="exportOption" 
                                value="search" 
                                label="Descuentos de búsqueda actual"
                                :disabled="empty($search)"
                            />
                            
                            <flux:radio 
                                wire:model.live="exportOption" 
                                value="filtered" 
                                label="Descuentos de la vista actual (con filtros)"
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
                        wire:click="exportDiscounts"
                        variant="primary"
                    >
                        Exportar descuentos
                    </flux:button>
                </div>
            </div>
        </flux:modal>
</div>