<?php

namespace App\Livewire\Product;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Product\OrderPurchase\OrderPurchases;
use App\Models\Product\Products;
use App\Models\Distribuidor\Distribuidores;
use App\Models\PointSale\Branch\Branches;
use App\Models\Product\OrderPurchase\ConditionPay;
use App\Models\Money\Moneda;
use App\Models\Transportista\Transportistas;

#[Layout('components.layouts.collapsable')]
class CreateOrderPurchase extends Component
{
    // Información del distribuidor
    public $id_distribuidor;
    public $id_sucursal_destino;
    public $id_condiciones_pago;
    public $id_moneda_del_distribuidor;
    
    // Información del envío
    public $fecha_llegada_estimada;
    public $id_empresa_trasnportista;
    public $numero_guia;
    
    // Productos
    public $searchProduct = '';
    public $selectedProducts = [];
    public $tempSelectedProducts = [];
    public $showProductModal = false;
    public $searchFilter = 'todo';
    
    // Información adicional
    public $numero_referencia;
    public $nota_al_distribuidor = '';
    public $id_etiquetas = '';
    
    // Totales y costos
    public $subtotal = 0;
    public $impuestos = 0;
    public $ajustes_costos = 0;
    public $costAdjustments = []; // Array de ajustes: [{tipo: 'aranceles', importe: 100}, ...]
    public $envio = 0;
    public $total = 0;
    
    // Modal de ajustes
    public $showCostAdjustmentModal = false;
    public $adjustmentType = '';
    public $adjustmentAmount = 0;
    public $availableAdjustments = [
        'aranceles' => 'Aranceles',
        'descuento' => 'Descuento',
        'cargo_transaccion_extranjera' => 'Cargo por transacción extranjera',
        'cargo_flete' => 'Cargo por flete',
        'seguro' => 'Seguro',
        'cargo_urgencia' => 'Cargo por urgencia',
        'recargo' => 'Recargo',
        'otro' => 'Otro',
    ];

    protected $rules = [
        'id_distribuidor' => 'required|exists:distribuidores,id',
        'id_sucursal_destino' => 'required|exists:branches,id',
        'selectedProducts' => 'required|array|min:1',
        'selectedProducts.*.id' => 'required|exists:products,id',
        'selectedProducts.*.cantidad' => 'required|integer|min:1',
        'selectedProducts.*.costo' => 'required|numeric|min:0',
    ];

    protected $messages = [
        'id_distribuidor.required' => 'El distribuidor es obligatorio.',
        'id_sucursal_destino.required' => 'El destino es obligatorio.',
        'selectedProducts.required' => 'Debes agregar al menos un producto.',
        'selectedProducts.min' => 'Debes agregar al menos un producto.',
    ];

