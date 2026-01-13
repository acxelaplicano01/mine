# Guía de Uso: Sistema de Vistas Guardadas Reutilizable

Este documento explica cómo implementar el sistema de filtros y vistas guardadas en cualquier tabla de tu aplicación.

## Arquitectura del Sistema

El sistema consta de tres componentes principales:

1. **Base de datos**: Tabla `user_saved_views` para almacenar vistas persistentemente
2. **Trait PHP**: `HasSavedViews` - Lógica reutilizable de filtrado y vistas guardadas
3. **Componente Blade**: `saved-views-table` - UI reutilizable con slots personalizables

## Paso 1: Preparar el Componente Livewire

### 1.1 Usar el Trait

En tu componente Livewire, agrega el trait `HasSavedViews`:

```php
use App\Livewire\Traits\HasSavedViews;
use Livewire\WithPagination;

class TuComponente extends Component
{
    use HasSavedViews, WithPagination;
    
    // ... tus propiedades
}
```

### 1.2 Implementar Métodos Abstractos

El trait requiere dos métodos abstractos:

#### a) `getViewType()` - Identificador único

```php
abstract protected function getViewType(): string;
```

**Ejemplo:**
```php
protected function getViewType(): string
{
    return 'productos'; // o 'clientes', 'facturas', etc.
}
```

#### b) `applyFilterToQuery()` - Lógica de filtrado específica

```php
abstract protected function applyFilterToQuery($query, array $filter);
```

**Ejemplo:**
```php
protected function applyFilterToQuery($query, array $filter)
{
    switch ($filter['type']) {
        case 'en_stock':
            $query->where('stock', '>', 0);
            break;
            
        case 'sin_stock':
            $query->where('stock', '<=', 0);
            break;
            
        case 'categoria':
            $query->where('category_id', $filter['value']);
            break;
            
        case 'precio_mayor_a':
            $query->where('price', '>', $filter['value']);
            break;
            
        // Agrega más casos según tus necesidades
    }
}
```

### 1.3 Cargar Vistas en mount()

```php
public function mount()
{
    $this->loadSavedViews(); // Carga las vistas guardadas desde la BD
}
```

### 1.4 Aplicar Filtros en render()

```php
public function render()
{
    $query = Product::query();
    
    // Aplicar filtros predefinidos
    if ($this->activeFilter === 'en_stock') {
        $query->where('stock', '>', 0);
    }
    
    // Aplicar vistas guardadas (filtros dinámicos + búsqueda)
    if (str_starts_with($this->activeFilter, 'custom_')) {
        $this->applySavedViewFilters($query);
    }
    
    $productos = $query->paginate(20);
    
    return view('livewire.productos', [
        'productos' => $productos,
    ]);
}
```

## Paso 2: Configurar la Vista Blade

### 2.1 Estructura Básica

```blade
<x-saved-views-table 
    view-name="productos" 
    search-placeholder="Buscar productos..."
    save-button-text="Guardar vista"
>
    {{-- Tabs predefinidos --}}
    <x-slot name="predefinedTabs">
        <!-- Tus tabs personalizados -->
    </x-slot>
    
    {{-- Dropdown de filtros --}}
    <x-slot name="filtersDropdown">
        <!-- Tus filtros personalizados -->
    </x-slot>
    
    {{-- Contenido de la tabla --}}
    <table class="w-full">
        <!-- Tu tabla aquí -->
    </table>
</x-saved-views-table>
```

### 2.2 Definir Tabs Predefinidos

```blade
<x-slot name="predefinedTabs">
    <button 
        wire:click="setFilter('todos')"
        class="px-3 py-1.5 text-sm font-medium rounded transition-colors whitespace-nowrap {{ $activeFilter === 'todos' ? 'bg-zinc-900 text-white dark:bg-zinc-700' : 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700' }}"
    >
        Todos
    </button>
    
    <button 
        wire:click="setFilter('en_stock')"
        class="px-3 py-1.5 text-sm font-medium rounded transition-colors whitespace-nowrap {{ $activeFilter === 'en_stock' ? 'bg-zinc-900 text-white dark:bg-zinc-700' : 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700' }}"
    >
        En Stock
    </button>
    
    <button 
        wire:click="setFilter('sin_stock')"
        class="px-3 py-1.5 text-sm font-medium rounded transition-colors whitespace-nowrap {{ $activeFilter === 'sin_stock' ? 'bg-zinc-900 text-white dark:bg-zinc-700' : 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700' }}"
    >
        Sin Stock
    </button>
</x-slot>
```

### 2.3 Definir Filtros Disponibles

