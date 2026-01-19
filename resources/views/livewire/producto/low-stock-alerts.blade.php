<div class="bg-white dark:bg-white/5 rounded-lg shadow-sm border border-zinc-300 dark:border-zinc-600 ">
    {{-- Header --}}
    <div class="px-4 py-3 border-b border-zinc-300 dark:border-zinc-600  flex items-center justify-between">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">
                Alertas de Stock Bajo
            </h3>
            @if($totalCount > 0)
                <flux:badge color="yellow" size="sm">{{ $totalCount }}</flux:badge>
            @endif
        </div>
        
        <a href="{{ route('inventories', ['statusFilter' => 'low-stock']) }}" class="text-xs text-blue-600 hover:text-blue-700">
            Ver todo →
        </a>
    </div>

    {{-- Content --}}
    <div class="p-4">
        @if($lowStockProducts->count() > 0)
            <div class="space-y-3">
                @foreach($lowStockProducts as $product)
                    <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg border border-yellow-200 dark:bg-yellow-900/10 dark:border-yellow-800">
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            @if($product->image)
                                <img src="{{ $product->image }}" alt="{{ $product->title }}" class="w-10 h-10 rounded object-cover flex-shrink-0">
                            @else
                                <div class="w-10 h-10 rounded bg-zinc-200 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                </div>
                            @endif
                            
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-zinc-900 dark:text-white truncate">
                                    {{ $product->name }}
                                </div>
                                <div class="flex items-center gap-2 text-xs text-zinc-600 dark:text-zinc-400 mt-1">
                                    <span>SKU: {{ $product->inventory?->sku ?? 'N/A' }}</span>
                                    <span class="text-zinc-400">•</span>
                                    <span class="font-semibold {{ $product->inventory?->cantidad_inventario <= 0 ? 'text-red-600' : 'text-yellow-600' }}">
                                        Stock: {{ $product->inventory?->cantidad_inventario ?? 0 }} / {{ $product->inventory?->umbral_aviso_inventario ?? 0 }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex gap-1 ml-3">
                            <flux:button 
                                href="{{ route('inventories') }}?search={{ urlencode($product->name) }}" 
                                size="xs" 
                                variant="primary"
                            >
                                Gestionar
                            </flux:button>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($totalCount > $limit && !$showAll)
                <div class="mt-3 text-center">
                    <flux:button 
                        wire:click="toggleShowAll" 
                        size="sm" 
                        variant="ghost"
                        class="w-full"
                    >
                        Ver {{ $totalCount - $limit }} más
                    </flux:button>
                </div>
            @endif

            @if($showAll && $totalCount > $limit)
                <div class="mt-3 text-center">
                    <flux:button 
                        wire:click="toggleShowAll" 
                        size="sm" 
                        variant="ghost"
                        class="w-full"
                    >
                        Mostrar menos
                    </flux:button>
                </div>
            @endif
        @else
            <div class="text-center py-8">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-green-100 mb-3">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <p class="text-sm text-zinc-600 dark:text-white font-medium">¡Todo en orden!</p>
                <p class="text-xs text-zinc-500 dark:text-white mt-1">No hay productos con stock bajo</p>
            </div>
        @endif
    </div>

    {{-- Footer --}}
    <div class="px-4 py-3 bg-white dark:bg-zinc-800 border-t border-zinc-300 dark:border-zinc-600 0 rounded-b-lg">
        <a 
            href="{{ route('inventories') }}" 
            class="text-xs text-zinc-500 dark:text-white dark:hover:text-zinc-400 hover:text-zinc-900 flex items-center justify-center gap-1"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
            Ir a Gestión de Inventario
        </a>
    </div>
</div>