    public function mount()
    {
        $this->fecha_llegada_estimada = now()->addDays(7)->format('Y-m-d');
        $this->calculateTotals();
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

    public function toggleProductSelection($productId, $productName, $productSku, $stock, $cost)
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
                'sku_distribuidor' => '',
                'stock' => $stock,
                'cantidad' => 1,
                'costo' => $cost,
                'impuesto' => 0,
            ];
        }
    }

    public function toggleVariantSelection($productId, $variantId, $productName, $variantName, $variantSku, $stock, $cost)
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
                'sku_distribuidor' => '',
                'stock' => $stock,
                'cantidad' => 1,
                'costo' => $cost,
                'impuesto' => 0,
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
                    'sku_distribuidor' => '',
                    'stock' => $variant->cantidad_inventario,
                    'cantidad' => 1,
                    'costo' => $variant->price ?? $product->price_unitario ?? 0,
                    'impuesto' => 0,
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
        
        $this->calculateTotals();
        $this->closeProductModal();
    }

    public function removeProduct($index)
    {
        unset($this->selectedProducts[$index]);
        $this->selectedProducts = array_values($this->selectedProducts);
        $this->calculateTotals();
    }

    public function updateQuantity($index, $quantity)
    {
        if (isset($this->selectedProducts[$index]) && $quantity > 0) {
            $this->selectedProducts[$index]['cantidad'] = $quantity;
            $this->calculateTotals();
        }
    }

    public function updateCosto($index, $costo)
    {
        if (isset($this->selectedProducts[$index]) && $costo >= 0) {
            $this->selectedProducts[$index]['costo'] = $costo;
            $this->calculateTotals();
        }
    }

    public function updateSkuDistribuidor($index, $sku)
    {
        if (isset($this->selectedProducts[$index])) {
            $this->selectedProducts[$index]['sku_distribuidor'] = $sku;
        }
    }

    public function updateImpuesto($index, $impuesto)
    {
        if (isset($this->selectedProducts[$index]) && $impuesto >= 0) {
            $this->selectedProducts[$index]['impuesto'] = $impuesto;
            $this->calculateTotals();
        }
    }

    public function updatedAjustesCostos()
    {
        $this->calculateTotals();
    }

    public function updatedEnvio()
    {
        $this->calculateTotals();
    }
    
    // Métodos para ajustes de costos
    public function openCostAdjustmentModal()
    {
        $this->showCostAdjustmentModal = true;
        $this->adjustmentType = '';
        $this->adjustmentAmount = 0;
    }
    
    public function closeCostAdjustmentModal()
    {
        $this->showCostAdjustmentModal = false;
    }
    
    public function addCostAdjustment()
    {
        if ($this->adjustmentType && $this->adjustmentAmount != 0) {
            $this->costAdjustments[] = [
                'tipo' => $this->adjustmentType,
                'nombre' => $this->availableAdjustments[$this->adjustmentType] ?? $this->adjustmentType,
                'importe' => floatval($this->adjustmentAmount),
            ];
            
            $this->calculateTotals();
            $this->closeCostAdjustmentModal();
        }
    }
    
    public function removeCostAdjustment($index)
    {
        unset($this->costAdjustments[$index]);
        $this->costAdjustments = array_values($this->costAdjustments);
        $this->calculateTotals();
    }

    private function calculateTotals()
    {
        $this->subtotal = 0;
        $this->impuestos = 0;
        $this->ajustes_costos = 0;
        
        foreach ($this->selectedProducts as $item) {
            $cantidad = $item['cantidad'] ?? 1;
            $costo = $item['costo'] ?? 0;
            $impuesto = $item['impuesto'] ?? 0;
            
            $subtotalItem = $costo * $cantidad;
            $this->subtotal += $subtotalItem;
            
            // Calcular impuesto por producto (porcentaje)
            $this->impuestos += ($subtotalItem * $impuesto) / 100;
        }
        
        // Sumar ajustes de costos
        foreach ($this->costAdjustments as $adjustment) {
            $this->ajustes_costos += $adjustment['importe'];
        }
        
        // Total
        $this->total = $this->subtotal + $this->impuestos + $this->ajustes_costos + $this->envio;
    }

    public function save()
    {
        $this->validate();

        try {
            // Crear una sola orden con todos los productos
            OrderPurchases::create([
                'estado' => 'borrador',
                'id_distribuidor' => $this->id_distribuidor,
                'id_sucursal_destino' => $this->id_sucursal_destino,
                'id_condiciones_pago' => $this->id_condiciones_pago,
                'id_moneda_del_distribuidor' => $this->id_moneda_del_distribuidor,
                'fecha_llegada_estimada' => $this->fecha_llegada_estimada,
                'id_empresa_trasnportista' => $this->id_empresa_trasnportista,
                'numero_guia' => $this->numero_guia,
                'numero_referencia' => $this->numero_referencia,
                'nota_al_distribuidor' => $this->nota_al_distribuidor,
                'ajustes_costos' => $this->costAdjustments,
                'subtotal' => $this->subtotal,
                'impuestos' => $this->impuestos,
                'envio' => $this->envio,
                'total' => $this->total,
                'productos' => $this->selectedProducts,
            ]);

            session()->flash('message', 'Orden de compra creada exitosamente en estado borrador');
            return redirect()->route('orders_purchases');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al crear la orden de compra: ' . $e->getMessage());
        }
    }

    public function cancel()
    {
        return redirect()->route('orders_purchases');
    }

    public function render()
    {
        $distribuidores = Distribuidores::all();
        $branches = Branches::all();
        $condicionesPago = ConditionPay::all();
        $monedas = Moneda::all();
        $transportistas = Transportistas::all();
        
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

        return view('livewire.producto.order-purchase.create-order-purchase', [
            'distribuidores' => $distribuidores,
            'branches' => $branches,
            'condicionesPago' => $condicionesPago,
            'monedas' => $monedas,
            'transportistas' => $transportistas,
            'products' => $products,
        ]);
    }
}
