<div class="min-h-screen max-w-5xl mx-auto">
    <form wire:submit.prevent="store">
        {{-- Header con breadcrumb --}}
        <div>
            <div class="px-2 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400">
                       
                    </div>
                    <flux:button 
                        type="button" 
                        wire:click="saveDraft"
                        variant="filled" 
                        size="sm"
                    >
                        Guardar borrador
                    </flux:button>
                </div>
            </div>
        </div>

        {{-- Contenido principal en dos columnas --}}
            <div class="flex-1 overflow-y-auto">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 p-4">
                    {{-- Columna principal (izquierda) --}}
                    <div class="lg:col-span-2 space-y-4">
                        {{-- Productos --}}
                        <div class="bg-white dark:bg-white/5 rounded-lg shadow-sm">
                            <div class="p-4 border-b border-zinc-200 dark:border-zinc-700">
                                <h3 class="text-base font-semibold text-zinc-900 dark:text-white">Productos</h3>
                            </div>
                            
                            <div class="p-4">
                                <div class="flex gap-2 mb-4">
                                    <flux:input 
                                        wire:click="openProductModal"
                                        icon="magnifying-glass"
                                        placeholder="Buscar productos"
                                        class="flex-1 cursor-pointer"
                                        readonly
                                    />
                                    <flux:button wire:click="openProductModal">
                                        Explorar
                                    </flux:button>
                                    <flux:button wire:click="openCustomProductModal">
                                        Agregar artículo personalizado
                                    </flux:button>
                                </div>

                                {{-- Lista de productos seleccionados --}}
                                @if(count($selectedProducts) > 0)
                                <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden">
                                    <table class="w-full">
                                        <thead class="bg-zinc-50 dark:bg-zinc-900">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Producto</th>
                                                <th class="px-4 py-3 text-center text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Cantidad</th>
                                                <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Total</th>
                                                <th class="w-12"></th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                                            @foreach($selectedProducts as $index => $item)
                                            <tr>
                                                <td class="px-4 py-4">
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-10 h-10 bg-zinc-100 dark:bg-zinc-700 rounded flex items-center justify-center flex-shrink-0">
                                                            <svg class="w-6 h-6 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                                            </svg>
                                                        </div>
                                                        <div>
                                                            <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                                                {{ $item['name'] }}
                                                            </div>
                                                            <div class="text-xs text-zinc-500 dark:text-zinc-400">Requiere envío</div>
                                                            <div class="text-sm font-medium text-blue-600 dark:text-blue-400 mt-1">
                                                                {{ number_format($item['price'], 2) }} L
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-4">
                                                    <div class="flex justify-center">
                                                        <flux:input 
                                                            wire:change="updateQuantity({{ $index }}, $event.target.value)"
                                                            type="number"
                                                            min="1"
                                                            value="{{ $item['quantity'] }}"
                                                            class="w-20 text-center"
                                                        />
                                                    </div>
                                                </td>
                                                <td class="px-4 py-4 text-right">
                                                    <span class="text-sm font-medium text-zinc-900 dark:text-white">
                                                        {{ number_format($item['price'] * $item['quantity'], 2) }} L
                                                    </span>
                                                </td>
                                                <td class="px-4 py-4 text-center">
                                                    <button type="button" wire:click="removeProduct({{ $index }})" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- Pago --}}
                        <div class="bg-white dark:bg-white/5 rounded-lg shadow-sm">
                            <div class="px-4 pt-4">
                                <h3 class="text-base font-semibold text-zinc-900 dark:text-white">Pago</h3>
                            </div>
                            
                        <div class="p-4 space-y-4">
                            <div class="p-4 border border-zinc-200 dark:border-zinc-700 rounded-md">
                                {{-- Subtotal --}}
                                <div class="flex justify-between items-center py-2">
                                    <div class="text-sm text-zinc-600 dark:text-zinc-400">
                                        <span>Subtotal</span>
                                        <span class="text-zinc-500 ml-1">{{ $quantity }} artículo{{ $quantity > 1 ? 's' : '' }}</span>
                                    </div>
                                    <div class="text-sm text-zinc-900 dark:text-white">
                                        {{ number_format($subtotal_price, 2) }} L
                                    </div>
                                </div>

                                {{-- Editar descuento --}}
                                <div class="flex justify-between items-center py-2">
                                    <button type="button" class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400">
                                        Editar descuento
                                    </button>
                                    <div class="text-sm text-zinc-500 dark:text-zinc-400">25%</div>
                                    <div class="text-sm text-red-600 dark:text-red-400">-25,00 L</div>
                                </div>

                                {{-- Agregar envío --}}
                                <div class="flex justify-between items-center py-2">
                                    <button type="button" class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400">
                                        Agregar envío o entrega
                                    </button>
                                    <div class="text-sm text-zinc-500 dark:text-zinc-400">—</div>
                                    <div class="text-sm text-zinc-900 dark:text-white">0,00 L</div>
                                </div>

                                {{-- Impuesto --}}
                                <div class="flex justify-between items-center py-2">
                                    <div class="flex items-center gap-1">
                                        <span class="text-sm text-zinc-600 dark:text-zinc-400">Impuesto estimado</span>
                                        <button type="button" class="text-zinc-400 hover:text-zinc-600">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="text-sm text-zinc-500 dark:text-zinc-400">VAT 12%</div>
                                    <div class="text-sm text-zinc-900 dark:text-white">
                                        {{ number_format($subtotal_price * 0.12, 2) }} L
                                    </div>
                                </div>

                                {{-- Total --}}
                                <div class="flex justify-between items-center py-3 border-t border-zinc-200 dark:border-zinc-700 mt-2">
                                    <div class="text-base font-semibold text-zinc-900 dark:text-white">Total</div>
                                    <div class="text-base font-semibold text-zinc-900 dark:text-white">
                                        {{ number_format($total_price, 2) }} L
                                    </div>
                                </div>

                            </div>
                                
                            <div class="p-4 border border-zinc-200 dark:border-zinc-700 rounded-md">
                                {{-- Checkbox Pago con vencimiento posterior --}}
                                <div class="pt-2">
                                    <flux:checkbox wire:model.live="showPaymentTerms" label="Pago con vencimiento posterior" />
                                </div>

                                @if($showPaymentTerms)
                                    {{-- Condiciones de pago --}}
                                    <div class="pt-2">
                                        <flux:label class="text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-2">Condiciones de pago</flux:label>
                                        <flux:select class="mt-1">
                                            <flux:select.option value="reception" selected>A pagar al momento de la recepción</flux:select.option>
                                        </flux:select>
                                    </div>

                                    {{-- Texto informativo --}}
                                    <div class="pt-2">
                                        <p class="text-xs text-zinc-600 dark:text-zinc-400">
                                            <span class="font-semibold">Pago con vencimiento cuando se envía la factura.</span> Podrás recaudar el saldo desde la página del pedido.
                                        </p>
                                    </div>

                                    {{-- Banner azul informativo --}}
                                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mt-4">
                                    <div class="flex gap-3">
                                        <div class="flex-shrink-0">
                                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <button type="button" class="text-blue-600 dark:text-blue-400 hover:text-blue-700 text-xs float-right">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                            <p class="text-sm text-blue-900 dark:text-blue-100">
                                                Los clientes pueden recibir recordatorios automáticos de sus pedidos cuando el pago vence en una fecha posterior
                                            </p>
                                            <button type="button" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 font-medium mt-2 inline-flex items-center gap-1">
                                                Configurar recordatorios de pago
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                                {{-- Botones de acción --}}
                                <div class="flex gap-3 pt-4">
                                    @if($showPaymentTerms)
                                        {{-- Si tiene pago con vencimiento posterior, solo crear orden --}}
                                        <flux:button type="button" variant="filled" size="sm" class="flex-1">
                                            Enviar factura
                                        </flux:button>
                                        <flux:button wire:click="store" type="button" size="sm" variant="primary" class="flex-1">
                                            Crear pedido
                                        </flux:button>
                                    @else
                                        {{-- Si no tiene pago con vencimiento, marcar como pagado --}}
                                        <flux:button type="button" variant="filled" size="sm" class="flex-1">
                                            Enviar factura
                                        </flux:button>
                                        <flux:button wire:click="markAsPaid" type="button" size="sm" variant="primary" class="flex-1">
                                            Marcar como pagado
                                        </flux:button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Columna lateral (derecha) --}}
                    <div class="space-y-4">
                        {{-- Notas --}}
                        <div class="bg-white dark:bg-white/5 rounded-lg shadow-sm">
                            <div class="p-4 flex items-center justify-between border-b border-zinc-200 dark:border-zinc-700">
                                <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">Notas</h3>
                                <button type="button" wire:click="openNoteModal" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                            </div>
                            <div class="p-4">
                                @if(count($notes) > 0)
                                    <div class="space-y-3">
                                        @foreach($notes as $note)
                                            <div class="bg-zinc-50 dark:bg-zinc-900 rounded-lg p-3 relative group">
                                                <button 
                                                    type="button" 
                                                    wire:click="removeNote('{{ $note['id'] }}')"
                                                    class="absolute top-2 right-2 text-zinc-400 hover:text-red-600 dark:hover:text-red-400 opacity-0 group-hover:opacity-100 transition-opacity"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                                <p class="text-sm text-zinc-700 dark:text-zinc-300 pr-6">{{ $note['text'] }}</p>
                                                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">{{ $note['created_at'] }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Sin notas</p>
                                @endif
                            </div>
                        </div>

                        {{-- Cliente --}}
                        <div class="bg-white dark:bg-white/5 rounded-lg shadow-sm">
                            <div class="p-4 flex items-center justify-between border-b border-zinc-200 dark:border-zinc-700">
                                <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">Cliente</h3>
                                <button type="button" class="text-zinc-400 hover:text-zinc-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <div class="p-4">
                                <div class="space-y-3">
                                    @if($id_customer)
                                        @php
                                            $selectedCustomer = $customers->firstWhere('id', $id_customer);
                                        @endphp
                                        @if($selectedCustomer)
                                            <div>
                                                <a href="#" class="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400">
                                                    {{ $selectedCustomer->name }}
                                                </a>
                                                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">Sin pedidos</p>
                                            </div>

                                            {{-- Información de contacto --}}
                                            <div class="border-t border-zinc-200 dark:border-zinc-700 pt-3">
                                                <div class="flex items-center justify-between mb-2">
                                                    <h4 class="text-xs font-semibold text-zinc-900 dark:text-white">Información de contacto</h4>
                                                    <button type="button" class="text-zinc-400 hover:text-zinc-600">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                        </svg>
                                                    </button>
                                                </div>
                                                <a href="mailto:{{ $selectedCustomer->email }}" class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 block">
                                                    {{ $selectedCustomer->email }}
                                                </a>
                                                @if($selectedCustomer->phone)
                                                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">{{ $selectedCustomer->phone }}</p>
                                                @endif
                                            </div>

                                            {{-- Dirección de envío --}}
                                            <div class="border-t border-zinc-200 dark:border-zinc-700 pt-3">
                                                <div class="flex items-center justify-between mb-2">
                                                    <h4 class="text-xs font-semibold text-zinc-900 dark:text-white">Dirección de envío</h4>
                                                    <button type="button" class="text-zinc-400 hover:text-zinc-600">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                        </svg>
                                                    </button>
                                                </div>
                                                <p class="text-sm text-zinc-500 dark:text-zinc-400">No se proporcionó dirección de envío</p>
                                            </div>

                                            {{-- Dirección de facturación --}}
                                            <div class="border-t border-zinc-200 dark:border-zinc-700 pt-3">
                                                <div class="flex items-center justify-between mb-2">
                                                    <h4 class="text-xs font-semibold text-zinc-900 dark:text-white">Dirección de facturación</h4>
                                                    <button type="button" class="text-zinc-400 hover:text-zinc-600">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                        </svg>
                                                    </button>
                                                </div>
                                                <p class="text-sm text-zinc-500 dark:text-zinc-400">Igual que la dirección de envío</p>
                                            </div>
                                        @endif
                                    @else
                                        <div class="space-y-2">
                                            <flux:input 
                                                wire:model.live.debounce.300ms="searchCustomer"
                                                icon="magnifying-glass"
                                                placeholder="Buscar cliente por nombre o email"
                                            />
                                            <flux:select 
                                                wire:model.live="id_customer"
                                                placeholder="Seleccionar cliente"
                                                required
                                            >
                                                @foreach($customers as $customer)
                                                    <flux:select.option value="{{ $customer->id }}">
                                                        {{ $customer->name }} @if($customer->email) - {{ $customer->email }} @endif
                                                    </flux:select.option>
                                                @endforeach
                                            </flux:select>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Mercados --}}
                        <div class="bg-white dark:bg-white/5 rounded-lg shadow-sm">
                            <div class="p-4 flex items-center justify-between border-b border-zinc-200 dark:border-zinc-700">
                                <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">Mercados</h3>
                            </div>
                            <div class="p-4 space-y-3">
                                {{-- Selector de Mercado --}}
                                <div>
                                    <flux:label class="text-xs mb-1">Mercado</flux:label>
                                    <flux:select wire:model="id_market" disabled>
                                        <flux:select.option value="">Seleccionar mercado</flux:select.option>
                                        @foreach($markets as $market)
                                            <flux:select.option value="{{ $market->id }}">
                                                {{ $market->name }}
                                            </flux:select.option>
                                        @endforeach
                                    </flux:select>
                                </div>
                                
                                {{-- Selector de Moneda --}}
                                <div>
                                    <flux:label class="text-xs mb-1">Moneda</flux:label>
                                    <flux:select wire:model="id_moneda">
                                        <flux:select.option value="">Seleccionar moneda</flux:select.option>
                                        @foreach($monedas as $moneda)
                                            <flux:select.option value="{{ $moneda->id }}">
                                                {{ $moneda->nombre }} ({{ $moneda->codigo }} {{ $moneda->simbolo }})
                                            </flux:select.option>
                                        @endforeach
                                    </flux:select>
                                </div>
                            </div>
                        </div>

                        {{-- Etiquetas --}}
                        <div class="bg-white dark:bg-white/5 rounded-lg shadow-sm">
                            <div class="p-4 flex items-center justify-between border-b border-zinc-200 dark:border-zinc-700">
                                <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">Etiquetas</h3>
                                <button type="button" class="text-zinc-400 hover:text-zinc-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                            </div>
                            <div class="p-4">
                                <flux:input 
                                    wire:model="id_etiqueta"
                                    placeholder=""
                                    class="text-sm"
                                />
                            </div>
                        </div>
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
                                    icon="magnifying-glass"
                                    placeholder="Buscar productos"
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
                        <button type="button" class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 font-medium">
                            + Agregar filtro
                        </button>
                    </div>

                    {{-- Tabla de productos --}}
                    <div class="flex-1 border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden">
                        <div class="h-full overflow-y-auto">
                            <table class="w-full">
                                <thead class="bg-zinc-50 dark:bg-zinc-900 sticky top-0">
                                    <tr>
                                        <th class="w-12 px-4 py-3"></th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-700 dark:text-zinc-300">Producto</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-700 dark:text-zinc-300">Disponible</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-zinc-700 dark:text-zinc-300">Precio</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-white/5 divide-y divide-zinc-200 dark:divide-zinc-700">
                                    @php
                                        $filteredProducts = $products;
                                        if ($searchProduct) {
                                            $filteredProducts = $products->filter(function($product) {
                                                return stripos($product->name, $this->searchProduct) !== false;
                                            });
                                        }
                                    @endphp

                                    @if($filteredProducts->count() > 0)
                                        @foreach($filteredProducts as $product)
                                            {{-- Producto principal --}}
                                            @if($product->variants && $product->variants->count() > 0)
                                                {{-- Producto con variantes - solo mostrar nombre --}}
                                                <tr class="bg-zinc-50 dark:bg-zinc-900/50">
                                                    <td class="px-4 py-3">
                                                        @php
                                                            $allVariantsSelected = true;
                                                            foreach($product->variants as $v) {
                                                                if (!isset($tempSelectedProducts['variant_' . $v->id])) {
                                                                    $allVariantsSelected = false;
                                                                    break;
                                                                }
                                                            }
                                                        @endphp
                                                        <input 
                                                            type="checkbox" 
                                                            wire:click="toggleAllVariants({{ $product->id }}, '{{ $product->name }}')"
                                                            @if($allVariantsSelected) checked @endif
                                                            class="rounded border-zinc-300 dark:border-zinc-600 text-blue-600 focus:ring-blue-500"
                                                        >
                                                    </td>
                                                    <td colspan="3" class="px-4 py-3">
                                                        <div class="flex items-center gap-3">
                                                            <div class="w-10 h-10 bg-zinc-900 dark:bg-zinc-700 rounded flex items-center justify-center flex-shrink-0">
                                                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                                                </svg>
                                                            </div>
                                                            <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                                                {{ $product->name }}
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                
                                                {{-- Variantes del producto --}}
                                                @foreach($product->variants as $variant)
                                                    @php
                                                        // Extraer solo el valor de la variante
                                                        $valores = $variant->valores_variante;
                                                        if (is_array($valores)) {
                                                            $variantDisplay = implode(' : ', array_values($valores));
                                                        } else {
                                                            $variantDisplay = $valores;
                                                        }
                                                    @endphp
                                                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                                        <td class="px-4 py-3">
                                                            <input 
                                                                type="checkbox" 
                                                                wire:click="toggleVariantSelection({{ $product->id }}, {{ $variant->id }}, '{{ $product->name }}', '{{ $variantDisplay }}', {{ $variant->price }}, {{ $variant->cantidad_inventario }})"
                                                                @if(isset($tempSelectedProducts['variant_' . $variant->id])) checked @endif
                                                                class="rounded border-zinc-300 dark:border-zinc-600 text-blue-600 focus:ring-blue-500"
                                                            >
                                                        </td>
                                                        <td class="px-4 py-3">
                                                            <div class="pl-10">
                                                                <div class="text-sm text-zinc-600 dark:text-zinc-400">
                                                                    {{ $variantDisplay }}
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="px-4 py-3">
                                                            <span class="text-sm text-zinc-900 dark:text-white">
                                                                {{ $variant->cantidad_inventario }}
                                                            </span>
                                                        </td>
                                                        <td class="px-4 py-3 text-right">
                                                            <span class="text-sm text-zinc-900 dark:text-white">
                                                                {{ number_format($variant->price, 2) }} L HNL
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                {{-- Producto sin variantes --}}
                                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                                    <td class="px-4 py-3">
                                                        <input 
                                                            type="checkbox" 
                                                            wire:click="toggleProductSelection({{ $product->id }}, '{{ $product->name }}', {{ $product->price_unitario ?? 0 }}, {{ $product->stock ?? 0 }})"
                                                            @if(isset($tempSelectedProducts['product_' . $product->id])) checked @endif
                                                            class="rounded border-zinc-300 dark:border-zinc-600 text-blue-600 focus:ring-blue-500"
                                                        >
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <div class="flex items-center gap-3">
                                                            <div class="w-10 h-10 bg-zinc-900 dark:bg-zinc-700 rounded flex items-center justify-center flex-shrink-0">
                                                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                                                </svg>
                                                            </div>
                                                            <div>
                                                                <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                                                    {{ $product->name }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <span class="text-sm text-zinc-900 dark:text-white">
                                                            {{ $product->stock ?? 0 }}
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-3 text-right">
                                                        <span class="text-sm text-zinc-900 dark:text-white">
                                                            {{ number_format($product->price_unitario ?? 0, 2) }} L HNL
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="4" class="px-4 py-12 text-center">
                                                <div class="text-zinc-500 dark:text-zinc-400">
                                                    <svg class="mx-auto h-12 w-12 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    <p class="mt-2 text-sm">No se encontraron productos</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex-shrink-0 px-6 py-4 border-t border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">
                            {{ count($tempSelectedProducts) }}/500 variantes seleccionadas
                        </span>
                        <div class="flex gap-3">
                            <button type="button" wire:click="closeProductModal" class="px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 rounded-lg transition-colors">
                                Cancelar
                            </button>
                            <button 
                                type="button" 
                                wire:click="addSelectedProducts" 
                                @if(count($tempSelectedProducts) === 0) disabled @endif
                                class="px-4 py-2 text-sm font-medium text-white bg-zinc-900 hover:bg-zinc-800 dark:bg-zinc-800 dark:hover:bg-zinc-700 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Agregar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </flux:modal>

        {{-- Modal para agregar notas --}}
        <flux:modal name="note-modal" class="min-w-[500px]" variant="flyout">
            <div>
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Agregar nota</h2>
                </div>
                
                <div class="px-6 py-4">
                    <flux:textarea
                        wire:model="noteText"
                        placeholder="Escribe una nota..."
                        rows="4"
                        class="w-full"
                    />
                </div>

                <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-700 flex justify-end gap-3">
                    <flux:button type="button" wire:click="closeNoteModal" variant="ghost">
                        Cancelar
                    </flux:button>
                    <flux:button 
                        type="button" 
                        wire:click="addNote"
                        flux:modal.close
                        :disabled="!$noteText || trim($noteText) === ''"
                        class="bg-zinc-900 hover:bg-zinc-800 text-white dark:bg-zinc-800 dark:hover:bg-zinc-700 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Agregar nota
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    </div>
                    