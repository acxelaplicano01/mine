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
                    <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">Agregar colección</h1>
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
                    {{-- Información básica --}}
                    <div class="bg-white dark:bg-white/5 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                    Título <span class="text-red-500">*</span>
                                </label>
                                <flux:input 
                                    wire:model="name"
                                    placeholder="p. ej., colección de verano, menos de 100$, nuestros favoritos"
                                />
                                @error('name')
                                    <span class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                    Descripción
                                </label>
                                <flux:textarea 
                                    wire:model="description"
                                    placeholder="Escribe la descripción de la colección..."
                                    rows="4"
                                />
                            </div>
                        </div>
                    </div>

                    {{-- Tipo de colección --}}
                    <div class="bg-white dark:bg-white/5 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                        <h3 class="text-base font-semibold text-zinc-900 dark:text-white mb-4">Tipo de colección</h3>
                        
                        <div class="space-y-3">
                            <label class="flex items-start gap-3 p-3 border border-zinc-200 dark:border-zinc-700 rounded-lg cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800/50 {{ $id_tipo_collection == 1 ? 'bg-zinc-50 dark:bg-zinc-800/50 border-zinc-900 dark:border-zinc-500' : '' }}">
                                <input type="radio" wire:model.live="id_tipo_collection" value="1" />
                                <div class="flex-1">
                                    <div class="font-medium text-zinc-900 dark:text-white">Manual</div>
                                    <div class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">
                                        Agrega productos a esta colección uno por uno. 
                                        <a href="#" class="text-blue-600 hover:text-blue-700 dark:text-blue-400">Más información sobre colecciones manuales</a>
                                    </div>
                                </div>
                            </label>

                            <label class="flex items-start gap-3 p-3 border border-zinc-200 dark:border-zinc-700 rounded-lg cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800/50 {{ $id_tipo_collection == 2 ? 'bg-zinc-50 dark:bg-zinc-800/50 border-zinc-900 dark:border-zinc-500' : '' }}">
                                <input type="radio" wire:model.live="id_tipo_collection" value="2" />
                                <div class="flex-1">
                                    <div class="font-medium text-zinc-900 dark:text-white">Inteligente</div>
                                    <div class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">
                                        Los productos existentes y futuros que cumplan las condiciones que definas se añadirán automáticamente a esta colección. 
                                        <a href="#" class="text-blue-600 hover:text-blue-700 dark:text-blue-400">Más información sobre colecciones inteligentes</a>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Condiciones (solo para colecciones inteligentes) --}}
                    @if($id_tipo_collection == 2)
                        <div class="bg-white dark:bg-white/5 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                            <h3 class="text-base font-semibold text-zinc-900 dark:text-white mb-4">Condiciones</h3>
                            
                            {{-- Los productos deben cumplir --}}
                            <div class="mb-6">
                                <div class="flex items-center gap-6 text-sm">
                                    <span class="text-zinc-700 dark:text-zinc-300">Los productos deben cumplir:</span>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" wire:model.live="conditionMatch" value="all" class="w-4 h-4" />
                                        <span class="text-zinc-700 dark:text-zinc-300">todas las condiciones</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" wire:model.live="conditionMatch" value="any" class="w-4 h-4" />
                                        <span class="text-zinc-700 dark:text-zinc-300">cualquier condición</span>
                                    </label>
                                </div>
                            </div>

                            {{-- Lista de condiciones --}}
                            <div class="space-y-3">
                                @foreach($conditions as $index => $condition)
                                    <div class="flex gap-2 items-start">
                                        {{-- Campo --}}
                                        <div class="flex-1">
                                            <flux:select wire:model.live="conditions.{{ $index }}.field">
                                                <flux:select.option value="titulo">Título del producto</flux:select.option>
                                                <flux:select.option value="tipo">Tipo de producto</flux:select.option>
                                                <flux:select.option value="proveedor">Proveedor</flux:select.option>
                                                <flux:select.option value="etiqueta">Etiqueta</flux:select.option>
                                                <flux:select.option value="precio">Precio</flux:select.option>
                                                <flux:select.option value="precio_comparacion">Precio de comparación</flux:select.option>
                                                <flux:select.option value="peso">Peso</flux:select.option>
                                                <flux:select.option value="stock">Existencias</flux:select.option>
                                                <flux:select.option value="titulo_variante">Título de la variante</flux:select.option>
                                            </flux:select>
                                        </div>

                                        {{-- Operador --}}
                                        <div class="flex-1">
                                            <flux:select wire:model="conditions.{{ $index }}.operator">
                                                <flux:select.option value="igual">es igual a</flux:select.option>
                                                <flux:select.option value="diferente">no es igual a</flux:select.option>
                                                <flux:select.option value="contiene">contiene</flux:select.option>
                                                <flux:select.option value="no_contiene">no contiene</flux:select.option>
                                                <flux:select.option value="mayor">es mayor que</flux:select.option>
                                                <flux:select.option value="menor">es menor que</flux:select.option>
                                                <flux:select.option value="empieza">empieza con</flux:select.option>
                                                <flux:select.option value="termina">termina con</flux:select.option>
                                            </flux:select>
                                        </div>

                                        {{-- Valor --}}
                                        <div class="flex-1">
                                            @if(isset($condition['field']))
                                                @if($condition['field'] === 'proveedor')
                                                    {{-- Select para Proveedor --}}
                                                    <flux:select wire:model="conditions.{{ $index }}.value">
                                                        <flux:select.option value="">Selecciona un proveedor</flux:select.option>
                                                        @foreach($distribuidores as $distribuidor)
                                                            <flux:select.option value="{{ $distribuidor['id'] }}">{{ $distribuidor['name'] ?? 'Sin nombre' }}</flux:select.option>
                                                        @endforeach
                                                    </flux:select>
                                                @elseif($condition['field'] === 'tipo')
                                                    {{-- Select para Tipo de Producto --}}
                                                    <flux:select wire:model="conditions.{{ $index }}.value">
                                                        <flux:select.option value="">Selecciona un tipo</flux:select.option>
                                                        @foreach($tipos as $tipo)
                                                            <flux:select.option value="{{ $tipo['id'] }}">{{ $tipo['name'] ?? 'Sin nombre' }}</flux:select.option>
                                                        @endforeach
                                                    </flux:select>
                                                @elseif($condition['field'] === 'etiqueta')
                                                    {{-- Select para Etiqueta --}}
                                                    <flux:select wire:model="conditions.{{ $index }}.value">
                                                        <flux:select.option value="">Selecciona una etiqueta</flux:select.option>
                                                        @foreach($etiquetas as $etiqueta)
                                                            <flux:select.option value="{{ $etiqueta['id'] }}">{{ $etiqueta['name'] ?? 'Sin nombre' }}</flux:select.option>
                                                        @endforeach
                                                    </flux:select>
                                                @elseif($condition['field'] === 'precio' || $condition['field'] === 'precio_comparacion' || $condition['field'] === 'peso' || $condition['field'] === 'stock')
                                                    {{-- Input numérico --}}
                                                    <flux:input 
                                                        wire:model="conditions.{{ $index }}.value"
                                                        type="number"
                                                        step="0.01"
                                                        placeholder="Ingresa un valor"
                                                    />
                                                @else
                                                    {{-- Input de texto por defecto --}}
                                                    <flux:input 
                                                        wire:model="conditions.{{ $index }}.value"
                                                        placeholder="Ingresa un valor"
                                                    />
                                                @endif
                                            @else
                                                {{-- Input por defecto si no hay field --}}
                                                <flux:input 
                                                    wire:model="conditions.{{ $index }}.value"
                                                    placeholder="Ingresa un valor"
                                                />
                                            @endif
                                        </div>

                                        {{-- Botón eliminar --}}
                                        @if(count($conditions) > 1)
                                            <button 
                                                type="button"
                                                wire:click="removeCondition({{ $index }})"
                                                class="p-2 text-zinc-400 hover:text-red-600 dark:hover:text-red-400"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            {{-- Botón agregar condición --}}
                            <div class="mt-4">
                                <flux:button 
                                    type="button" 
                                    wire:click="addCondition" 
                                    size="sm"
                                    icon="plus"
                                >
                                    Agregar otra condición
                                </flux:button>
                            </div>
                        </div>
                    @endif

                    {{-- Productos (solo para colecciones manuales) --}}
                    @if($id_tipo_collection == 1)
                        <div class="bg-white dark:bg-white/5 rounded-lg border border-zinc-200 dark:border-zinc-800">
                            <div class="p-6 border-b border-zinc-200 dark:border-zinc-800">
                                <h3 class="text-base font-semibold text-zinc-900 dark:text-white">Productos</h3>
                            </div>

                            <div class="p-6">
                                <div class="flex gap-2 mb-4">
                                    <flux:input 
                                        wire:click="openProductModal" 
                                        icon="magnifying-glass"
                                        placeholder="Buscar productos" 
                                        class="flex-1 cursor-pointer" 
                                        readonly 
                                    />
                                    <flux:button type="button" wire:click="openProductModal">
                                        Explorar
                                    </flux:button>
                                </div>

                                {{-- Lista de productos seleccionados --}}
                                @if(count($selectedProducts) > 0)
                                    <div class="space-y-2">
                                        @foreach($selectedProducts as $index => $item)
                                            <div class="flex items-center gap-3 p-3 border border-zinc-200 dark:border-zinc-700 rounded-lg">
                                                <div class="w-10 h-10 bg-zinc-100 dark:bg-zinc-700 rounded flex items-center justify-center">
                                                    <svg class="w-6 h-6 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                                    </svg>
                                                </div>
                                                <div class="flex-1">
                                                    <div class="text-sm font-medium text-zinc-900 dark:text-white">{{ $item['name'] }}</div>
                                                    @if($item['sku'])
                                                        <div class="text-xs text-zinc-500 dark:text-zinc-400">SKU: {{ $item['sku'] }}</div>
                                                    @endif
                                                </div>
                                                <button 
                                                    type="button"
                                                    wire:click="removeProduct({{ $index }})"
                                                    class="p-1 text-zinc-400 hover:text-red-600 dark:hover:text-red-400"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-8 text-zinc-500 dark:text-zinc-400">
                                        <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                        </svg>
                                        <p class="text-sm">No hay productos en esta colección</p>
                                        <p class="text-xs mt-1">Busca o navega para agregar productos</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Publicación en motores de búsqueda --}}
                    <div class="bg-white dark:bg-white/5 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h3 class="text-base font-semibold text-zinc-900 dark:text-white">Publicación en motores de búsqueda</h3>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">
                                    Agrega un título y una descripción para ver cómo podría aparecer esta colección en una publicación de motor de búsqueda
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Columna lateral --}}
                <div class="space-y-6">
                    {{-- Publicación --}}
                    <div class="bg-white dark:bg-white/5 rounded-lg border border-zinc-200 dark:border-zinc-800 p-6">
                        <h3 class="text-base font-semibold text-zinc-900 dark:text-white mb-4">Publicación</h3>
                        
                        <div class="space-y-3">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-zinc-600 dark:text-zinc-400">Canales de ventas</span>
                                <button type="button" class="text-blue-600 hover:text-blue-700 dark:text-blue-400">
                                    Gestionar
                                </button>
                            </div>
                            
                            <div class="space-y-2">
                                <label class="flex items-center gap-2">
                                    <flux:checkbox wire:model="id_publicacion" value="1" />
                                    <span class="text-sm text-zinc-700 dark:text-zinc-300">Tienda online</span>
                                </label>
                                <label class="flex items-center gap-2">
                                    <flux:checkbox disabled />
                                    <span class="text-sm text-zinc-500 dark:text-zinc-500 flex items-center gap-1">
                                        Point of Sale
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Imagen --}}
                    <div class="bg-white dark:bg-white/5 rounded-lg border border-zinc-200 dark:border-zinc-800 p-6">
                        <h3 class="text-base font-semibold text-zinc-900 dark:text-white mb-4">Imagen</h3>
                        
                        <div class="border-2 border-dashed border-zinc-300 dark:border-zinc-600 rounded-lg p-6 text-center">
                            <svg class="w-12 h-12 mx-auto text-zinc-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <button type="button" class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 font-medium">
                                Agregar imagen
                            </button>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                                o arrastra una imagen para subirla
                            </p>
                        </div>
                    </div>

                    {{-- Plantilla de tema --}}
                    <div class="bg-white dark:bg-white/5 rounded-lg border border-zinc-200 dark:border-zinc-800 p-6">
                        <h3 class="text-base font-semibold text-zinc-900 dark:text-white mb-4">Plantilla de tema</h3>
                        
                        <flux:select>
                            <flux:select.option value="default">Colección predeterminada</flux:select.option>
                        </flux:select>
                    </div>

                    {{-- Botones de acción --}}
                    <div class="space-y-3">
                        <flux:button 
                            type="submit"
                            variant="primary"
                            class="w-full"
                        >
                            Guardar
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
                                <flux:select wire:model.live="searchFilter">
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
                                                            wire:click="toggleVariantSelection({{ $product->id }}, {{ $variant->id }}, '{{ addslashes($product->name) }}', '{{ addslashes($variantDisplay) }}', '{{ $variant->sku ?? $product->sku }}')"
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
                                                </tr>
                                            @endforeach
                                        @else
                                            {{-- Producto sin variantes --}}
                                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                                <td class="px-4 py-3">
                                                    <button 
                                                        type="button"
                                                        wire:click="toggleProductSelection({{ $product->id }}, '{{ addslashes($product->name) }}', '{{ $product->sku ?? '' }}')"
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
                                            </tr>
                                        @endif
                                    @empty
                                        <tr>
                                            <td colspan="2" class="px-4 py-12 text-center text-zinc-500 dark:text-zinc-400">
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
