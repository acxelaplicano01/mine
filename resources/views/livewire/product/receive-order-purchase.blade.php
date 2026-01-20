<div class="min-h-screen">
    <div class="max-w-6xl mx-auto px-4 py-6">
        {{-- Header --}}
        <div class="mb-6">
            <div class="flex items-center gap-3 mb-2">
                <button type="button" wire:click="cancel" class="p-1.5 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <div>
                    <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">Recibir inventario</h1>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Orden #{{ $order->numero_referencia ?? $order->id }}</p>
                </div>
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Productos --}}
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-white/5 rounded-lg border border-zinc-200 dark:border-zinc-800">
                    <div class="p-6 border-b border-zinc-200 dark:border-zinc-800">
                        <h3 class="text-base font-semibold text-zinc-900 dark:text-white">Productos de la orden</h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                            Distribuidor: {{ $order->distribuidor->name ?? 'N/A' }}
                        </p>
                    </div>

                    <div class="p-6">
                        @if(count($productos) > 0)
                            <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden">
                                <table class="w-full">
                                    <thead class="bg-zinc-50 dark:bg-white/5">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Producto</th>
                                            <th class="px-4 py-3 text-center text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Cantidad pedida</th>
                                            <th class="px-4 py-3 text-center text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Cantidad recibida</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Costo unitario</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                                        @foreach($productos as $index => $producto)
                                            <tr>
                                                <td class="px-4 py-4">
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-10 h-10 bg-zinc-100 dark:bg-zinc-700 rounded flex items-center justify-center">
                                                            <svg class="w-6 h-6 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                                            </svg>
                                                        </div>
                                                        <div>
                                                            <div class="text-sm font-medium text-zinc-900 dark:text-white">{{ $producto['name'] }}</div>
                                                            @if($producto['sku_distribuidor'] ?? false)
                                                                <div class="text-xs text-zinc-500 dark:text-zinc-400">SKU: {{ $producto['sku_distribuidor'] }}</div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-4 text-center">
                                                    <span class="text-sm font-medium text-zinc-900 dark:text-white">{{ $producto['cantidad'] ?? 0 }}</span>
                                                </td>
                                                <td class="px-4 py-4">
                                                    <div class="flex justify-center">
                                                        <flux:input 
                                                            type="number" 
                                                            wire:model.live="productos.{{ $index }}.cantidad_recibida"
                                                            wire:change="updateCantidadRecibida({{ $index }}, $event.target.value)"
                                                            min="0" 
                                                            max="{{ $producto['cantidad'] ?? 0 }}"
                                                            class="w-20 text-center"
                                                        />
                                                    </div>
                                                </td>
                                                <td class="px-4 py-4 text-right">
                                                    <span class="text-sm text-zinc-900 dark:text-white">{{ number_format($producto['costo'] ?? 0, 2) }} L</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-12 text-zinc-500 dark:text-zinc-400">
                                <p>No hay productos en esta orden</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Resumen --}}
            <div>
                <div class="bg-white dark:bg-white/5 rounded-lg border border-zinc-200 dark:border-zinc-800 p-6">
                    <h3 class="text-base font-semibold text-zinc-900 dark:text-white mb-4">Resumen</h3>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-zinc-600 dark:text-zinc-400">Total de la orden</span>
                            <span class="font-medium text-zinc-900 dark:text-white">{{ number_format($total_orden, 2) }} L</span>
                        </div>
                        
                        <div class="flex justify-between text-sm">
                            <span class="text-zinc-600 dark:text-zinc-400">Total recibido</span>
                            <span class="font-medium text-zinc-900 dark:text-white">{{ number_format($total_recibido, 2) }} L</span>
                        </div>

                        <div class="pt-3 border-t border-zinc-200 dark:border-zinc-700 flex justify-between">
                            <span class="font-semibold text-zinc-900 dark:text-white">Estado</span>
                            <span class="text-sm px-2 py-1 rounded bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                {{ ucfirst($order->estado) }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <flux:button 
                            type="button"
                            wire:click="recibirInventario"
                            variant="primary"
                            class="w-full"
                        >
                            Confirmar recepción
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
        </div>
    </div>
</div>