```blade
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
                    {{-- Stock --}}
                    <div class="mb-2">
                        <flux:menu.separator>Stock</flux:menu.separator>
                        <flux:menu.item wire:click="addFilter('en_stock', null, 'Stock: En stock')">En stock</flux:menu.item>
                        <flux:menu.item wire:click="addFilter('sin_stock', null, 'Stock: Sin stock')">Sin stock</flux:menu.item>
                    </div>
                    
                    {{-- Categorías --}}
                    <div class="mb-2">
                        <flux:menu.separator>Categoría</flux:menu.separator>
                        @foreach($categorias as $categoria)
                            <flux:menu.item wire:click="addFilter('categoria', {{ $categoria->id }}, 'Categoría: {{ $categoria->name }}')">
                                {{ $categoria->name }}
                            </flux:menu.item>
                        @endforeach
                    </div>
                </div>
            </div>
        </flux:menu>
    </flux:dropdown>
</x-slot>
```

## Métodos Disponibles del Trait

### Gestión de Búsqueda
- `toggleSearchBar()` - Muestra/oculta la barra de búsqueda
- `setFilter($filter)` - Activa un filtro predefinido

### Gestión de Filtros Dinámicos
- `addFilter($type, $value, $label)` - Agrega un filtro dinámico
- `removeFilter($filterId)` - Elimina un filtro específico
- `clearAllFilters()` - Elimina todos los filtros

### Gestión de Vistas Guardadas
- `saveCurrentView()` - Guarda la vista actual en la base de datos
- `loadTab($tabId)` - Carga una vista guardada
- `deleteTab($tabId)` - Elimina una vista guardada
- `renameTab()` - Renombra una vista guardada
- `duplicateTab($tabId)` - Duplica una vista guardada

### Modales
- `openSaveTabModal()` / `closeSaveTabModal()` - Controla el modal de guardar
- `openRenameTabModal($tabId)` / `closeRenameTabModal()` - Controla el modal de renombrar

## Ejemplo Completo: Tabla de Productos

### Componente Livewire: `app/Livewire/Product/Products.php`

```php
<?php

namespace App\Livewire\Product;

use App\Livewire\Traits\HasSavedViews;
use App\Models\Product\Products as ProductModel;
use App\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;

class Products extends Component
{
    use HasSavedViews, WithPagination;

    protected function getViewType(): string
    {
        return 'products';
    }

    protected function applyFilterToQuery($query, array $filter)
    {
        switch ($filter['type']) {
            case 'en_stock':
                $query->where('stock', '>', 0);
                break;
                
            case 'sin_stock':
                $query->where('stock', '<=', 0);
                break;
                
            case 'categoria':
                $query->where('category_id', $filter['value']);
                break;
        }
    }

    public function mount()
    {
        $this->loadSavedViews();
    }

    public function render()
    {
        $query = ProductModel::query();

        // Filtros predefinidos
        if ($this->activeFilter === 'en_stock') {
            $query->where('stock', '>', 0);
        } elseif ($this->activeFilter === 'sin_stock') {
            $query->where('stock', '<=', 0);
        }

        // Vistas guardadas (custom)
        if (str_starts_with($this->activeFilter, 'custom_')) {
            $this->applySavedViewFilters($query);
        }

        $products = $query->paginate(20);
        $categorias = Category::all();

        return view('livewire.product.products', [
            'products' => $products,
            'categorias' => $categorias,
        ]);
    }
}
```

### Vista Blade: `resources/views/livewire/product/products.blade.php`

