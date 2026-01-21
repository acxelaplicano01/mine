<?php

namespace App\Livewire\Product;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use App\Models\Product\Collection\CollectionsPage;
use App\Models\Product\Collection\CollectionProduct;
use App\Models\Product\Products;
use App\Models\Product\TypeProduct;
use App\Models\Etiquetas\Etiquetas;
use App\Models\Distribuidor\Distribuidores;
use Illuminate\Support\Facades\DB;

#[Layout('components.layouts.collapsable')]
class CreateCollection extends Component
{
    use WithFileUploads;

    // Propiedades del formulario
    public $name = '';
    public $description = '';
    public $id_tipo_collection = 1; // 1: Manual, 2: Inteligente
    public $image_url = '';
    public $id_status_collection = 1; // 1: Activo, 0: Inactivo
    public $id_publicacion = null;
    
    // Búsqueda y selección de productos (para colecciones manuales)
    public $searchProduct = '';
    public $searchFilter = 'todo';
    public $selectedProducts = [];
    public $tempSelectedProducts = [];
    public $showProductModal = false;
    public $products = [];
    
    // Condiciones para colecciones inteligentes
    public $conditionMatch = 'all'; // 'all' o 'any'
    public $conditions = [];
    
    // Datos para los selects de condiciones
    public $tipos = [];
    public $distribuidores = [];
    public $etiquetas = [];
    public $categorias = [];
    
    public function mount()
    {
        $this->loadProducts();
        // Agregar una condición por defecto para colecciones inteligentes
        $this->conditions = [
            ['field' => 'etiqueta', 'operator' => 'igual', 'value' => '']
        ];
        
        // Cargar datos para los selects
        $this->distribuidores = Distribuidores::all(['id', 'name'])->toArray();
        $this->tipos = TypeProduct::all(['id', 'name'])->toArray();
        $this->etiquetas = Etiquetas::all(['id', 'name'])->toArray();
    }
    
    public function addCondition()
    {
        $this->conditions[] = ['field' => 'etiqueta', 'operator' => 'igual', 'value' => ''];
    }
    
