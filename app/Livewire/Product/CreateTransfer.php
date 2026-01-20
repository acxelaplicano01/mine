<?php

namespace App\Livewire\Product;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Product\Transfer\Transfers;
use App\Models\Product\Transfer\StatusTransfer;
use App\Models\Product\Products;
use App\Models\PointSale\Branch\Branches;

#[Layout('components.layouts.collapsable')]
class CreateTransfer extends Component
{
    // Información básica
    public $id_sucursal_origen;
    public $id_sucursal_destino;
    public $nombre_referencia;
    
    // Fecha
    public $fecha_envio_creacion;
    
    // Productos
    public $searchProduct = '';
    public $selectedProducts = [];
    public $tempSelectedProducts = [];
    public $showProductModal = false;
    public $searchFilter = 'todo';
    
    // Información adicional
    public $nota_interna = '';
    public $id_etiquetas = '';
    
    // Estado
    public $id_status_transfer = 1;

    protected $rules = [
        'id_sucursal_origen' => 'required|exists:branches,id',
        'id_sucursal_destino' => 'required|exists:branches,id|different:id_sucursal_origen',
        'selectedProducts' => 'required|array|min:1',
        'selectedProducts.*.id' => 'required|exists:products,id',
        'selectedProducts.*.cantidad' => 'required|integer|min:1',
    ];

    protected $messages = [
        'id_sucursal_origen.required' => 'La sucursal de origen es obligatoria.',
        'id_sucursal_destino.required' => 'La sucursal de destino es obligatoria.',
        'id_sucursal_destino.different' => 'La sucursal de destino debe ser diferente a la de origen.',
        'selectedProducts.required' => 'Debes agregar al menos un producto.',
        'selectedProducts.min' => 'Debes agregar al menos un producto.',
    ];

    public function mount()
    {
        $this->fecha_envio_creacion = now()->format('Y-m-d');
    }

    public function openProductModal()
    {
        $this->showProductModal = true;
        $this->searchProduct = '';
        $this->tempSelectedProducts = [];
    }

    public function closeProductModal()
    {
        $this->showProductModal = false;
    }

    public function toggleProductSelection($productId, $productName, $productSku, $stock)
    {
        $key = 'product_' . $productId;
        
        if (isset($this->tempSelectedProducts[$key])) {
            unset($this->tempSelectedProducts[$key]);
        } else {
            $this->tempSelectedProducts[$key] = [
                'id' => $productId,
                'variant_id' => null,
                'name' => $productName,
                'sku' => $productSku,
                'stock' => $stock,
                'cantidad' => 1,
            ];
        }
    }

    public function toggleVariantSelection($productId, $variantId, $productName, $variantName, $variantSku, $stock)
    {
        $key = 'variant_' . $variantId;
        
        if (isset($this->tempSelectedProducts[$key])) {
            unset($this->tempSelectedProducts[$key]);
        } else {
            $this->tempSelectedProducts[$key] = [
                'id' => $productId,
                'variant_id' => $variantId,
                'name' => $productName . ' - ' . $variantName,
                'sku' => $variantSku,
                'stock' => $stock,
                'cantidad' => 1,
            ];
        }
    }

    public function toggleAllVariants($productId, $productName)
    {
        $product = Products::with('variants')->find($productId);
        
        if (!$product || !$product->variants) {
            return;
        }
        
        $allSelected = true;
        foreach ($product->variants as $variant) {
            $key = 'variant_' . $variant->id;
            if (!isset($this->tempSelectedProducts[$key])) {
                $allSelected = false;
                break;
            }
        }

        if ($allSelected) {
            foreach ($product->variants as $variant) {
                $key = 'variant_' . $variant->id;
                unset($this->tempSelectedProducts[$key]);
            }
        } else {
            foreach ($product->variants as $variant) {
                $key = 'variant_' . $variant->id;
                $valores = $variant->valores_variante;
                if (is_array($valores)) {
                    $variantDisplay = implode(' : ', array_values($valores));
                } else {
                    $variantDisplay = $valores;
                }
                
                $this->tempSelectedProducts[$key] = [
                    'id' => $productId,
                    'variant_id' => $variant->id,
                    'name' => $productName . ' - ' . $variantDisplay,
                    'sku' => $variant->sku ?? $product->sku,
                    'stock' => $variant->cantidad_inventario,
                    'cantidad' => 1,
                ];
            }
        }
    }

    public function addSelectedProducts()
    {
        foreach ($this->tempSelectedProducts as $product) {
            $exists = false;
            foreach ($this->selectedProducts as $key => $item) {
                if ($item['id'] == $product['id'] && $item['variant_id'] == $product['variant_id']) {
                    $this->selectedProducts[$key]['cantidad']++;
                    $exists = true;
                    break;
                }
            }
            
            if (!$exists) {
                $this->selectedProducts[] = $product;
            }
        }
        
        $this->closeProductModal();
    }

    public function removeProduct($index)
    {
        unset($this->selectedProducts[$index]);
        $this->selectedProducts = array_values($this->selectedProducts);
    }

    public function updateQuantity($index, $quantity)
    {
        if (isset($this->selectedProducts[$index]) && $quantity > 0) {
            $this->selectedProducts[$index]['cantidad'] = $quantity;
        }
    }

    public function save()
    {
        $this->validate();

        try {
            foreach ($this->selectedProducts as $product) {
                Transfers::create([
                    'id_sucursal_origen' => $this->id_sucursal_origen,
                    'id_sucursal_destino' => $this->id_sucursal_destino,
                    'fecha_envio_creacion' => $this->fecha_envio_creacion,
                    'id_product' => $product['id'],
                    'cantidad' => $product['cantidad'],
                    'nombre_referencia' => $this->nombre_referencia,
                    'nota_interna' => $this->nota_interna,
                    'id_etquetas' => $this->id_etiquetas,
                    'id_status_transfer' => $this->id_status_transfer,
                ]);
            }

            session()->flash('message', 'Transferencia creada exitosamente');
            return redirect()->route('transfers');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al crear la transferencia: ' . $e->getMessage());
        }
    }

    public function cancel()
    {
        return redirect()->route('transfers');
    }

    public function render()
    {
        $branches = Branches::all();
        
        $products = Products::with('variants')
            ->when($this->searchProduct, function($query) {
                $searchTerm = '%' . $this->searchProduct . '%';
                $query->where(function($q) use ($searchTerm) {
                    if ($this->searchFilter === 'nombre' || $this->searchFilter === 'todo') {
                        $q->where('name', 'like', $searchTerm);
                    }
                    if ($this->searchFilter === 'sku' || $this->searchFilter === 'todo') {
                        $q->orWhere('sku', 'like', $searchTerm);
                    }
                });
            })
            ->limit(50)
            ->get();

        return view('livewire.producto.transfer.create-transfer', [
            'branches' => $branches,
            'products' => $products,
        ]);
    }
}
