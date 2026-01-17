<div class="min-h-screen">
    <div class="max-w-5xl mx-auto px-2 sm:px-6 lg:px-6 py-6">
        <form wire:submit.prevent="save">
            {{-- Header --}}
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">
                    {{ $isEdit ? 'Editar descuento' : 'Crear descuento' }}
                </h1>
                
                <div class="flex gap-3">
                    <flux:button href="{{ route('discounts') }}" variant="ghost">
                        Cancelar
                    </flux:button>
                    <flux:button type="submit" variant="primary">
                        Guardar
                    </flux:button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Columna principal --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Método (Pestañas) --}}
                    <div class="bg-white dark:bg-white/5 rounded-lg shadow-sm p-6">
                        <div class="border-b border-zinc-200 dark:border-zinc-700 -mx-6 px-6 pb-0 mb-6">
                            <nav class="flex -mb-px gap-6">
                                <button
                                    type="button"
                                    wire:click="$set('activeTab', 'codigo')"
                                    class="pb-3 text-sm font-medium {{ $activeTab === 'codigo' ? 'border-b-2 border-lime-600 text-lime-600' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white' }}"
                                >
                                    Código de descuento
                                </button>
                                <button
                                    type="button"
                                    wire:click="$set('activeTab', 'automatico')"
                                    class="pb-3 text-sm font-medium {{ $activeTab === 'automatico' ? 'border-b-2 border-lime-600 text-lime-600' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white' }}"
                                >
                                    Descuento automático
                                </button>
                            </nav>
                        </div>

                        <div class="space-y-6">
                            {{-- Método --}}
                            <div>
                                <flux:label>Método</flux:label>
                                <div class="flex items-center gap-2 mt-2">
                                    @if($activeTab === 'codigo')
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                        </svg>
                                        <span class="text-sm text-zinc-900 dark:text-white">Código de descuento</span>
                                    @else
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                        <span class="text-sm text-zinc-900 dark:text-white">Descuento automático</span>
                                    @endif
                                </div>
                            </div>

                            {{-- Código de descuento (solo si es código) --}}
                            @if($activeTab === 'codigo')
                                <div>
                                    <flux:label class="text-sm font-medium mb-2">Código de descuento</flux:label>
                                    <div class="flex gap-2">
                                        <flux:input 
                                            wire:model="code_discount"
                                            placeholder="Ej: SUMMER2024"
                                            class="flex-1"
                                        />
                                        <flux:button 
                                            type="button"
                                            wire:click="generateRandomCode"
                                            variant="filled"
                                        >
                                            Generar código aleatorio
                                        </flux:button>
                                    </div>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                                        Los clientes deben introducir este código en el pago.
                                    </p>
                                </div>
                            @endif

                            {{-- Tipo de descuento --}}
                            <div>
                                <flux:label>Tipo</flux:label>
                                <flux:select wire:model="id_type_discount">
                                    @foreach($typeDiscounts as $type)
                                        <flux:select.option value="{{ $type->id }}">
                                            {{ $type->name }}
                                        </flux:select.option>
                                    @endforeach
                                </flux:select>
                            </div>

                            {{-- Valor del descuento --}}
                            <div>
                                <flux:label>Valor del descuento</flux:label>
                                <div class="grid grid-cols-2 gap-3">
                                    <flux:select wire:model.live="discount_value_type">
                                        <flux:select.option value="percentage">Porcentaje</flux:select.option>
                                        <flux:select.option value="fixed_amount">Monto fijo</flux:select.option>
                                    </flux:select>
                                    <div class="relative">
                                        <flux:input 
                                            wire:model="valor_discount"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            placeholder="0"
                                            class="pr-10"
                                        />
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm font-medium text-zinc-600 dark:text-zinc-400">
                                            {{ $discount_value_type === 'percentage' ? '%' : 'L' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- Se aplica a --}}
                            <div class="pt-2">
                                <div class="mt-4 space-y-4">
                                    {{-- Select para elegir entre colecciones o productos --}}
                                    <div>
                                        <flux:label>Se aplica a</flux:label>
                                        <flux:select wire:model.live="applies_to">
                                            <flux:select.option value="collections">Colecciones específicas</flux:select.option>
                                            <flux:select.option value="products">Productos específicos</flux:select.option>
                                        </flux:select>
                                    </div>
                                    
                                    {{-- Búsqueda de productos --}}
                                    @if($applies_to === 'products')
                                        <div class="flex gap-2">
                                            <flux:input 
                                                wire:click="openProductModal"
                                                placeholder="Buscar productos..."
                                                class="flex-1 cursor-pointer"
                                                readonly
                                            />
                                            <flux:button type="button" wire:click="openProductModal">
                                                Explorar
                                            </flux:button>
                                        </div>

                                        {{-- Lista de productos seleccionados --}}
                                        @if(count($selected_products) > 0)
                                            <div class="mt-4 border border-zinc-200 dark:border-zinc-700 rounded-lg divide-y divide-zinc-200 dark:divide-zinc-700">
                                                @foreach($selected_products as $key => $product)
                                                    <div class="p-3 flex items-center justify-between">
                                                        <div class="flex items-center gap-3">
                                                            <div class="w-8 h-8 bg-zinc-100 dark:bg-zinc-700 rounded flex items-center justify-center flex-shrink-0">
                                                                <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                                                </svg>
                                                            </div>
                                                            <div>
                                                                <div class="text-sm font-medium text-zinc-900 dark:text-white">{{ $product['name'] }}</div>
                                                                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ number_format($product['price'], 2) }} L</div>
                                                            </div>
                                                        </div>
                                                        <button 
                                                            type="button"
                                                            wire:click="removeProduct('{{ $key }}')"
                                                            class="text-zinc-400 hover:text-red-600"
                                                        >
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    @endif
                                    
                                    {{-- Búsqueda de colecciones --}}
                                    @if($applies_to === 'collections')
                                        <div class="flex gap-2">
                                            <flux:input 
                                                wire:click="openCollectionModal"
                                                placeholder="Buscar colecciones"
                                                class="flex-1 cursor-pointer"
                                                readonly
                                            />
                                            <flux:button wire:click="openCollectionModal" type="button">
                                                Explorar
                                            </flux:button>
                                        </div>
                                        
                                        {{-- Lista de colecciones seleccionadas --}}
                                        @if(count($selected_collections) > 0)
                                            <div class="mt-4 border border-zinc-200 dark:border-zinc-700 rounded-lg divide-y divide-zinc-200 dark:divide-zinc-700">
                                                @foreach($selected_collections as $key => $collection)
                                                    <div class="p-3 flex items-center justify-between">
                                                        <div class="flex items-center gap-3">
                                                            <div class="w-8 h-8 bg-zinc-100 dark:bg-zinc-700 rounded flex items-center justify-center flex-shrink-0">
                                                                <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                                                </svg>
                                                            </div>
                                                            <div class="text-sm font-medium text-zinc-900 dark:text-white">{{ $collection['name'] }}</div>
                                                        </div>
                                                        <button 
                                                            type="button"
                                                            wire:click="removeCollection('{{ $key }}')"
                                                            class="text-zinc-400 hover:text-red-600"
                                                        >
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                   

                    {{-- Usos máximos --}}
                    <div class="bg-white dark:bg-white/5 rounded-lg shadow-sm p-6">
                        <h3 class="text-base font-semibold text-zinc-900 dark:text-white mb-4">
                            Usos máximos del descuento
                        </h3>

                        <div class="space-y-4">
                            <div>
                                <label class="flex items-start cursor-pointer">
                                    <input type="checkbox" wire:model.live="limit_usage" class="sr-only peer">
                                    <div class="relative w-11 h-6 bg-zinc-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-lime-300 dark:peer-focus:ring-lime-800 rounded-full peer dark:bg-zinc-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-zinc-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-zinc-600 peer-checked:bg-lime-600 flex-shrink-0"></div>
                                    <span class="ms-3 text-sm text-zinc-900 dark:text-zinc-300">Limitar el número total de veces que se puede usar este descuento</span>
                                </label>
                                @if($limit_usage)
                                    <div class="ml-6 mt-2">
                                        <flux:input 
                                            wire:model.defer="number_usage_max"
                                            type="number"
                                            min="1"
                                            placeholder="Ejemplo: 100"
                                        />
                                    </div>
                                @endif
                            </div>

                            <div>
                                <label class="flex items-start cursor-pointer">
                                    <input type="checkbox" wire:model.live="limit_per_customer" class="sr-only peer">
                                    <div class="relative w-11 h-6 bg-zinc-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-lime-300 dark:peer-focus:ring-lime-800 rounded-full peer dark:bg-zinc-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-zinc-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-zinc-600 peer-checked:bg-lime-600 flex-shrink-0"></div>
                                    <span class="ms-3 text-sm text-zinc-900 dark:text-zinc-300">Limitar a un uso por cliente</span>
                                </label>
                                @if($limit_per_customer)
                                    <p class="ml-6 mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                                        Cada cliente podrá usar este descuento solo una vez.
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Combinaciones --}}
                    <div class="bg-white dark:bg-white/5 rounded-lg shadow-sm p-6">
                        <h3 class="text-base font-semibold text-zinc-900 dark:text-white mb-4">
                            Combinaciones
                        </h3>

                        <div class="space-y-4">
                            <div>
                                <label class="flex items-start cursor-pointer">
                                    <input type="checkbox" wire:model.live="combine_with_product" class="sr-only peer">
                                    <div class="relative w-11 h-6 bg-zinc-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-lime-300 dark:peer-focus:ring-lime-800 rounded-full peer dark:bg-zinc-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-zinc-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-zinc-600 peer-checked:bg-lime-600 flex-shrink-0"></div>
                                    <span class="ms-3 text-sm text-zinc-900 dark:text-zinc-300">Descuentos de producto</span>
                                </label>
                                @if($combine_with_product)
                                    <p class="ml-6 mt-2 text-xs text-zinc-600 dark:text-zinc-400 bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg">
                                        Cada artículo apto del carrito puede recibir como máximo un descuento de producto.
                                    </p>
                                @endif
                            </div>
                            
                            <div>
                                <label class="flex items-start cursor-pointer">
                                    <input type="checkbox" wire:model.live="combine_with_order" class="sr-only peer">
                                    <div class="relative w-11 h-6 bg-zinc-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-lime-300 dark:peer-focus:ring-lime-800 rounded-full peer dark:bg-zinc-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-zinc-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-zinc-600 peer-checked:bg-lime-600 flex-shrink-0"></div>
                                    <span class="ms-3 text-sm text-zinc-900 dark:text-zinc-300">Descuentos de pedido</span>
                                </label>
                                @if($combine_with_order)
                                    <p class="ml-6 mt-2 text-xs text-zinc-600 dark:text-zinc-400 bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg">
                                        Todos los descuentos de pedido aptos aplicarán además de los descuentos de producto aptos.
                                    </p>
                                @endif
                            </div>
                            
                            <div>
                                <label class="flex items-start cursor-pointer">
                                    <input type="checkbox" wire:model.live="combine_with_shipping" class="sr-only peer">
                                    <div class="relative w-11 h-6 bg-zinc-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-lime-300 dark:peer-focus:ring-lime-800 rounded-full peer dark:bg-zinc-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-zinc-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-zinc-600 peer-checked:bg-lime-600 flex-shrink-0"></div>
                                    <span class="ms-3 text-sm text-zinc-900 dark:text-zinc-300">Descuentos de envío</span>
                                </label>
                                @if($combine_with_shipping)
                                    <p class="ml-6 mt-2 text-xs text-zinc-600 dark:text-zinc-400 bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg">
                                        El descuento de envío apto de mayor importe se aplicará además de los descuentos de producto aptos.
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Fechas de activación --}}
                    <div class="bg-white dark:bg-white/5 rounded-lg shadow-sm p-6">
                        <h3 class="text-base font-semibold text-zinc-900 dark:text-white mb-4">
                            Fechas de activación
                        </h3>

                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <flux:label>Fecha de inicio</flux:label>
                                    <flux:input 
                                        wire:model="fecha_inicio_uso"
                                        type="date"
                                    />
                                </div>
                                <div>
                                    <flux:label>Hora de inicio (CST)</flux:label>
                                    <flux:input 
                                        wire:model="hora_inicio_uso"
                                        type="time"
                                    />
                                </div>
                            </div>

                            <label class="flex items-start cursor-pointer">
                                <input type="checkbox" wire:model.live="set_end_date" class="sr-only peer">
                                <div class="relative w-11 h-6 bg-zinc-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-lime-300 dark:peer-focus:ring-lime-800 rounded-full peer dark:bg-zinc-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-zinc-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-zinc-600 peer-checked:bg-lime-600 flex-shrink-0"></div>
                                <span class="ms-3 text-sm text-zinc-900 dark:text-zinc-300">Establecer fecha de finalización</span>
                            </label>

                            @if($set_end_date)
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <flux:label>Fecha de fin</flux:label>
                                        <flux:input 
                                            wire:model="fecha_fin_uso"
                                            type="date"
                                        />
                                    </div>
                                    <div>
                                        <flux:label>Hora de fin (CST)</flux:label>
                                        <flux:input 
                                            wire:model="hora_fin_uso"
                                            type="time"
                                        />
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Columna lateral --}}
                <div class="space-y-6">
                    {{-- Resumen --}}
                    <div class="bg-white dark:bg-white/5 rounded-lg shadow-sm p-6">
                        <h3 class="text-base font-semibold text-zinc-900 dark:text-white mb-4">Resumen</h3>
                        
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-zinc-600 dark:text-zinc-400">Tipo:</span>
                                <span class="text-zinc-900 dark:text-white">
                                    @foreach($typeDiscounts as $type)
                                        @if($type->id == $id_type_discount)
                                            {{ $type->name }}
                                        @endif
                                    @endforeach
                                </span>
                            </div>

                            <div class="flex justify-between">
                                <span class="text-zinc-600 dark:text-zinc-400">Método:</span>
                                <span class="text-zinc-900 dark:text-white">
                                    {{ $activeTab === 'codigo' ? 'Código de descuento' : 'Descuento automático' }}
                                </span>
                            </div>

                            @if($activeTab === 'codigo' && $code_discount)
                                <div class="flex justify-between">
                                    <span class="text-zinc-600 dark:text-zinc-400">Código:</span>
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200 font-mono">
                                        {{ $code_discount }}
                                    </span>
                                </div>
                            @endif

                            <div class="flex justify-between">
                                <span class="text-zinc-600 dark:text-zinc-400">Valor:</span>
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-lime-100 text-lime-800 dark:bg-lime-900 dark:text-lime-200">
                                    {{ $valor_discount }}{{ $discount_value_type === 'percentage' ? '%' : 'L' }}
                                </span>
                            </div>

                            @if($fecha_inicio_uso)
                                <div class="flex justify-between">
                                    <span class="text-zinc-600 dark:text-zinc-400">Activo desde:</span>
                                    <span class="text-zinc-900 dark:text-white">
                                        {{ \Carbon\Carbon::parse($fecha_inicio_uso)->format('d/m/Y') }}
                                    </span>
                                </div>
                            @endif

                            @if($set_end_date && $fecha_fin_uso)
                                <div class="flex justify-between">
                                    <span class="text-zinc-600 dark:text-zinc-400">Activo hasta:</span>
                                    <span class="text-zinc-900 dark:text-white">
                                        {{ \Carbon\Carbon::parse($fecha_fin_uso)->format('d/m/Y') }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Estado --}}
                    <div class="bg-white dark:bg-white/5 rounded-lg shadow-sm p-6">
                        <h3 class="text-base font-semibold text-zinc-900 dark:text-white mb-4">Estado</h3>
                        
                        <flux:select wire:model="id_status_discount">
                            @foreach($statusDiscounts as $status)
                                <flux:select.option value="{{ $status->id }}">
                                    {{ $status->name }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                </div>
            </div>
        </form>
        
        {{-- Modal de búsqueda de productos --}}
        <flux:modal name="product-modal" class="min-w-[800px] space-y-6">
        <div class="flex items-center justify-between">
            <flux:heading size="lg">Seleccionar productos</flux:heading>
        </div>

        <div class="space-y-4">
            <flux:input wire:model.live.debounce.300ms="searchProducts" placeholder="Buscar productos..." />
            
            <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden max-h-[500px] overflow-y-auto">
                <table class="w-full">
                    <thead class="bg-zinc-50 dark:bg-zinc-900 sticky top-0">
                        <tr>
                            <th class="w-12 px-4 py-3"></th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-zinc-700 dark:text-zinc-300">Producto</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-zinc-700 dark:text-zinc-300">Precio</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-white/5 divide-y divide-zinc-200 dark:divide-zinc-700">
                        @forelse($products as $product)
                            {{-- Si el producto tiene variantes --}}
                            @if($product->variants && count($product->variants) > 0)
                                {{-- Fila del producto principal con checkbox para seleccionar todas las variantes --}}
                                <tr class="bg-zinc-50 dark:bg-zinc-900/50">
                                    <td class="px-4 py-3 text-center">
                                        @php
                                            $allVariantsSelected = true;
                                            foreach($product->variants as $variant) {
                                                if(!isset($tempSelectedProducts['variant_' . $variant->id])) {
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
                                            <div class="w-10 h-10 bg-zinc-900 dark:bg-zinc-700 rounded flex items-center justify-center flex-shrink-0">
                                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                                </svg>
                                            </div>
                                            <div class="text-sm font-medium text-zinc-900 dark:text-white">{{ $product->name }}</div>
                                        </div>
                                    </td>
                                </tr>

                                {{-- Variantes del producto --}}
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
                                        <td class="px-4 py-3 text-center">
                                            <button 
                                                type="button"
                                                wire:click="toggleVariantSelection({{ $product->id }}, {{ $variant->id }}, '{{ addslashes($product->name) }}', '{{ addslashes($variantDisplay) }}', {{ $variant->price }}, {{ $variant->cantidad_inventario }})"
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
                                            <div class="pl-10">
                                                <div class="text-sm text-zinc-600 dark:text-zinc-400">{{ $variantDisplay }}</div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <span class="text-sm text-zinc-900 dark:text-white">{{ number_format($variant->price, 2) }} L</span>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                {{-- Producto sin variantes --}}
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                    <td class="px-4 py-3 text-center">
                                        <button 
                                            type="button"
                                            wire:click="toggleProductSelection({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->price_unitario ?? 0 }}, {{ $product->stock ?? 0 }})"
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
                                            <div class="w-10 h-10 bg-zinc-100 dark:bg-zinc-700 rounded flex items-center justify-center">
                                                <svg class="w-6 h-6 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                                </svg>
                                            </div>
                                            <div class="text-sm font-medium text-zinc-900 dark:text-white">{{ $product->name }}</div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <span class="text-sm text-zinc-900 dark:text-white">{{ number_format($product->price, 2) }} L</span>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-12 text-center text-zinc-500 dark:text-zinc-400">
                                    <svg class="mx-auto h-12 w-12 text-zinc-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                    </svg>
                                    <p class="text-sm">No se encontraron productos</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

            <div class="flex items-center justify-between pt-4 border-t border-zinc-200 dark:border-zinc-700">
                <span class="text-sm text-zinc-600 dark:text-zinc-400">
                    {{ count($tempSelectedProducts) }} producto(s) seleccionado(s)
                </span>
                <div class="flex gap-3">
                    <flux:button variant="ghost" wire:click="closeProductModal">Cancelar</flux:button>
                    <flux:button wire:click="confirmProductSelection" :disabled="count($tempSelectedProducts) === 0">Agregar</flux:button>
                </div>
            </div>
    </flux:modal>
    
    {{-- Modal de búsqueda de colecciones --}}
    <flux:modal name="collection-modal" class="min-w-[800px] space-y-6">
        <div class="flex items-center justify-between">
            <flux:heading size="lg">Seleccionar colecciones</flux:heading>
        </div>

        <div class="space-y-4">
            <flux:input wire:model.live.debounce.300ms="searchCollections" placeholder="Buscar colecciones..." />
            
            <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden max-h-[500px] overflow-y-auto">
                <table class="w-full">
                                <thead class="bg-zinc-50 dark:bg-zinc-900 sticky top-0">
                                    <tr>
                                        <th class="w-12 px-4 py-3"></th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-700 dark:text-zinc-300">Colección</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-zinc-700 dark:text-zinc-300">Productos</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-white/5 divide-y divide-zinc-200 dark:divide-zinc-700">
                                    @forelse($collections as $collection)
                                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                            <td class="px-4 py-3">
    <label class="relative inline-flex items-center cursor-pointer">
        <input 
            type="checkbox" 
            class="sr-only peer"
            onclick="$wire.toggleCollectionSelection({{ $collection->id }}, '{{ addslashes($collection->name) }}')"
            {{ isset($tempSelectedCollections['collection_' . $collection->id]) ? 'checked' : '' }}
        >
        <div class="w-11 h-6 bg-zinc-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-lime-300 dark:peer-focus:ring-lime-800 rounded-full peer dark:bg-zinc-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-lime-600"></div>
    </label>
</td>
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-10 h-10 bg-zinc-100 dark:bg-zinc-700 rounded flex items-center justify-center">
                                                        <svg class="w-6 h-6 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                                        </svg>
                                                    </div>
                                                    <div class="text-sm font-medium text-zinc-900 dark:text-white">{{ $collection->name }}</div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <span class="text-sm text-zinc-900 dark:text-white">{{ $collection->products_count ?? 0 }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-4 py-12 text-center text-zinc-500 dark:text-zinc-400">
                                                <svg class="mx-auto h-12 w-12 text-zinc-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                                </svg>
                                                <p class="text-sm">No se encontraron colecciones</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
            
            <div class="flex items-center justify-between pt-4 border-t border-zinc-200 dark:border-zinc-700">
                <span class="text-sm text-zinc-600 dark:text-zinc-400">
                    {{ count($tempSelectedCollections) }} colección(es) seleccionada(s)
                </span>
                <div class="flex gap-3">
                    <flux:button variant="ghost" wire:click="closeCollectionModal">Cancelar</flux:button>
                    <flux:button wire:click="confirmCollectionSelection" :disabled="count($tempSelectedCollections) === 0">Agregar</flux:button>
                </div>
            </div>
        </div>
    </flux:modal>
</div>