    public function removeCondition($index)
    {
        unset($this->conditions[$index]);
        $this->conditions = array_values($this->conditions);
    }

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'id_tipo_collection' => 'required|in:1,2',
        'image_url' => 'nullable|string',
        'id_status_collection' => 'required|in:0,1',
    ];

    protected $messages = [
        'name.required' => 'El nombre de la colección es obligatorio',
        'name.max' => 'El nombre no puede exceder 255 caracteres',
    ];



    public function updatedSearchProduct()
    {
        $this->loadProducts();
    }

    public function updatedSearchFilter()
    {
        $this->loadProducts();
    }

    public function loadProducts()
    {
        $query = Products::query()
            ->with(['variants' => function($query) {
                $query->select('id', 'product_id', 'valores_variante', 'sku', 'cantidad_inventario');
            }])
            ->where('id_status_product', 1);

        if ($this->searchProduct) {
            $search = $this->searchProduct;
            
            if ($this->searchFilter === 'nombre') {
                $query->where('name', 'like', '%' . $search . '%');
            } elseif ($this->searchFilter === 'sku') {
                $query->where('sku', 'like', '%' . $search . '%');
            } else {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('sku', 'like', '%' . $search . '%')
                      ->orWhere('description', 'like', '%' . $search . '%');
                });
            }
        }

        $this->products = $query->limit(50)->get();
    }

    public function openProductModal()
    {
        $this->showProductModal = true;
        $this->tempSelectedProducts = [];
        
        // Pre-seleccionar productos ya agregados
        foreach ($this->selectedProducts as $product) {
            if (isset($product['variant_id'])) {
                $this->tempSelectedProducts['variant_' . $product['variant_id']] = true;
            } else {
                $this->tempSelectedProducts['product_' . $product['product_id']] = true;
            }
        }
        
        $this->loadProducts();
    }

    public function closeProductModal()
    {
        $this->showProductModal = false;
        $this->tempSelectedProducts = [];
    }

    public function toggleProductSelection($productId, $name, $sku)
    {
        $key = 'product_' . $productId;
        
        if (isset($this->tempSelectedProducts[$key])) {
            unset($this->tempSelectedProducts[$key]);
        } else {
            $this->tempSelectedProducts[$key] = [
                'product_id' => $productId,
                'variant_id' => null,
                'name' => $name,
                'sku' => $sku,
            ];
        }
    }

    public function toggleVariantSelection($productId, $variantId, $productName, $variantName, $sku)
    {
        $key = 'variant_' . $variantId;
        
        if (isset($this->tempSelectedProducts[$key])) {
            unset($this->tempSelectedProducts[$key]);
        } else {
            $this->tempSelectedProducts[$key] = [
                'product_id' => $productId,
                'variant_id' => $variantId,
                'name' => $productName . ' - ' . $variantName,
                'sku' => $sku,
            ];
        }
    }

    public function toggleAllVariants($productId, $productName)
    {
        $product = $this->products->firstWhere('id', $productId);
        
        if (!$product || !$product->variants) {
            return;
        }

        $allSelected = true;
        foreach ($product->variants as $variant) {
            if (!isset($this->tempSelectedProducts['variant_' . $variant->id])) {
                $allSelected = false;
                break;
            }
        }

        if ($allSelected) {
            // Deseleccionar todas las variantes
            foreach ($product->variants as $variant) {
                unset($this->tempSelectedProducts['variant_' . $variant->id]);
            }
        } else {
            // Seleccionar todas las variantes
            foreach ($product->variants as $variant) {
                $valores = $variant->valores_variante;
                if (is_array($valores)) {
                    $variantDisplay = implode(' : ', array_values($valores));
                } else {
                    $variantDisplay = $valores;
                }
                
                $this->tempSelectedProducts['variant_' . $variant->id] = [
                    'product_id' => $productId,
                    'variant_id' => $variant->id,
                    'name' => $productName . ' - ' . $variantDisplay,
                    'sku' => $variant->sku ?? $product->sku,
                ];
            }
        }
    }

    public function addSelectedProducts()
    {
        foreach ($this->tempSelectedProducts as $key => $product) {
            if (is_array($product)) {
                $exists = false;
                foreach ($this->selectedProducts as $existing) {
                    if ($existing['product_id'] == $product['product_id'] && 
                        $existing['variant_id'] == $product['variant_id']) {
                        $exists = true;
                        break;
                    }
                }
                
                if (!$exists) {
                    $this->selectedProducts[] = $product;
                }
            }
        }
        
        $this->closeProductModal();
    }

    public function removeProduct($index)
    {
        unset($this->selectedProducts[$index]);
        $this->selectedProducts = array_values($this->selectedProducts);
    }

    public function save()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            $data = [
                'name' => $this->name,
                'description' => $this->description,
                'id_tipo_collection' => $this->id_tipo_collection,
                'image_url' => $this->image_url,
                'id_status_collection' => $this->id_status_collection,
                'id_publicacion' => $this->id_publicacion,
            ];
            
            // Si es colección inteligente, guardar las condiciones
            if ($this->id_tipo_collection == 2) {
                $data['conditions'] = $this->conditions;
                $data['condition_match'] = $this->conditionMatch;
            }

            $collection = CollectionsPage::create($data);

            // Si es manual, guardar los productos seleccionados en la tabla intermedia
            if ($this->id_tipo_collection == 1 && count($this->selectedProducts) > 0) {
                foreach ($this->selectedProducts as $index => $product) {
                    CollectionProduct::create([
                        'collection_id' => $collection->id,
                        'product_id' => $product['product_id'],
                        'variant_id' => $product['variant_id'],
                        'sort_order' => $index,
                    ]);
                }
            }

            // Si es inteligente, aplicar las condiciones y agregar productos que cumplan
            if ($this->id_tipo_collection == 2) {
                $matchingProducts = $collection->getSmartCollectionProducts();
                
                foreach ($matchingProducts as $index => $product) {
                    CollectionProduct::create([
                        'collection_id' => $collection->id,
                        'product_id' => $product->id,
                        'variant_id' => null,
                        'sort_order' => $index,
                    ]);
                }
            }

            DB::commit();

            session()->flash('message', 'Colección creada exitosamente');
            
            return redirect()->route('collections');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al crear la colección: ' . $e->getMessage());
        }
    }

    public function cancel()
    {
        return redirect()->route('collections');
    }

    public function render()
    {
        return view('livewire.producto.collection.create-collection');
    }
}
