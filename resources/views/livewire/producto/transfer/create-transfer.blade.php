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
                    <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">Crear transferencia</h1>
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
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Columna principal --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Origen y Destino --}}
                    <div class="bg-white dark:bg-white/5 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                    Origen 
                                    <svg class="w-4 h-4 inline text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </label>
                                <flux:select wire:model="id_sucursal_origen" placeholder="Seleccionar origen...">
                                    @foreach($branches as $branch)
                                        <flux:select.option value="{{ $branch->id }}">{{ $branch->name }}</flux:select.option>
                                    @endforeach
                                </flux:select>
                                @if($id_sucursal_origen)
                                    @php
                                        $origen = $branches->firstWhere('id', $id_sucursal_origen);
                                    @endphp
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">{{ $origen->name ?? '' }}, {{ $origen->name ?? '' }}</div>
                                @endif
                                @error('id_sucursal_origen')
                                    <span class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                    Destino 
                                    <svg class="w-4 h-4 inline text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </label>
                                <flux:select wire:model="id_sucursal_destino" placeholder="Seleccionar destino...">
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
                                        <thead class="bg-zinc-50 dark:bg-zinc-900">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Productos</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">SKU</th>
                                                <th class="px-4 py-3 text-center text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">En origen</th>
                                                <th class="px-4 py-3 text-center text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Cantidad</th>
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
                                                        @if($id_sucursal_origen && $item['stock'] < $item['cantidad'])
                                                            <div class="flex items-center gap-1 text-xs text-orange-600 dark:text-orange-400 mt-2">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                                </svg>
                                                                Solo hay {{ $item['stock'] }} artículos disponibles en {{ $branches->firstWhere('id', $id_sucursal_origen)->name ?? 'origen' }}
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-4">
                                                        <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $item['sku'] ?? 'N/A' }}</span>
                                                    </td>
                                                    <td class="px-4 py-4 text-center">
                                                        <span class="text-sm text-zinc-900 dark:text-white">{{ $item['stock'] ?? 0 }}</span>
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
                </div>

                {{-- Columna lateral --}}
                <div class="space-y-6">
                    {{-- Notas --}}
                    <div class="bg-white dark:bg-white/5 rounded-lg border border-zinc-200 dark:border-zinc-800 p-6">
                        <h3 class="text-base font-semibold text-zinc-900 dark:text-white mb-4">Notas</h3>
                        <flux:textarea 
                            wire:model="nota_interna"
                            placeholder="Sin notas"
                            rows="3"
                        />
                    </div>

                    {{-- Detalles de la transferencia --}}
                    <div class="bg-white dark:bg-white/5 rounded-lg border border-zinc-200 dark:border-zinc-800 p-6">
                        <h3 class="text-base font-semibold text-zinc-900 dark:text-white mb-4">Detalles de la transferencia</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Fecha de creación</label>
                                <flux:input 
                                    type="date"
                                    wire:model="fecha_envio_creacion"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Nombre de referencia</label>
                                <flux:input 
                                    wire:model="nombre_referencia"
                                    placeholder="Ej: TRANS-001"
                                />
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

                    {{-- Botones de acción --}}
                    <div class="space-y-3">
                        <flux:button 
                            type="submit"
                            variant="primary"
                            class="w-full"
                        >
                            Crear transferencia
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

        {{-- Modal de búsqueda de productos --}}
        <flux:modal name="product-search-modal" class="max-w-4xl w-200" wire:model="showProductModal">
            <div class="flex flex-col h-[86vh] max-h-[86vh]">
                {{-- Header --}}
                <div class="flex-shrink-0 px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Seleccionar productos</h2>
                </div>

                {{-- Content --}}
                <div class="flex-1 flex flex-col min-h-0 p-6">
                    {{-- Búsqueda y filtros --}}
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

                    {{-- Tabla de productos --}}
                    <div class="flex-1 border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden">
                        <div class="h-full overflow-y-auto">
                            <table class="w-full">
                                <thead class="bg-zinc-50 dark:bg-white/5 sticky top-0">
                                    <tr>
                                        <th class="w-12 px-4 py-3"></th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-700 dark:text-zinc-300">Producto</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-700 dark:text-zinc-300">Disponible</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-white/5 divide-y divide-zinc-200 dark:divide-zinc-700">
                                    @forelse($products as $product)
                                        {{-- Producto con variantes --}}
                                        @if($product->variants && $product->variants->count() > 0)
                                            <tr class="bg-zinc-50 dark:bg-zinc-900/50">
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
                                                <td colspan="2" class="px-4 py-3">
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
                                                            wire:click="toggleVariantSelection({{ $product->id }}, {{ $variant->id }}, '{{ addslashes($product->name) }}', '{{ addslashes($variantDisplay) }}', '{{ $variant->sku ?? $product->sku }}', {{ $variant->cantidad_inventario }})"
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
                                                </tr>
                                            @endforeach
                                        @else
                                            {{-- Producto sin variantes --}}
                                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                                <td class="px-4 py-3">
                                                    <button 
                                                        type="button"
                                                        wire:click="toggleProductSelection({{ $product->id }}, '{{ addslashes($product->name) }}', '{{ $product->sku ?? '' }}', {{ $product->stock ?? 0 }})"
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
                                            </tr>
                                        @endif
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-4 py-12 text-center text-zinc-500 dark:text-zinc-400">
                                                No se encontraron productos
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
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
    </div>
</div>
