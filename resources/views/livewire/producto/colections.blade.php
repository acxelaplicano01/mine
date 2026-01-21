<div class="min-h-screen">
    {{-- Mensaje de éxito --}}
    @if (session()->has('message'))
        <div class="px-4 sm:px-6 lg:px-8 py-4">
            <flux:callout dismissible variant="success" icon="check-circle" heading="{{ session('message') }}" />
        </div>
    @endif

    {{-- Mensaje de error --}}
    @if (session()->has('error'))
        <div class="px-4 sm:px-6 lg:px-8 py-4">
            <flux:callout dismissible variant="danger" icon="exclamation-circle" heading="{{ session('error') }}" />
        </div>
    @endif

    {{-- Header principal --}}
    <div>
        <div class="px-2 sm:px-4 lg:px-2">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Colecciones</h1>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">Organiza tus productos en colecciones</p>
                </div>
                <div class="flex items-center gap-3">
                    <flux:button href="{{ route('create-collection') }}" icon="plus" variant="primary" size="sm">
                        Crear colección
                    </flux:button>
                </div>
            </div>
        </div>
    </div>

    {{-- Contenido principal --}}
    <div class="px-2">
        {{-- Tabla de colecciones --}}
        @php
            $columns = [
                ['key' => 'select', 'label' => '', 'sortable' => false],
                ['key' => 'id', 'label' => 'ID', 'sortable' => true],
                ['key' => 'imagen', 'label' => 'Imagen', 'sortable' => false],
                ['key' => 'nombre', 'label' => 'Nombre', 'sortable' => true],
                ['key' => 'descripcion', 'label' => 'Descripción', 'sortable' => false],
                ['key' => 'tipo', 'label' => 'Tipo', 'sortable' => true],
                ['key' => 'estado', 'label' => 'Estado', 'sortable' => true],
                ['key' => 'productos', 'label' => 'Productos', 'sortable' => false],
                ['key' => 'canales', 'label' => 'Canales de venta', 'sortable' => false],
            ];
        @endphp

        <x-saved-views-table 
            view-name="colecciones" 
            search-placeholder="Buscar colecciones"
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
                    wire:click="setFilter('todos')"
                    class="px-3 py-1.5 text-sm font-medium rounded transition-colors whitespace-nowrap {{ $activeFilter === 'todos' ? 'bg-zinc-900 text-white dark:bg-zinc-700' : 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700' }}"
                >
                    Todas
                </button>
                <button 
                    wire:click="setFilter('activo')"
                    class="px-3 py-1.5 text-sm font-medium rounded transition-colors whitespace-nowrap {{ $activeFilter === 'activo' ? 'bg-zinc-900 text-white dark:bg-zinc-700' : 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700' }}"
                >
                    Activas
                </button>
                <button 
                    wire:click="setFilter('inactivo')"
                    class="px-3 py-1.5 text-sm font-medium rounded transition-colors whitespace-nowrap {{ $activeFilter === 'inactivo' ? 'bg-zinc-900 text-white dark:bg-zinc-700' : 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700' }}"
                >
                    Inactivas
                </button>
            </x-slot>
            
            {{-- Dropdown de filtros (modo búsqueda) --}}
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
                            <flux:input 
                                wire:model.live.debounce.300ms="filterSearch"
                                placeholder="Buscar..."
                                icon="magnifying-glass"
                                class="mb-2"
                            />
                            
                            <div class="max-h-96 overflow-y-auto">
                                {{-- Estado de la colección --}}
                                <div class="mb-2">
                                    <flux:separator text="Estado" />
                                    <flux:menu.item wire:click="addFilter('activo', 'estado', 'Estado: Activo')">
                                        Activo
                                    </flux:menu.item>
                                    <flux:menu.item wire:click="addFilter('inactivo', 'estado', 'Estado: Inactivo')">
                                        Inactivo
                                    </flux:menu.item>
                                </div>
                                
                                {{-- Tipo de colección --}}
                                <div class="mb-2">
                                    <flux:separator text="Tipo de colección" />
                                    <flux:menu.item wire:click="addFilter('manual', 'tipo', 'Tipo: Manual')">
                                        Manual
                                    </flux:menu.item>
                                    <flux:menu.item wire:click="addFilter('inteligente', 'tipo', 'Tipo: Inteligente')">
                                        Inteligente
                                    </flux:menu.item>
                                </div>
                            </div>
                        </div>
                    </flux:menu>
                </flux:dropdown>
            </x-slot>

            {{-- Acciones masivas para colecciones --}}
            <x-slot name="bulkActions">
                <flux:dropdown>
                    <flux:button icon:trailing="chevron-down" size="xs">
                        Marcar como
                    </flux:button>
                    <flux:menu class="min-w-40">
                        <flux:menu.item wire:click="changeStatus(1)">
                            Activa
                        </flux:menu.item>
                        <flux:menu.item wire:click="changeStatus(0)">
                            Inactiva
                        </flux:menu.item>
                    </flux:menu>
                </flux:dropdown>
                
                <flux:button wire:click="confirmDelete" size="xs" variant="danger">
                    Eliminar
                </flux:button>
            </x-slot>

            {{-- Contenido de la tabla --}}
            <x-slot name="desktop">
                @forelse($collections as $collection)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50 {{ in_array($collection->id, $selected) ? 'bg-lime-50 dark:bg-lime-900/20' : '' }}">
                        <td class="px-4 py-3">
                            <flux:checkbox wire:model.live.debounce.150ms="selected" value="{{ $collection->id }}" />
                        </td>
                        <td class="px-4 py-3">
                            <a href="#" class="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400">
                                #{{ str_pad($collection->id, 4, '0', STR_PAD_LEFT) }}
                            </a>
                        </td>
                        <td class="px-4 py-3">
                            <div class="w-10 h-10 rounded bg-zinc-100 dark:bg-zinc-700 flex items-center justify-center overflow-hidden">
                                @if($collection->image_url)
                                    <img src="{{ $collection->image_url }}" alt="{{ $collection->name }}" class="w-full h-full object-cover">
                                @else
                                    <svg class="w-5 h-5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-zinc-900 dark:text-white font-medium">
                                {{ $collection->name }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            @if($collection->description)
                                <div class="text-xs text-zinc-500 dark:text-zinc-400 line-clamp-2 max-w-xs">
                                    {{ $collection->description }}
                                </div>
                            @else
                                <span class="text-xs text-zinc-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($collection->id_tipo_collection == 1)
                                <span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 dark:bg-blue-900/30 rounded text-xs text-blue-700 dark:text-blue-400">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Manual
                                </span>
                            @elseif($collection->id_tipo_collection == 2)
                                <span class="inline-flex items-center gap-1 px-2 py-1 bg-purple-100 dark:bg-purple-900/30 rounded text-xs text-purple-700 dark:text-purple-400">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                    Inteligente
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($collection->id_status_collection == 1)
                                <span class="inline-flex items-center gap-1 px-2 py-1 bg-green-100 dark:bg-green-900/30 rounded text-xs text-green-700 dark:text-green-400">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <circle cx="10" cy="10" r="4" />
                                    </svg>
                                    Activa
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-1 bg-zinc-100 dark:bg-zinc-700 rounded text-xs text-zinc-600 dark:text-zinc-400">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <circle cx="10" cy="10" r="4" />
                                    </svg>
                                    Inactiva
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-sm text-zinc-600 dark:text-zinc-400">
                                {{ $collection->collection_products_count ?? 0 }} {{ $collection->collection_products_count == 1 ? 'producto' : 'productos' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex gap-1">
                                @if($collection->id_publicacion)
                                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-zinc-100 dark:bg-zinc-700 rounded text-xs text-zinc-600 dark:text-zinc-400">
                                        Tienda online
                                    </span>
                                @else
                                    <span class="text-xs text-zinc-400">—</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-zinc-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                                <p class="text-zinc-500 dark:text-zinc-400">No se encontraron colecciones</p>
                                <p class="text-sm text-zinc-400 dark:text-zinc-500 mt-1">Crea tu primera colección para organizar tus productos</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </x-slot>

            {{-- Vista móvil --}}
            <x-slot name="mobile">
                @forelse($collections as $collection)
                    <div class="p-4 border-b border-zinc-200 dark:border-zinc-700 {{ in_array($collection->id, $selected) ? 'bg-lime-50 dark:bg-lime-900/20' : '' }}">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 pt-1">
                                <flux:checkbox wire:model.live.debounce.150ms="selected" value="{{ $collection->id }}" />
                            </div>
                            
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 rounded bg-zinc-100 dark:bg-zinc-700 flex items-center justify-center overflow-hidden">
                                    @if($collection->image_url)
                                        <img src="{{ $collection->image_url }}" alt="{{ $collection->name }}" class="w-full h-full object-cover">
                                    @else
                                        <svg class="w-6 h-6 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                        </svg>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="flex-1 min-w-0">
                                <a href="#" class="text-sm font-medium text-zinc-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400">
                                    {{ $collection->name }}
                                </a>
                                
                                @if($collection->description)
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 line-clamp-1">
                                        {{ $collection->description }}
                                    </p>
                                @endif
                                
                                <div class="flex items-center gap-2 mt-2 flex-wrap">
                                    @if($collection->id_tipo_collection == 1)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-blue-100 dark:bg-blue-900/30 rounded text-xs text-blue-700 dark:text-blue-400">
                                            Manual
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-purple-100 dark:bg-purple-900/30 rounded text-xs text-purple-700 dark:text-purple-400">
                                            Inteligente
                                        </span>
                                    @endif
                                    
                                    @if($collection->id_status_collection == 1)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-green-100 dark:bg-green-900/30 rounded text-xs text-green-700 dark:text-green-400">
                                            Activa
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-zinc-100 dark:bg-zinc-700 rounded text-xs text-zinc-600 dark:text-zinc-400">
                                            Inactiva
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center">
                        <svg class="w-12 h-12 text-zinc-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        <p class="text-zinc-500 dark:text-zinc-400">No se encontraron colecciones</p>
                    </div>
                @endforelse
            </x-slot>
        </x-saved-views-table>
    </div>

    {{-- Modal de confirmación de eliminación --}}
    <flux:modal name="delete-modal" wire:model="showDeleteModal">
        <div class="p-6">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </div>
                
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-2">
                        Eliminar {{ count($deletingCollections) }} {{ count($deletingCollections) === 1 ? 'colección' : 'colecciones' }}
                    </h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                        ¿Estás seguro de que deseas eliminar {{ count($deletingCollections) === 1 ? 'esta colección' : 'estas colecciones' }}? Esta acción no se puede deshacer.
                    </p>
                </div>
            </div>
            
            <div class="flex justify-end gap-2 mt-6">
                <flux:button wire:click="cancelDelete" variant="ghost">
                    Cancelar
                </flux:button>
                <flux:button wire:click="deleteSelected" variant="danger">
                    Eliminar
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