```blade
<div class="min-h-screen">
    <div class="px-2 sm:px-6 lg:px-8">
        <x-saved-views-table 
            view-name="productos" 
            search-placeholder="Buscar productos..."
            save-button-text="Guardar vista de productos"
        >
            {{-- Tabs predefinidos --}}
            <x-slot name="predefinedTabs">
                <button 
                    wire:click="setFilter('todos')"
                    class="px-3 py-1.5 text-sm font-medium rounded transition-colors whitespace-nowrap {{ $activeFilter === 'todos' ? 'bg-zinc-900 text-white dark:bg-zinc-700' : 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700' }}"
                >
                    Todos
                </button>
                
                <button 
                    wire:click="setFilter('en_stock')"
                    class="px-3 py-1.5 text-sm font-medium rounded transition-colors whitespace-nowrap {{ $activeFilter === 'en_stock' ? 'bg-zinc-900 text-white dark:bg-zinc-700' : 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700' }}"
                >
                    En Stock
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
                            <flux:input 
                                wire:model.live.debounce.300ms="filterSearch"
                                placeholder="Buscar..."
                                icon="magnifying-glass"
                                class="mb-2"
                            />
                            
                            <div class="max-h-96 overflow-y-auto">
                                {{-- Stock --}}
                                <div class="mb-2">
                                    <flux:menu.separator>Stock</flux:menu.separator>
                                    <flux:menu.item wire:click="addFilter('en_stock', null, 'Stock: En stock')">En stock</flux:menu.item>
                                    <flux:menu.item wire:click="addFilter('sin_stock', null, 'Stock: Sin stock')">Sin stock</flux:menu.item>
                                </div>
                                
                                {{-- Categorías --}}
                                <div class="mb-2">
                                    <flux:menu.separator>Categoría</flux:menu.separator>
                                    @foreach($categorias as $categoria)
                                        <flux:menu.item wire:click="addFilter('categoria', {{ $categoria->id }}, 'Categoría: {{ $categoria->name }}')">
                                            {{ $categoria->name }}
                                        </flux:menu.item>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </flux:menu>
                </flux:dropdown>
            </x-slot>
        </x-saved-views-table>

        {{-- Tabla de productos --}}
        <table class="w-full">
            <thead class="bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-700 dark:text-zinc-300">Producto</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-700 dark:text-zinc-300">SKU</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-700 dark:text-zinc-300">Stock</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-700 dark:text-zinc-300">Precio</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-700 dark:text-zinc-300">Categoría</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse($products as $product)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                        <td class="px-4 py-3">{{ $product->name }}</td>
                        <td class="px-4 py-3">{{ $product->sku }}</td>
                        <td class="px-4 py-3">{{ $product->stock }}</td>
                        <td class="px-4 py-3">${{ number_format($product->price, 2) }}</td>
                        <td class="px-4 py-3">{{ $product->category->name ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                            No hay productos registrados
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Paginación --}}
        @if($products->hasPages())
            <div class="px-4 py-3 border-t border-zinc-200 dark:border-zinc-700">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>
```

## Propiedades del Componente `<x-saved-views-table>`

| Propiedad | Tipo | Por Defecto | Descripción |
|-----------|------|-------------|-------------|
| `view-name` | string | `'tabla'` | Nombre descriptivo de la vista (para textos del UI) |
| `search-placeholder` | string | `'Buscar...'` | Placeholder del input de búsqueda |
| `save-button-text` | string | `'Guardar vista'` | Texto del botón de guardar vista |

## Slots Disponibles

| Slot | Requerido | Descripción |
|------|-----------|-------------|
| `predefinedTabs` | ✅ | Botones de tabs predefinidos (Todos, En stock, etc.) |
| `filtersDropdown` | ✅ | Contenido del dropdown de filtros |
| `filtersDropdownCompact` | ❌ | Versión compacta del dropdown (opcional) |
| *Slot principal* | ✅ | Contenido de la tabla |

## Flujo de Trabajo del Usuario

1. **Vista por defecto**: El usuario ve los tabs predefinidos y los tabs guardados
2. **Búsqueda**: Click en "Buscar" → Se expande barra de búsqueda
3. **Agregar filtros**: Click en "Agregar filtro" → Selecciona filtros del dropdown
4. **Guardar vista**: Click en "Guardar vista" → Modal con nombre → Vista guardada aparece como nuevo tab
5. **Usar vista guardada**: Click en tab guardado → Se aplican filtros y búsqueda automáticamente
6. **Gestionar vistas**: En tab activo → Click en flecha → Menú con opciones (Renombrar, Duplicar, Eliminar)

## Notas Importantes

- Las vistas se guardan **por usuario** (columna `user_id`)
- Las vistas son **específicas por tipo** (columna `view_type`)
- Los filtros se almacenan como **JSON** en la columna `filters`
- El componente incluye **dark mode** automáticamente
- Los modales están **integrados** en el componente
- La búsqueda y filtros son **independientes** (se pueden usar juntos)

## Ventajas del Sistema

✅ **Reutilizable**: Implementa en cualquier tabla con mínimo código
✅ **Persistente**: Las vistas se guardan en la base de datos
✅ **Flexible**: Personaliza tabs y filtros según tus necesidades
✅ **UX Shopify**: Interfaz probada y familiar para usuarios
✅ **Organizado**: Trait para lógica, componente para UI
✅ **Escalable**: Agrega nuevos tipos de vistas fácilmente

---

Para más información o soporte, consulta el código de referencia en:
- `app/Livewire/Order/Orders.php`
- `resources/views/livewire/order/orders.blade.php`
- `app/Livewire/Traits/HasSavedViews.php`
- `resources/views/components/saved-views-table.blade.php`
