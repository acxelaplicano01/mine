<div class="min-h-screen">
    <div class="max-w-5xl mx-auto px-2 sm:px-6 lg:px-6 py-6">
        <form wire:submit.prevent="store">
            {{-- Header con breadcrumb --}}
            <div>
                <div class="px-2 sm:px-4 lg:px-6">
                    {{-- Mensajes de advertencia --}}
                    @if (session()->has('warning'))
                        <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg dark:bg-yellow-900/20 dark:border-yellow-800">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <p class="text-sm text-yellow-800 dark:text-yellow-200">{{ session('warning') }}</p>
                            </div>
                        </div>
                    @endif
                    
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400">
                            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">
                                {{ $product_id ? 'Editar Orden' : 'Crear Orden' }}
                            </h1>
                        </div>
                        <flux:button type="button" wire:click="saveDraft"  size="sm">
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
                                    <flux:input wire:click="openProductModal" icon="magnifying-glass"
                                        placeholder="Buscar productos..." class="flex-1 cursor-pointer" readonly />
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
                                                    <th
                                                        class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                                        Producto</th>
                                                    <th
                                                        class="px-4 py-3 text-center text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                                        Cantidad</th>
                                                    <th
                                                        class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                                        Total</th>
                                                    <th class="w-12"></th>
                                                </tr>
                                            </thead>
                                            <tbody
                                                class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                                                @foreach($selectedProducts as $index => $item)
                                                    <tr>
                                                        <td class="px-4 py-4">
                                                            <div class="flex items-center gap-3">
                                                                <div
                                                                    class="w-10 h-10 bg-zinc-100 dark:bg-zinc-700 rounded flex items-center justify-center flex-shrink-0">
                                                                    <svg class="w-6 h-6 text-zinc-400" fill="none"
                                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                                            stroke-width="2"
                                                                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                                                    </svg>
                                                                </div>
                                                                <div class="flex-1">
                                                                    <div class="flex items-center gap-2">
                                                                        <div
                                                                            class="text-sm font-medium text-zinc-900 dark:text-white">
                                                                            {{ $item['name'] }}
                                                                        </div>
                                                                        @php
                                                                            $itemDiscount = 0;
                                                                            $discountCodes = [];

                                                                            // Calcular descuentos automáticos
                                                                            if ($applyAutomaticDiscounts && count($appliedAutomaticDiscounts) > 0) {
                                                                                foreach ($appliedAutomaticDiscounts as $discount) {
                                                                                    $applies = true;

                                                                                    // Verificar si aplica a este producto
                                                                                    if (isset($discount['id_product']) && $discount['id_product']) {
                                                                                        $applies = $item['id'] == $discount['id_product'];
                                                                                    } elseif (isset($discount['id_collection']) && $discount['id_collection']) {
                                                                                        $product = \App\Models\Product\Products::find($item['id']);
                                                                                        $applies = $product && $product->id_collection == $discount['id_collection'];
                                                                                    }

                                                                                    if ($applies && !in_array($discount['id_type_discount'] ?? 0, [3, 4])) {
                                                                                        $itemSubtotal = $item['price'] * $item['quantity'];
                                                                                        if ($discount['discount_value_type'] === 'percentage') {
                                                                                            $itemDiscount += ($itemSubtotal * $discount['valor_discount']) / 100;
                                                                                        } else {
                                                                                            $itemDiscount += $discount['valor_discount'] / count($selectedProducts);
                                                                                        }
                                                                                        $discountCodes[] = $discount['code_discount'];
                                                                                    }
                                                                                }
                                                                            }

                                                                            // Calcular descuentos manuales
                                                                            if (count($selectedManualDiscounts) > 0) {
                                                                                foreach ($selectedManualDiscounts as $discount) {
                                                                                    $applies = true;

                                                                                    if (isset($discount['id_product']) && $discount['id_product']) {
                                                                                        $applies = $item['id'] == $discount['id_product'];
                                                                                    } elseif (isset($discount['id_collection']) && $discount['id_collection']) {
                                                                                        $product = \App\Models\Product\Products::find($item['id']);
                                                                                        $applies = $product && $product->id_collection == $discount['id_collection'];
                                                                                    }

                                                                                    if ($applies && !in_array($discount['id_type_discount'] ?? 0, [3, 4])) {
                                                                                        $itemSubtotal = $item['price'] * $item['quantity'];
                                                                                        if ($discount['discount_value_type'] === 'percentage') {
                                                                                            $itemDiscount += ($itemSubtotal * $discount['valor_discount']) / 100;
                                                                                        } else {
                                                                                            $itemDiscount += $discount['valor_discount'] / count($selectedProducts);
                                                                                        }
                                                                                        $discountCodes[] = $discount['code_discount'];
                                                                                    }
                                                                                }
                                                                            }
                                                                        @endphp

                                                                        @if($itemDiscount > 0)
                                                                            <span
                                                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-lime-100 text-lime-800 dark:bg-lime-900/30 dark:text-lime-300">
                                                                                <svg class="w-3 h-3" fill="none"
                                                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                                                    <path stroke-linecap="round"
                                                                                        stroke-linejoin="round" stroke-width="2"
                                                                                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                                                                </svg>
                                                                                Descuento
                                                                            </span>
                                                                        @endif
                                                                    </div>
                                                                    <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                                                                        Geométrico
                                                                    </div>
                                                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                                        {{ $item['sku'] ?? 'HYH-1' }}
                                                                    </div>

                                                                    <div class="mt-2">
                                                                        @if($itemDiscount > 0)
                                                                            <div
                                                                                class="text-sm font-medium text-lime-600 dark:text-lime-400">
                                                                                {{ number_format($item['price'] - ($itemDiscount / $item['quantity']), 2) }}
                                                                                L
                                                                            </div>
                                                                            <div
                                                                                class="flex items-center gap-1 text-xs text-zinc-500 dark:text-zinc-400">
                                                                                <svg class="w-3 h-3" fill="none"
                                                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                                                    <path stroke-linecap="round"
                                                                                        stroke-linejoin="round" stroke-width="2"
                                                                                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                                                                </svg>
                                                                                @foreach($discountCodes as $code)
                                                                                    {{ $code }}{{ !$loop->last ? ', ' : '' }}
                                                                                @endforeach
                                                                                (-{{ number_format($itemDiscount / $item['quantity'], 2) }}
                                                                                L HNL)
                                                                            </div>
                                                                        @else
                                                                            <div
                                                                                class="text-sm font-medium text-lime-600 dark:text-lime-400">
                                                                                {{ number_format($item['price'], 2) }} L
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="px-4 py-4">
                                                            <div class="flex justify-center">
                                                                <flux:input
                                                                    wire:change="updateQuantity({{ $index }}, $event.target.value)"
                                                                    type="number" min="1" value="{{ $item['quantity'] }}"
                                                                    class="w-20 text-center" />
                                                            </div>
                                                        </td>
                                                        <td class="px-4 py-4 text-right">
                                                            <div>
                                                                @if($itemDiscount > 0)
                                                                    <div
                                                                        class="text-xs text-zinc-400 dark:text-zinc-500 line-through mb-1">
                                                                        {{ number_format($item['price'] * $item['quantity'], 2) }} L
                                                                    </div>
                                                                @endif
                                                                <span class="text-sm font-medium text-zinc-900 dark:text-white">
                                                                    {{ number_format(($item['price'] * $item['quantity']) - $itemDiscount, 2) }}
                                                                    L
                                                                </span>
                                                            </div>
                                                        </td>
                                                        <td class="px-4 py-4 text-center">
                                                            <button type="button" wire:click="removeProduct({{ $index }})"
                                                                class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300">
                                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                                    viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12" />
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
                            <div class="p-4 space-y-4">
                            <div class="font-semibold text-zinc-900 dark:text-white">Pago</div>
                                <div class="p-4 border border-zinc-200 dark:border-zinc-700 rounded-md">
                                    <!-- Subtotal -->
                                    <div class="flex justify-between items-center py-2">
                                        <div class="text-sm text-zinc-600 dark:text-zinc-400">
                                            <span>Subtotal</span>
                                        </div>
                                        <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                            <span class="text-zinc-500 ml-1">{{ $quantity }} artículo{{ $quantity > 1 ? 's' : '' }}</span>
                                        </div>
                                        <div class="text-sm text-zinc-900 dark:text-white">
                                            {{ number_format($subtotal_price, 2) }} L
                                        </div>
                                    </div>
                                    <!-- Editar descuento -->
                                    <div class="flex justify-between items-center py-2">
                                        <button type="button" wire:click="openDiscountModal"
                                            class="text-sm text-lime-600 hover:text-lime-700 dark:text-lime-400">
                                            @if(count($selectedManualDiscounts) > 0 || $applyAutomaticDiscounts || $addCustomDiscount)
                                                Editar descuento
                                            @else
                                                Agregar descuento
                                            @endif
                                        </button>
                                        <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                            @if ($customDiscountReason != null && $customDiscountReason != '') 
                                                {{ $customDiscountReason }}
                                            @else
                                                  —
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <div class="text-sm text-zinc-600 dark:text-zinc-400">
                                                @if ($discountAmount == 0)
                                                   0.00 L
                                                @else
                                                    -{{ number_format($discountAmount, 2) }} L
                                                @endif
                                            </div>
                                            @if($discountAmount > 0)
                                                <button type="button" wire:click="removeAllDiscounts"
                                                    class="text-zinc-400 hover:text-red-600 dark:hover:text-red-400"
                                                    title="Eliminar todos los descuentos">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                    <!-- Editar envío o entrega -->
                                    <div class="flex justify-between items-center py-2 ">
                                        <button type="button"
                                            class="text-sm text-lime-600 hover:text-lime-700 dark:text-lime-400">
                                            Editar envío o entrega
                                        </button>
                                        <div class="text-sm text-zinc-600 dark:text-zinc-400">—</div>
                                        <div class="text-sm text-zinc-600 dark:text-zinc-400">0.00 L</div>
                                    </div>
                                    <!-- Impuesto estimado -->
                                    <div class="flex justify-between items-center py-2  relative">
                                        <div class="flex items-center gap-1">
                                            <button type="button" wire:click="toggleTaxDropdown"
                                                class="text-sm text-lime-600 hover:text-lime-700 dark:text-lime-400">
                                                Impuesto estimado
                                            </button>
                                            <button type="button"
                                                class="text-zinc-400 hover:text-zinc-600">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </button>
                                        </div>
                                        <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                            {{ $chargeTaxes ? 'VAT 12%' : 'No recaudado' }}
                                        </div>
                                        <div class="text-sm text-zinc-900 dark:text-white">
                                            @if($chargeTaxes)
                                                {{ number_format(($subtotal_price - $discountAmount) * 0.12, 2) }} L
                                            @else
                                                0,00 L
                                            @endif
                                        </div>
                                        @if($showTaxDropdown)
                                            <div class="absolute top-full left-0 mt-2 w-78 bg-white dark:bg-zinc-800 rounded-lg shadow-lg border border-zinc-200 dark:border-zinc-700 p-4 z-50"
                                                x-data @click.away="$wire.closeTaxDropdown()">
                                                <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-3">
                                                    Los impuestos se calculan automáticamente.
                                                </p>
                                                <div class="mb-4">
                                                    <flux:checkbox wire:model="chargeTaxes" label="Cobrar impuestos" />
                                                </div>
                                                <div class="flex justify-end gap-3">
                                                    <flux:button type="button" wire:click="closeTaxDropdown" variant="ghost"
                                                        size="sm">
                                                        Cancelar
                                                    </flux:button>
                                                    <flux:button type="button" wire:click="applyTaxSettings" size="sm">
                                                        Listo
                                                    </flux:button>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    <!-- Total -->
                                    <div class="flex justify-between items-center py-3">
                                        <div class="text-base font-semibold text-zinc-900 dark:text-white">Total</div>
                                        <div class="text-base font-semibold text-zinc-900 dark:text-white">
                                            {{ number_format($total_price, 2) }} L
                                        </div>
                                    </div>
                                </div>

                                <div class="p-4 border border-zinc-200 dark:border-zinc-700 rounded-md">
                                    {{-- Checkbox Pago con vencimiento posterior --}}
                                    <div class="pt-2">
                                        <flux:checkbox wire:model.live="showPaymentTerms"
                                            label="Pago con vencimiento posterior" />
                                    </div>

                                    @if($showPaymentTerms)
                                        {{-- Condiciones de pago y date pickers en la misma fila --}}
                                        <div class="pt-2 grid grid-cols-2 gap-3">
                                            {{-- Condiciones de pago --}}
                                            <div>
                                                <flux:label
                                                    class="text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                                    Condiciones de pago</flux:label>
                                                <flux:select wire:model.live="id_condiciones_pago" class="mt-1">
                                                    @foreach ($condicion_pago as $condicion)
                                                        <flux:select.option value="{{ $condicion->id }}">
                                                            {{ $condicion->nombre_condicion }}
                                                        </flux:select.option>
                                                    @endforeach
                                                </flux:select>
                                            </div>

                                            {{-- Date picker para fecha de vencimiento (solo si es fecha_fija) --}}
                                            @if($id_condiciones_pago && $selectedConditionType === 'fecha_fija')
                                                <div>
                                                    <flux:label
                                                        class="text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-2">Fecha
                                                        de vencimiento</flux:label>
                                                    <flux:input type="date" wire:model.live="fecha_vencimiento" class="mt-1" />
                                                </div>
                                            @endif

                                            {{-- Date picker para fecha de emisión (solo si es neto_dias) --}}
                                            @if($id_condiciones_pago && $selectedConditionType === 'neto_dias')
                                                <div>
                                                    <flux:label
                                                        class="text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-2">Fecha
                                                        de emisión</flux:label>
                                                    <flux:input type="date" wire:model.live="fecha_emision" class="mt-1" />
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Texto informativo con fecha de vencimiento --}}
                                        @if($id_condiciones_pago && $paymentDueDate)
                                            <div class="pt-2">
                                                <p class="text-xs text-zinc-600 dark:text-zinc-400">
                                                    @if($selectedConditionType === 'reception' || $selectedConditionType === 'envio')
                                                        <span class="font-semibold">{{ $paymentDueDate }}</span> Podrás recaudar el
                                                        saldo desde la página del pedido.
                                                    @else
                                                        <span class="font-semibold">Pago con vencimiento el {{ $paymentDueDate }}
                                                            ({{ $paymentTermName }}).</span> Podrás recaudar el saldo desde la
                                                        página del pedido.
                                                    @endif
                                                </p>
                                            </div>
                                        @endif

                                        {{-- Banner azul informativo
                                        <div
                                            class="bg-lime-50 dark:bg-lime-900/20 border border-lime-200 dark:border-lime-800 rounded-lg p-4 mt-4">
                                            <div class="flex gap-3">
                                                <div class="flex-shrink-0">
                                                    <svg class="w-5 h-5 text-lime-600 dark:text-lime-400" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                </div>
                                                <div class="flex-1">
                                                    <button type="button"
                                                        class="text-lime-600 dark:text-lime-400 hover:text-lime-700 text-xs float-right">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                    <p class="text-sm text-lime-900 dark:text-lime-100">
                                                        Los clientes pueden recibir recordatorios automáticos de sus pedidos
                                                        cuando el pago vence en una fecha posterior
                                                    </p>
                                                    <button type="button"
                                                        class="text-sm text-lime-600 dark:text-lime-400 hover:text-lime-700 font-medium mt-2 inline-flex items-center gap-1">
                                                        Configurar recordatorios de pago
                                                    </button>
                                                </div>
                                            </div>
                                        </div>--}}
                                    @endif
                                </div>
                                {{-- Botones de acción --}}
                                <div class="flex gap-3 pt-4 w-70">
                                    {{-- Botón Enviar factura y Crear pedido o Marcar como pagado --}}
                                    @if($showPaymentTerms)
                                        {{-- Si tiene pago con vencimiento posterior, solo crear orden --}}
                                        <flux:button type="button" size="sm" class="flex-1">
                                            Enviar factura
                                        </flux:button>
                                        <flux:button wire:click="store" type="button" size="sm" variant="primary"
                                            class="flex-1">
                                            Crear pedido
                                        </flux:button>
                                    @else
                                        {{-- Si no tiene pago con vencimiento, marcar como pagado --}}
                                        <flux:button type="button" size="sm" class="flex-1">
                                            Enviar factura
                                        </flux:button>
                                        <flux:button wire:click="markAsPaid" type="button" size="sm" variant="primary"
                                            class="flex-1">
                                            Marcar como pagado
                                        </flux:button>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Notas de seguimiento 
                        @if($order_id)
                        <div class="bg-white dark:bg-white/5 rounded-lg shadow-sm">
                            <div
                                class="p-4 flex items-center justify-between border-b border-zinc-200 dark:border-zinc-700">
                                <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">Notas</h3>
                                <button type="button" wire:click="openNoteModal"
                                    class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                            </div>
                            <div class="p-4">
                                @if(count($notes) > 0)
                                    <div class="space-y-3">
                                        @foreach($notes as $note)
                                            <div class="bg-zinc-50 dark:bg-zinc-900 rounded-lg p-3 relative group" x-data="{ expanded: false }">
                                                <button type="button" wire:click="removeNote('{{ $note['id'] }}')"
                                                    class="absolute top-2 right-2 text-zinc-400 hover:text-red-600 dark:hover:text-red-400 opacity-0 group-hover:opacity-100 transition-opacity z-10">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                                @php
                                                    $isLongNote = strlen($note['text']) > 200;
                                                @endphp
                                                <p class="text-sm text-zinc-700 dark:text-zinc-300 pr-6" 
                                                   :class="{ 'line-clamp-9': !expanded && {{ $isLongNote ? 'true' : 'false' }} }">
                                                    {{ $note['text'] }}
                                                </p>
                                                @if($isLongNote)
                                                    <button type="button" 
                                                            @click="expanded = !expanded"
                                                            class="flex items-center gap-1 text-sm text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-200 mt-2">
                                                        <span x-text="expanded ? 'Mostrar menos' : 'Mostrar más'">Mostrar más</span>
                                                        <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': expanded }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                        </svg>
                                                    </button>
                                                @endif
                                                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                                                    {{ $note['created_at'] }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Sin notas</p>
                                @endif
                            </div>
                        </div>
                        @endif --}}
                    </div>

                    {{-- Columna lateral (derecha) --}}
                    <div class="space-y-4">
                        {{-- Nota principal de la orden --}}
                        <div class="bg-white dark:bg-white/5 rounded-lg shadow-sm">
                            <div class="p-4 border-b border-zinc-200 dark:border-zinc-700">
                                <h3 class="text-base font-semibold text-zinc-900 dark:text-white">Nota principal</h3>
                            </div>
                            <div class="p-4">
                                <flux:textarea
                                    wire:model="note"
                                    placeholder="Agrega una nota a la orden (opcional)"
                                    rows="2"
                                    class="w-full text-sm"
                                />
                            </div>
                        </div>

                        
                        {{-- Cliente --}}
                        <div class="bg-white dark:bg-white/5 rounded-lg shadow-sm">
                            <div
                                class="p-4 flex items-center justify-between border-b border-zinc-200 dark:border-zinc-700">
                                <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">Cliente</h3>
                                <button type="button" wire:click="removeCustomer" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
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
                                                <a href="#"
                                                    class="text-sm font-medium text-lime-600 hover:text-lime-700 dark:text-lime-400">
                                                    {{ $selectedCustomer->name }}
                                                </a>
                                                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">Sin pedidos</p>
                                            </div>

                                            {{-- Información de contacto --}}
                                            <div class="border-t border-zinc-200 dark:border-zinc-700 pt-3">
                                                <div class="flex items-center justify-between mb-2">
                                                    <h4 class="text-xs font-semibold text-zinc-900 dark:text-white">Información
                                                        de contacto</h4>
                                                    <button type="button" class="text-zinc-400 hover:text-zinc-600">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                        </svg>
                                                    </button>
                                                </div>
                                                <a href="mailto:{{ $selectedCustomer->email }}"
                                                    class="text-sm text-lime-600 hover:text-lime-700 dark:text-lime-400 block">
                                                    {{ $selectedCustomer->email }}
                                                </a>
                                                @if($selectedCustomer->phone)
                                                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">
                                                        {{ $selectedCustomer->phone }}</p>
                                                @endif
                                            </div>

                                            {{-- Dirección de envío --}}
                                            <div class="border-t border-zinc-200 dark:border-zinc-700 pt-3">
                                                <div class="flex items-center justify-between mb-2">
                                                    <h4 class="text-xs font-semibold text-zinc-900 dark:text-white">Dirección de
                                                        envío</h4>
                                                    <button type="button" class="text-zinc-400 hover:text-zinc-600">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                        </svg>
                                                    </button>
                                                </div>
                                                <p class="text-sm text-zinc-500 dark:text-zinc-400">No se proporcionó dirección
                                                    de envío</p>
                                            </div>

                                            {{-- Dirección de facturación --}}
                                            <div class="border-t border-zinc-200 dark:border-zinc-700 pt-3">
                                                <div class="flex items-center justify-between mb-2">
                                                    <h4 class="text-xs font-semibold text-zinc-900 dark:text-white">Dirección de
                                                        facturación</h4>
                                                    <button type="button" class="text-zinc-400 hover:text-zinc-600">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                        </svg>
                                                    </button>
                                                </div>
                                                <p class="text-sm text-zinc-500 dark:text-zinc-400">Igual que la dirección de
                                                    envío</p>
                                            </div>
                                        @endif
                                    @else
                                        <div class="space-y-2">
                                            <flux:input wire:model.live.debounce.300ms="searchCustomer"
                                                icon="magnifying-glass" placeholder="Buscar cliente por nombre o email" />
                                            <flux:select wire:model.live="id_customer" placeholder="Seleccionar cliente"
                                                required>
                                                @foreach($customers as $customer)
                                                    <flux:select.option value="{{ $customer->id }}">
                                                        {{ $customer->name }} @if($customer->email) - {{ $customer->email }}
                                                        @endif
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
                            <div
                                class="p-4 flex items-center justify-between border-b border-zinc-200 dark:border-zinc-700">
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
                            <div
                                class="p-4 flex items-center justify-between border-b border-zinc-200 dark:border-zinc-700">
                                <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">Etiquetas</h3>
                                <button type="button" class="text-zinc-400 hover:text-zinc-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                            </div>
                            <div class="p-4">
                                <flux:input wire:model="id_etiqueta" placeholder="" class="text-sm" />
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
                                <flux:input wire:model.live.debounce.300ms="searchProduct" autofocus icon="magnifying-glass"
                                    placeholder="Buscar productos" class="w-full" />
                            </div>
                            <div class="w-48">
                                <flux:select wire:model="searchFilter">
                                    <flux:select.option value="todo">Buscar por Todo</flux:select.option>
                                    <flux:select.option value="nombre">Por Nombre</flux:select.option>
                                    <flux:select.option value="sku">Por SKU</flux:select.option>
                                </flux:select>
                            </div>
                        </div>
                        <button type="button"
                            class="text-sm text-lime-600 hover:text-lime-700 dark:text-lime-400 font-medium">
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
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-zinc-700 dark:text-zinc-300">
                                            Producto</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-zinc-700 dark:text-zinc-300">
                                            Disponible</th>
                                        <th
                                            class="px-4 py-3 text-right text-xs font-medium text-zinc-700 dark:text-zinc-300">
                                            Precio</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-white/5 divide-y divide-zinc-200 dark:divide-zinc-700">
                                    @php
                                        $filteredProducts = $products;
                                        if ($searchProduct) {
                                            $filteredProducts = $products->filter(function ($product) {
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
                                                            <div
                                                                class="w-10 h-10 bg-zinc-900 dark:bg-zinc-700 rounded flex items-center justify-center flex-shrink-0">
                                                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor"
                                                                    viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
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
                                                            <div
                                                                class="w-10 h-10 bg-zinc-900 dark:bg-zinc-700 rounded flex items-center justify-center flex-shrink-0">
                                                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor"
                                                                    viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
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
                                                    <svg class="mx-auto h-12 w-12 text-zinc-400" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
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
                <div
                    class="flex-shrink-0 px-6 py-4 border-t border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">
                            {{ count($tempSelectedProducts) }}/500 variantes seleccionadas
                        </span>
                        <div class="flex gap-3">
                            <button type="button" wire:click="closeProductModal"
                                class="px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 rounded-lg transition-colors">
                                Cancelar
                            </button>
                            <button type="button" wire:click="addSelectedProducts" @if(count($tempSelectedProducts) === 0)
                            disabled @endif
                                class="px-4 py-2 text-sm font-medium text-white bg-zinc-900 hover:bg-zinc-800 dark:bg-zinc-800 dark:hover:bg-zinc-700 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                Agregar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </flux:modal>

        {{-- Modal para agregar notas --}}
        <flux:modal name="note-modal" wire:model="showNoteModal" class="min-w-[500px]">
            <div>
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Agregar nota</h2>
                </div>

                <div class="px-6 py-4">
                    <flux:textarea wire:model.live="noteText" placeholder="Escribe una nota..." rows="4" class="w-full" />
                    <flux:text variant="subtle">{{strlen($noteText)}}/5000</flux:text>
                </div>
                @if (strlen($noteText) > 5000)
                    <flux:text variant="subtle" color="red">La nota no puede exceder los 5000 caracteres.</flux:text>
                @endif
                <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-700 flex justify-end gap-3">
                    <flux:button type="button" wire:click="closeNoteModal" variant="ghost">
                        Cancelar
                    </flux:button>
                    <button type="button" wire:click="addNote"
                        @if($noteText === '' || strlen(trim($noteText)) === 0 || strlen($noteText) > 5000)
                             disabled 
                        @endif
                        class="px-4 py-2 text-sm font-medium text-white bg-zinc-900 hover:bg-zinc-800 dark:bg-zinc-800 dark:hover:bg-zinc-700 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        Agregar nota
                    </button>
                    
                </div>
            </div>
        </flux:modal>

        {{-- Modal para agregar/editar descuento --}}
        <flux:modal name="discount-modal" class="min-w-[600px]" wire:model="showDiscountModal">
            <div>
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                        @if(count($selectedManualDiscounts) > 0 || $applyAutomaticDiscounts || $addCustomDiscount)
                            Editar descuento
                        @else
                            Agregar descuento
                        @endif
                    </h2>
                </div>

                <div class="px-6 py-4">
                    @if($hasDiscounts)
                            {{-- Mensaje de error si hay --}}
                            @if (session()->has('error'))
                                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg dark:bg-red-900/20 dark:border-red-800">
                                    <div class="flex items-start gap-3">
                                        <svg class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <p class="text-sm text-red-800 dark:text-red-200">{{ session('error') }}</p>
                                    </div>
                                </div>
                            @endif
                            
                            {{-- Select de códigos de descuento --}}
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Selecciona un
                                    descuento</label>
                                <flux:select wire:model.live="selectedDiscountCode"
                                    wire:change="selectDiscountCode($event.target.value)">
                                    <option value="">-- Selecciona un descuento --</option>
                                    @foreach($availableDiscounts as $discount)
                                        <option value="{{ $discount->code_discount }}">
                                            {{ $discount->code_discount }} -
                                            {{ $discount->valor_discount }}{{ $discount->discount_value_type === 'percentage' ? '%' : 'L' }}
                                            descuento
                                        </option>
                                    @endforeach
                                </flux:select>
                            </div>

                            {{-- Lista de descuentos manuales seleccionados --}}
                            @if(count($selectedManualDiscounts) > 0)
                                <div class="mb-4">
                                    <div class="flex items-center gap-2 mb-3">
                                        <span class="text-sm font-medium text-zinc-900 dark:text-white">Descuentos
                                            seleccionados:</span>
                                    </div>
                                    <div class="max-h-48 overflow-y-auto space-y-2">
                                        @foreach($selectedManualDiscounts as $discount)
                                            <div
                                                class="relative flex items-center justify-between p-3 border-2 border-blue-500 dark:border-blue-400 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                                <div class="flex items-center gap-3 flex-1">
                                                    <div class="flex-shrink-0">
                                                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                                        </svg>
                                                    </div>
                                                    <div class="flex-1">
                                                        <div class="flex items-center gap-2">
                                                            <span
                                                                class="text-sm font-medium text-zinc-900 dark:text-white">{{ $discount['code_discount'] }}</span>
                                                        </div>
                                                        <div class="text-xs text-zinc-600 dark:text-zinc-400 mt-1">
                                                            {{ $discount['valor_discount'] }}{{ $discount['discount_value_type'] === 'percentage' ? '%' : 'L' }}
                                                            de descuento
                                                            @if(isset($discount['description']))
                                                                en {{ $discount['description'] }}
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                <button type="button"
                                                    wire:click="removeSelectedDiscountCode('{{ $discount['code_discount'] }}')"
                                                    class="text-zinc-400 hover:text-red-600 dark:hover:text-red-400">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            {{-- Checkbox para aplicar descuentos automáticos --}}
                            <div class="mt-4">
                                <flux:checkbox wire:model.live="applyAutomaticDiscounts"
                                    label="Aplicar todos los descuentos automáticos que cumplan los requisitos" />
                            </div>
                            {{-- Mostrar descuentos automáticos aplicados --}}
                            @if($applyAutomaticDiscounts && count($appliedAutomaticDiscounts) > 0)
                                <div class="mb-4">
                                    <div class="flex items-center gap-2 mb-3">
                                        <svg class="w-5 h-5 text-lime-600 dark:text-lime-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span class="text-sm font-medium text-lime-900 dark:text-lime-100">Descuentos automáticos
                                            aplicados:</span>
                                    </div>
                                    <div class="max-h-48 overflow-y-auto space-y-2">
                                        @foreach($appliedAutomaticDiscounts as $discount)
                                            <div
                                                class="relative flex items-center justify-between p-3 border-2 border-lime-500 dark:border-lime-400 bg-lime-50 dark:bg-lime-900/20 rounded-lg">
                                                <div class="flex items-center gap-3 flex-1">
                                                    <div class="flex-shrink-0">
                                                        <svg class="w-5 h-5 text-lime-600 dark:text-lime-400" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                                        </svg>
                                                    </div>
                                                    <div class="flex-1">
                                                        <div class="flex items-center gap-2">
                                                            <span
                                                                class="text-sm font-medium text-zinc-900 dark:text-white">{{ $discount['code_discount'] }}</span>
                                                            <span class="text-xs text-lime-700 dark:text-lime-300">
                                                                <svg class="w-4 h-4 inline" fill="none" stroke="currentColor"
                                                                    viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2" d="M5 13l4 4L19 7" />
                                                                </svg>
                                                                Automático
                                                            </span>
                                                        </div>
                                                        <div class="text-xs text-zinc-600 dark:text-zinc-400 mt-1">
                                                            {{ $discount['valor_discount'] }}{{ $discount['discount_value_type'] === 'percentage' ? '%' : 'L' }}
                                                            de descuento
                                                            @if(isset($discount['description']))
                                                                en {{ $discount['description'] }}
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                    {{-- Mensaje cuando no hay descuentos --}}
                    <div class="mb-4">
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-3">No hay descuentos configurados en tu
                            tienda.</p>
                        <flux:button type="button" variant="ghost" size="sm" href="{{ route('discounts_create') }}">
                            Crear descuento
                        </flux:button>
                    </div>
                @endif

                {{-- Checkbox para descuento personalizado --}}
                <div class="mb-4 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    <flux:checkbox wire:model.live="addCustomDiscount"
                        label="Agregar descuento de pedido personalizado" />
                </div>

                {{-- Campos de descuento personalizado --}}
                @if($addCustomDiscount)
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Tipo de
                                    descuento</label>
                                <flux:select wire:model.live="customDiscountType">
                                    <flux:select.option value="monto">Monto</flux:select.option>
                                    <flux:select.option value="porcentaje">Porcentaje</flux:select.option>
                                </flux:select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Valor</label>
                                <div class="relative">
                                    <flux:input wire:model.live="customDiscountValue" type="number" step="0.01" min="0"
                                        placeholder="000" class="w-full pr-12" />
                                    <span
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ $customDiscountType === 'porcentaje' ? '%' : 'HNL' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Motivo del
                                descuento</label>
                            <flux:input wire:model="customDiscountReason" placeholder="" class="w-full" />
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">Visible para el cliente</p>
                        </div>
                    </div>
                @endif
            </div>

            <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-700 flex justify-end gap-3">
                <flux:button type="button" wire:click="cancelDiscountModal" variant="ghost">
                    Cancelar
                </flux:button>
                <flux:button type="button" wire:click="applyDiscount">
                    Listo
                </flux:button>
            </div>
    </div>
    </flux:modal>
</div>