<div class="min-h-screen">
    <div class="max-w-6xl mx-auto px-4 py-6">
        <form wire:submit.prevent="save">
            {{-- Header --}}
            <div class="mb-6">
                <div class="flex items-center gap-3 mb-2">
                    <button type="button" wire:click="cancel" class="p-1.5 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">Crear orden de compra</h1>
                </div>
            </div>

            @if (session()->has('message'))
                <div class="mb-4 p-4 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200 rounded-lg">
                    {{ session('message') }}
                </div>
            @endif

            @if (session()->has('error'))
                <div class="mb-4 p-4 bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Grid de dos columnas --}}
            <div class="grid grid-cols-1 lg:grid-cols-1 gap-6">
                {{-- Columna principal --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Distribuidor y Destino --}}
                    <div class="bg-white dark:bg-white/5 rounded-lg border border-zinc-200 dark:border-zinc-800 p-6">
                        <div class="grid grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Distribuidor</label>
                                <flux:select wire:model="id_distribuidor" placeholder="Seleccionar distribuidor...">
                                    <flux:select.option value="">Seleccionar distribuidor</flux:select.option>
                                    @foreach($distribuidores as $dist)
                                        <flux:select.option value="{{ $dist->id }}">{{ $dist->name }}</flux:select.option>
                                    @endforeach
                                </flux:select>
                                @error('id_distribuidor')
                                    <span class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Destino</label>
                                <flux:select wire:model="id_sucursal_destino" placeholder="Seleccionar destino...">
                                    <flux:select.option value="">Seleccionar sucursal</flux:select.option>
                                    @foreach($branches as $branch)
                                        <flux:select.option value="{{ $branch->id }}">{{ $branch->name }}</flux:select.option>
                                    @endforeach
                                </flux:select>
                                @if($id_sucursal_destino)
                                    @php
                                        $destino = $branches->firstWhere('id', $id_sucursal_destino);
                                    @endphp
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">{{ $destino->name ?? '' }}, {{ $destino->name ?? '' }}</div>
                                @endif
                                @error('id_sucursal_destino')
                                    <span class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Condiciones de pago (opcional)</label>
                                <flux:select wire:model="id_condiciones_pago" placeholder="Ninguna">
                                    <flux:select.option value="">Ninguna</flux:select.option>
                                    @foreach($condicionesPago as $condicion)
                                        <flux:select.option value="{{ $condicion->id }}">{{ $condicion->nombre_condicion }}</flux:select.option>
                                    @endforeach
                                </flux:select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Moneda del distribuidor</label>
                                <flux:select wire:model="id_moneda_del_distribuidor" placeholder="Seleccionar moneda...">
                                    <flux:select.option value="">Seleccionar moneda</flux:select.option>
                                    @foreach($monedas as $moneda)
                                        <flux:select.option value="{{ $moneda->id }}">{{ $moneda->nombre }} ({{ $moneda->simbolo }})</flux:select.option>
                                    @endforeach
                                </flux:select>
                            </div>
                        </div>
                    </div>

                    {{-- Información del envío --}}
                    <div class="bg-white dark:bg-white/5 rounded-lg border border-zinc-200 dark:border-zinc-800 p-6">
                        <h3 class="text-base font-semibold text-zinc-900 dark:text-white mb-4">Información del envío</h3>
                        
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Llegada estimada</label>
                                <flux:input 
                                    type="date"
                                    wire:model="fecha_llegada_estimada"
                                    placeholder="AAAA-MM-DD"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Empresa de transporte</label>
                                <flux:select wire:model="id_empresa_trasnportista" placeholder="Seleccionar...">
                                    <flux:select.option value="">Seleccionar empresa</flux:select.option>
                                    @foreach($transportistas as $transportista)
                                        <flux:select.option value="{{ $transportista->id }}">{{ $transportista->name }}</flux:select.option>
                                    @endforeach
                                </flux:select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Número de seguimiento</label>
                                <flux:input 
                                    wire:model="numero_guia"
                                    placeholder="Ej: TRACK123456"
                                />
                            </div>
                        </div>
                    </div>

                    {{-- Agregar productos --}}
                    <div class="bg-white dark:bg-white/5 rounded-lg border border-zinc-200 dark:border-zinc-800">
                        <div class="p-6 border-b border-zinc-200 dark:border-zinc-800">
                            <h3 class="text-base font-semibold text-zinc-900 dark:text-white">Agregar productos</h3>
                        </div>

                        <div class="p-6">
                            <div class="flex gap-2 mb-4">
                                <flux:input 
                                    wire:click="openProductModal" 
                                    icon="magnifying-glass"
                                    placeholder="Buscar productos..." 
                                    class="flex-1 cursor-pointer" 
                                    readonly 
                                />
                                <flux:button type="button" wire:click="openProductModal">
                                    Explorar
                                </flux:button>
                                <flux:button type="button" variant="ghost">
                                    Importar
                                </flux:button>
                            </div>

                            @error('selectedProducts')
                                <div class="mb-4 p-3 bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200 text-sm rounded">
                                    {{ $message }}
                                </div>
                            @enderror

                            {{-- Tabla de productos seleccionados --}}
                            @if(count($selectedProducts) > 0)
                                <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden">
                                    <table class="w-full">
                                        <thead class="bg-zinc-50 dark:bg-white/5">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Productos</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">SKU del distribuidor</th>
                                                <th class="px-4 py-3 text-center text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Cantidad</th>
                                                <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Costo</th>
                                                <th class="px-4 py-3 text-center text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Impuesto</th>
                                                <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Total</th>
                                                <th class="w-12"></th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                                            @foreach($selectedProducts as $index => $item)
                                                <tr>
                                                    <td class="px-4 py-4">
                                                        <div class="flex items-center gap-3">
                                                            <div class="w-10 h-10 bg-zinc-100 dark:bg-zinc-700 rounded flex items-center justify-center">
                                                                <svg class="w-6 h-6 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                                                </svg>
                                                            </div>
                                                            <div>
                                                                <div class="text-sm font-medium text-zinc-900 dark:text-white">{{ $item['name'] }}</div>
                                                                @if($item['variant_id'])
                                                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ explode(' - ', $item['name'])[1] ?? '' }}</div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-4">
                                                        <flux:input 
                                                            wire:model.live.debounce.500ms="selectedProducts.{{ $index }}.sku_distribuidor"
                                                            wire:change="updateSkuDistribuidor({{ $index }}, $event.target.value)"
                                                            placeholder="SKU..."
                                                            class="w-full"
                                                        />
                                                    </td>
                                                    <td class="px-4 py-4">
                                                        <div class="flex justify-center">
                                                            <flux:input 
                                                                type="number" 
                                                                wire:model.live.debounce.300ms="selectedProducts.{{ $index }}.cantidad"
                                                                wire:change="updateQuantity({{ $index }}, $event.target.value)"
                                                                min="1" 
                                                                class="w-20 text-center"
                                                            />
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-4">
                                                        <div class="flex justify-end items-center gap-2">
                                                            <flux:input 
                                                                type="number" 
                                                                wire:model.live.debounce.300ms="selectedProducts.{{ $index }}.costo"
                                                                wire:change="updateCosto({{ $index }}, $event.target.value)"
                                                                min="0" 
                                                                step="0.01"
                                                                class="w-24 text-right"
                                                            />
                                                            <span class="text-sm text-zinc-600 dark:text-zinc-400">L</span>
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-4">
                                                        <div class="flex justify-center items-center gap-2">
                                                            <flux:input 
                                                                type="number" 
                                                                wire:model.live.debounce.300ms="selectedProducts.{{ $index }}.impuesto"
                                                                wire:change="updateImpuesto({{ $index }}, $event.target.value)"
                                                                min="0" 
                                                                max="100"
                                                                step="0.01"
                                                                class="w-16 text-center"
                                                            />
                                                            <span class="text-sm text-zinc-600 dark:text-zinc-400">%</span>
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-4 text-right">
                                                        @php
                                                            $subtotalItem = ($item['costo'] ?? 0) * ($item['cantidad'] ?? 1);
                                                            $impuestoItem = ($subtotalItem * ($item['impuesto'] ?? 0)) / 100;
                                                            $totalItem = $subtotalItem + $impuestoItem;
                                                        @endphp
                                                        <span class="text-sm font-medium text-zinc-900 dark:text-white">
                                                            {{ number_format($totalItem, 2) }} L
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-4">
                                                        <button 
                                                            type="button"
                                                            wire:click="removeProduct({{ $index }})"
                                                            class="p-1 text-zinc-400 hover:text-red-600 dark:hover:text-red-400"
                                                        >
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                            </svg>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-12 text-zinc-500 dark:text-zinc-400">
                                    <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                    <p>No hay productos agregados</p>
                                </div>
                            @endif
                        </div>
                    </div>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Información adicional --}}
                    <div class="bg-white dark:bg-white/5 rounded-lg border border-zinc-200 dark:border-zinc-800 p-6">
                        <h3 class="text-base font-semibold text-zinc-900 dark:text-white mb-4">Información adicional</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Número de referencia</label>
                                <flux:input 
                                    wire:model="numero_referencia"
                                    placeholder="Ej: PO-001"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Nota para el distribuidor</label>
                                <flux:textarea 
                                    wire:model="nota_al_distribuidor"
                                    placeholder="Escribe una nota..."
                                    rows="4"
                                />
                                <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 text-right">
                                    {{ strlen($nota_al_distribuidor ?? '') }}/5000
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Etiquetas</label>
                                <flux:input 
                                    wire:model="id_etiquetas"
                                    placeholder="0/40"
                                />
                            </div>
                        </div>
                    </div>
                

                {{-- Columna lateral - Resumen de costos --}}
                <div class="space-y-6">
                    <div class="bg-white dark:bg-white/5 rounded-lg border border-zinc-200 dark:border-zinc-800 p-6">
                        <h3 class="text-base font-semibold text-zinc-900 dark:text-white mb-4">Resumen de costos</h3>
                        
                        <div class="space-y-3 mb-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-zinc-600 dark:text-zinc-400">Impuestos (Incluidos)</span>
                                <span class="font-medium text-zinc-900 dark:text-white">{{ number_format($impuestos, 2) }} L</span>
                            </div>
                            
                            <div class="flex justify-between text-sm">
                                <span class="text-zinc-600 dark:text-zinc-400">Subtotal</span>
                                <span class="font-medium text-zinc-900 dark:text-white">{{ number_format($subtotal, 2) }} L</span>
                            </div>

                            <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ count($selectedProducts) }} artículos
                            </div>

                            <div class="pt-3 border-t border-zinc-200 dark:border-zinc-700">
                                <h4 class="text-sm font-medium text-zinc-900 dark:text-white mb-3">Ajustes de costos</h4>
                                
                                @if(count($costAdjustments) > 0)
                                    <div class="space-y-2 mb-3">
                                        @foreach($costAdjustments as $index => $adjustment)
                                            <div class="flex justify-between items-center text-sm">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-zinc-600 dark:text-zinc-400">{{ $adjustment['nombre'] }}</span>
                                                    <button 
                                                        type="button"
                                                        wire:click="removeCostAdjustment({{ $index }})"
                                                        class="text-red-500 hover:text-red-700"
                                                    >
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                                <span class="font-medium text-zinc-900 dark:text-white">
                                                    {{ number_format($adjustment['importe'], 2) }} L
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                
                                <button 
                                    type="button" 
                                    wire:click="openCostAdjustmentModal"
                                    class="text-sm text-lime-600 dark:text-lime-400 hover:underline"
                                >
                                    + Gestionar
                                </button>
                            </div>

                            <div class="flex justify-between text-sm">
                                <span class="text-zinc-600 dark:text-zinc-400">Envío</span>
                                <flux:input 
                                    type="number" 
                                    wire:model.live.debounce.300ms="envio"
                                    min="0" 
                                    step="0.01"
                                    class="w-24 text-right"
                                    placeholder="0.00"
                                />
                            </div>

                            <div class="pt-3 border-t border-zinc-200 dark:border-zinc-700 flex justify-between">
                                <span class="font-semibold text-zinc-900 dark:text-white">Total</span>
                                <span class="font-bold text-lg text-zinc-900 dark:text-white">{{ number_format($total, 2) }} L</span>
                            </div>
                        </div>
                    </div>
                </div>
                </div>

                    {{-- Botones de acción --}}
                    <div class="space-y-3">
                        <flux:button 
                            type="submit"
                            variant="primary"
                            class="w-full"
                        >
                            Crear orden de compra
                        </flux:button>
                        
                        <flux:button 
                            type="button"
                            wire:click="cancel"
                            variant="ghost"
                            class="w-full"
                        >
                            Cancelar
                        </flux:button>
                    </div>
                </div>
            </div>
        </form>

        {{-- Modal de búsqueda de productos (mismo que CreateTransfer) --}}
        <flux:modal name="product-search-modal" class="max-w-4xl w-200" wire:model="showProductModal">
            <div class="flex flex-col h-[86vh] max-h-[86vh]">
                <div class="flex-shrink-0 px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Seleccionar productos</h2>
                </div>

                <div class="flex-1 flex flex-col min-h-0 p-6">
                    <div class="flex-shrink-0 mb-4">
                        <div class="flex gap-2 mb-3">
                            <div class="flex-1">
                                <flux:input 
                                    wire:model.live.debounce.300ms="searchProduct" 
                                    autofocus 
                                    icon="magnifying-glass"
                                    placeholder="Buscar productos..." 
                                    class="w-full" 
                                />
                            </div>
                            <div class="w-48">
                                <flux:select wire:model="searchFilter">
                                    <flux:select.option value="todo">Buscar por Todo</flux:select.option>
                                    <flux:select.option value="nombre">Por Nombre</flux:select.option>
                                    <flux:select.option value="sku">Por SKU</flux:select.option>
                                </flux:select>
                            </div>
                        </div>
                    </div>

                    <div class="flex-1 border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden">
                        <div class="h-full overflow-y-auto">
                            <table class="w-full">
                                <thead class="bg-zinc-50 dark:bg-zinc-800 sticky top-0">
                                    <tr>
                                        <th class="w-12 px-4 py-3"></th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-700 dark:text-zinc-300">Producto</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-700 dark:text-zinc-300">Disponible</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-zinc-700 dark:text-zinc-300">Costo</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-white/5 divide-y divide-zinc-200 dark:divide-zinc-700">
                                    @forelse($products as $product)
                                        @if($product->variants && $product->variants->count() > 0)
                                            <tr class="bg-zinc-50 dark:bg-zinc-800">
                                                <td class="px-4 py-3">
                                                    @php
                                                        $allVariantsSelected = true;
                                                        foreach ($product->variants as $v) {
                                                            if (!isset($tempSelectedProducts['variant_' . $v->id])) {
                                                                $allVariantsSelected = false;
                                                                break;
                                                            }
                                                        }
                                                    @endphp
                                                    <button 
                                                        type="button"
                                                        wire:click="toggleAllVariants({{ $product->id }}, '{{ addslashes($product->name) }}')"
                                                        class="inline-flex items-center justify-center w-8 h-8 rounded-full transition-colors {{ $allVariantsSelected ? 'bg-lime-600 text-white' : 'bg-zinc-200 text-zinc-400 dark:bg-zinc-700' }}"
                                                    >
                                                        @if($allVariantsSelected)
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                                        @else
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                                        @endif
                                                    </button>
                                                </td>
                                                <td colspan="3" class="px-4 py-3">
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-10 h-10 bg-zinc-900 dark:bg-zinc-700 rounded flex items-center justify-center">
                                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                                            </svg>
                                                        </div>
                                                        <div class="text-sm font-medium text-zinc-900 dark:text-white">{{ $product->name }}</div>
                                                    </div>
                                                </td>
                                            </tr>

                                            @foreach($product->variants as $variant)
                                                @php
                                                    $valores = $variant->valores_variante;
                                                    if (is_array($valores)) {
                                                        $variantDisplay = implode(' : ', array_values($valores));
                                                    } else {
                                                        $variantDisplay = $valores;
                                                    }
                                                @endphp
                                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                                    <td class="px-4 py-3">
                                                        <button 
                                                            type="button"
                                                            wire:click="toggleVariantSelection({{ $product->id }}, {{ $variant->id }}, '{{ addslashes($product->name) }}', '{{ addslashes($variantDisplay) }}', '{{ $variant->sku ?? $product->sku }}', {{ $variant->cantidad_inventario }}, {{ $variant->price ?? 0 }})"
                                                            class="inline-flex items-center justify-center w-8 h-8 rounded-full transition-colors {{ isset($tempSelectedProducts['variant_' . $variant->id]) ? 'bg-lime-600 text-white' : 'bg-zinc-200 text-zinc-400 dark:bg-zinc-700' }}"
                                                        >
                                                            @if(isset($tempSelectedProducts['variant_' . $variant->id]))
                                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                                            @else
                                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                                            @endif
                                                        </button>
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <div class="pl-10 text-sm text-zinc-600 dark:text-zinc-400">{{ $variantDisplay }}</div>
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <span class="text-sm text-zinc-900 dark:text-white">{{ $variant->cantidad_inventario }}</span>
                                                    </td>
                                                    <td class="px-4 py-3 text-right">
                                                        <span class="text-sm text-zinc-900 dark:text-white">{{ number_format($variant->price ?? 0, 2) }} L</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                                <td class="px-4 py-3">
                                                    <button 
                                                        type="button"
                                                        wire:click="toggleProductSelection({{ $product->id }}, '{{ addslashes($product->name) }}', '{{ $product->sku ?? '' }}', {{ $product->stock ?? 0 }}, {{ $product->price_unitario ?? 0 }})"
                                                        class="inline-flex items-center justify-center w-8 h-8 rounded-full transition-colors {{ isset($tempSelectedProducts['product_' . $product->id]) ? 'bg-lime-600 text-white' : 'bg-zinc-200 text-zinc-400 dark:bg-zinc-700' }}"
                                                    >
                                                        @if(isset($tempSelectedProducts['product_' . $product->id]))
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                                        @else
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                                        @endif
                                                    </button>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-10 h-10 bg-zinc-900 dark:bg-zinc-700 rounded flex items-center justify-center">
                                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                                            </svg>
                                                        </div>
                                                        <div class="text-sm font-medium text-zinc-900 dark:text-white">{{ $product->name }}</div>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <span class="text-sm text-zinc-900 dark:text-white">{{ $product->stock ?? 0 }}</span>
                                                </td>
                                                <td class="px-4 py-3 text-right">
                                                    <span class="text-sm text-zinc-900 dark:text-white">{{ number_format($product->price_unitario ?? 0, 2) }} L</span>
                                                </td>
                                            </tr>
                                        @endif
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-4 py-12 text-center text-zinc-500 dark:text-zinc-400">
                                                No se encontraron productos
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="flex-shrink-0 px-6 py-4 border-t border-zinc-200 dark:border-zinc-700 flex justify-between items-center">
                    <div class="text-sm text-zinc-600 dark:text-zinc-400">
                        {{ count($tempSelectedProducts) }} productos seleccionados
                    </div>
                    <div class="flex gap-2">
                        <flux:button type="button" wire:click="closeProductModal" variant="ghost">
                            Cancelar
                        </flux:button>
                        <flux:button type="button" wire:click="addSelectedProducts" variant="primary">
                            Agregar
                        </flux:button>
                    </div>
                </div>
            </div>
        </flux:modal>

        {{-- Modal de ajustes de costos --}}
        <flux:modal name="cost-adjustment-modal" class="max-w-md" wire:model="showCostAdjustmentModal">
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Agregar ajuste de costo</h2>
            </div>

            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Tipo de ajuste</label>
                    <flux:select wire:model="adjustmentType" placeholder="Seleccionar tipo...">
                        <flux:select.option value="">Seleccionar tipo de ajuste</flux:select.option>
                        @foreach($availableAdjustments as $key => $label)
                            <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Importe</label>
                    <flux:input 
                        type="number" 
                        wire:model="adjustmentAmount"
                        step="0.01"
                        placeholder="0.00"
                    />
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                        Usa números negativos para descuentos
                    </p>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-700 flex justify-end gap-2">
                <flux:button type="button" wire:click="closeCostAdjustmentModal" variant="ghost">
                    Cancelar
                </flux:button>
                <flux:button type="button" wire:click="addCostAdjustment" variant="primary">
                    Agregar ajuste
                </flux:button>
            </div>
        </flux:modal>
    </div>
</div>
