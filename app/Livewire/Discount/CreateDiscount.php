<?php

namespace App\Livewire\Discount;

use Livewire\Component;
use App\Models\Product\Collection\CollectionsPage;
use App\Models\Discount\Discounts as DiscountModel;
use App\Models\Discount\TypeDiscount;
use App\Models\Discount\MethodDiscount;
use App\Models\Discount\ElegibilityDiscount;
use App\Models\Discount\RequirementDiscount;
use App\Models\Discount\StatusDiscount;
use App\Models\Market\Markets;
use App\Models\Product\Products;
use App\Models\Customer\Customers;
use App\Models\Customer\Segments;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.collapsable')]
class CreateDiscount extends Component
{
    public $discountId = null;
    public $isEdit = false;
    
    // Pestañas
    public $activeTab = 'codigo';
    
    // Campos básicos
    public $code_discount = '';
    public $description = '';
    public $valor_discount = 0;
    public $discount_value_type = 'percentage';
    public $id_market;
    public $id_type_discount = 1;
    public $id_method_discount = 1;
    
    // Aplicación
    public $applies_to = 'products';
    public $selected_collections = [];
    public $selected_products = [];
    public $searchProducts = '';
    public $searchCollections = '';
    
    // Modales
    public $showProductModal = false;
    public $showCollectionModal = false;
    public $tempSelectedProducts = [];
    public $tempSelectedCollections = [];
    
    // Elegibilidad
    public $id_elegibility_discount = 1;
    public $selected_customers = [];
    public $selected_segments = [];
    public $searchCustomers = '';
    public $searchSegments = '';
    
    // Requisitos
    public $id_requirement_discount = 1;
    public $minimum_purchase_amount = null;
    public $minimum_quantity = null;
    
    // Usos máximos
    public $limit_usage = false;
    public $number_usage_max = null;
    public $limit_per_customer = false;
    
    // Combinaciones
    public $combine_with_product = false;
    public $combine_with_order = false;
    public $combine_with_shipping = false;
    
    // Fechas
    public $fecha_inicio_uso;
    public $hora_inicio_uso;
    public $set_end_date = false;
    public $fecha_fin_uso;
    public $hora_fin_uso;
    
    // Canales
    public $accesible_channel_sales = ['online', 'pos'];
    public $channel_promotion = false;
    
    public $id_status_discount = 1;

    public function mount($id = null)
    {
        if ($id) {
            $this->isEdit = true;
            $this->discountId = $id;
            $this->loadDiscount($id);
        } else {
            $this->fecha_inicio_uso = now()->format('Y-m-d');
            $this->hora_inicio_uso = now()->format('H:i');
            
            $hondurasMarket = Markets::where('name', 'like', '%Honduras%')->first();
            if ($hondurasMarket) {
                $this->id_market = $hondurasMarket->id;
            }
        }
    }

    public function loadDiscount($id)
    {
        $discount = DiscountModel::findOrFail($id);
        
        $this->code_discount = $discount->code_discount ?? '';
        $this->description = $discount->description;
        $this->valor_discount = $discount->valor_discount;
        $this->discount_value_type = $discount->discount_value_type;
        $this->id_market = $discount->id_market;
        $this->id_type_discount = $discount->id_type_discount;
        $this->id_method_discount = $discount->id_method_discount;
        $this->id_elegibility_discount = $discount->id_elegibility_discount;
        $this->id_requirement_discount = $discount->id_requirement_discount;
        $this->minimum_purchase_amount = $discount->minimum_purchase_amount;
        $this->minimum_quantity = $discount->minimum_quantity;
        $this->number_usage_max = $discount->number_usage_max;
        $this->fecha_inicio_uso = $discount->fecha_inicio_uso ? $discount->fecha_inicio_uso->format('Y-m-d') : null;
        $this->hora_inicio_uso = $discount->hora_inicio_uso ? $discount->hora_inicio_uso->format('H:i') : null;
        $this->fecha_fin_uso = $discount->fecha_fin_uso ? $discount->fecha_fin_uso->format('Y-m-d') : null;
        $this->hora_fin_uso = $discount->hora_fin_uso ? $discount->hora_fin_uso->format('H:i') : null;
        $this->id_status_discount = $discount->id_status_discount;
        
        $this->limit_usage = !is_null($discount->number_usage_max);
        $this->limit_per_customer = $discount->limit_per_customer ?? false;
        $this->set_end_date = !is_null($discount->fecha_fin_uso);
        $this->combine_with_product = $discount->combine_with_product ?? false;
        $this->combine_with_order = $discount->combine_with_order ?? false;
        $this->combine_with_shipping = $discount->combine_with_shipping ?? false;
        $this->channel_promotion = $discount->channel_promotion ?? false;
        
        $this->activeTab = $discount->id_method_discount == 1 ? 'codigo' : 'automatico';
        
        // Cargar productos/colecciones seleccionados si existen
        if ($discount->selected_products) {
            $this->selected_products = is_array($discount->selected_products) 
                ? $discount->selected_products 
                : json_decode($discount->selected_products, true) ?? [];
        }
        
        if ($discount->selected_collections) {
            $this->selected_collections = is_array($discount->selected_collections) 
                ? $discount->selected_collections 
                : json_decode($discount->selected_collections, true) ?? [];
        }
        
        // Cargar clientes y segmentos seleccionados
        if ($discount->selected_customers) {
            $this->selected_customers = is_array($discount->selected_customers) 
                ? $discount->selected_customers 
                : json_decode($discount->selected_customers, true) ?? [];
        }
        
        if ($discount->selected_segments) {
            $this->selected_segments = is_array($discount->selected_segments) 
                ? $discount->selected_segments 
                : json_decode($discount->selected_segments, true) ?? [];
        }
    }

    public function generateRandomCode()
    {
        $this->code_discount = strtoupper(Str::random(12));
    }

    public function updatedActiveTab($value)
    {
        $this->id_method_discount = $value === 'codigo' ? 1 : 2;
        
        if ($value === 'codigo' && empty($this->code_discount)) {
            $this->generateRandomCode();
        }
        
        if ($value === 'automatico') {
            $this->code_discount = null;
        }
    }
    
    public function openProductModal()
    {
        $this->searchProducts = '';
        $this->tempSelectedProducts = $this->selected_products;
        $this->modal('product-modal')->show();
    }
    
    public function closeProductModal()
    {
        $this->modal('product-modal')->close();
        $this->tempSelectedProducts = [];
    }
    
    public function toggleProductSelection($productId, $productName, $productPrice, $productStock = 0)
    {
        $key = 'product_' . $productId;
        
        if (isset($this->tempSelectedProducts[$key])) {
            unset($this->tempSelectedProducts[$key]);
        } else {
            $this->tempSelectedProducts[$key] = [
                'id' => $productId,
                'name' => $productName,
                'price' => $productPrice,
                'stock' => $productStock,
                'type' => 'product'
            ];
        }
    }
    
    public function toggleAllVariants($productId, $productName)
    {
        $product = Products::with('variants')->find($productId);
        
        if (!$product || !$product->variants || count($product->variants) === 0) {
            return;
        }
        
        // Verificar si todas las variantes están seleccionadas
        $allSelected = true;
        foreach ($product->variants as $variant) {
            $key = 'variant_' . $variant->id;
            if (!isset($this->tempSelectedProducts[$key])) {
                $allSelected = false;
                break;
            }
        }
        
        // Si todas están seleccionadas, deseleccionar todas; si no, seleccionar todas
        if ($allSelected) {
            foreach ($product->variants as $variant) {
                $key = 'variant_' . $variant->id;
                unset($this->tempSelectedProducts[$key]);
            }
        } else {
            foreach ($product->variants as $variant) {
                $valores = $variant->valores_variante;
                $variantDisplay = is_array($valores) ? implode(' : ', array_values($valores)) : $valores;
                
                $key = 'variant_' . $variant->id;
                $this->tempSelectedProducts[$key] = [
                    'id' => $variant->id,
                    'product_id' => $productId,
                    'name' => $productName,
                    'variant' => $variantDisplay,
                    'price' => $variant->price,
                    'stock' => $variant->cantidad_inventario ?? 0,
                    'type' => 'variant'
                ];
            }
        }
    }
    
    public function toggleVariantSelection($productId, $variantId, $productName, $variantDisplay, $variantPrice, $variantStock = 0)
    {
        $key = 'variant_' . $variantId;
        
        if (isset($this->tempSelectedProducts[$key])) {
            unset($this->tempSelectedProducts[$key]);
        } else {
            $this->tempSelectedProducts[$key] = [
                'id' => $variantId,
                'product_id' => $productId,
                'name' => $productName,
                'variant' => $variantDisplay,
                'price' => $variantPrice,
                'stock' => $variantStock,
                'type' => 'variant'
            ];
        }
    }
    
    public function confirmProductSelection()
    {
        $this->selected_products = $this->tempSelectedProducts;
        $this->tempSelectedProducts = [];
        $this->modal('product-modal')->close();
    }
    
    public function removeProduct($key)
    {
        unset($this->selected_products[$key]);
        $this->selected_products = $this->selected_products; // Forzar actualización
    }
    
    public function openCollectionModal()
    {
        $this->searchCollections = '';
        $this->tempSelectedCollections = $this->selected_collections;
        $this->modal('collection-modal')->show();
    }
    
    public function closeCollectionModal()
    {
        $this->modal('collection-modal')->close();
        $this->tempSelectedCollections = [];
    }
    
    public function toggleCollectionSelection($collectionId, $collectionName)
    {
        $key = 'collection_' . $collectionId;
        
        if (isset($this->tempSelectedCollections[$key])) {
            unset($this->tempSelectedCollections[$key]);
        } else {
            $this->tempSelectedCollections[$key] = [
                'id' => $collectionId,
                'name' => $collectionName,
                'type' => 'collection'
            ];
        }
    }
    
    public function confirmCollectionSelection()
    {
        $this->selected_collections = $this->tempSelectedCollections;
        $this->tempSelectedCollections = [];
        $this->modal('collection-modal')->close();
    }
    
    public function removeCollection($key)
    {
        unset($this->selected_collections[$key]);
        $this->selected_collections = $this->selected_collections; // Forzar actualización
    }
    
    // Métodos para manejar segmentos
    public function openSegmentModal()
    {
        $this->searchSegments = '';
        $this->modal('segment-modal')->show();
    }
    
    public function closeSegmentModal()
    {
        $this->modal('segment-modal')->close();
    }
    
    public function toggleSegmentSelection($segmentId, $segmentName)
    {
        $key = 'segment_' . $segmentId;
        
        if (isset($this->selected_segments[$key])) {
            unset($this->selected_segments[$key]);
        } else {
            $this->selected_segments[$key] = [
                'id' => $segmentId,
                'name' => $segmentName,
                'type' => 'segment'
            ];
        }
    }
    
    public function removeSegment($key)
    {
        unset($this->selected_segments[$key]);
        $this->selected_segments = $this->selected_segments;
    }
    
    // Métodos para manejar clientes
    public function openCustomerModal()
    {
        $this->searchCustomers = '';
        $this->modal('customer-modal')->show();
    }
    
    public function closeCustomerModal()
    {
        $this->modal('customer-modal')->close();
    }
    
    public function toggleCustomerSelection($customerId, $customerName, $customerEmail = null)
    {
        $key = 'customer_' . $customerId;
        
        if (isset($this->selected_customers[$key])) {
            unset($this->selected_customers[$key]);
        } else {
            $this->selected_customers[$key] = [
                'id' => $customerId,
                'name' => $customerName,
                'email' => $customerEmail,
                'type' => 'customer'
            ];
        }
    }
    
    public function removeCustomer($key)
    {
        unset($this->selected_customers[$key]);
        $this->selected_customers = $this->selected_customers;
    }

    public function save()
    {
        // Validación básica
        $this->validate([
            'valor_discount' => 'required|numeric|min:0',
            'id_type_discount' => 'required|exists:type_discounts,id',
            'fecha_inicio_uso' => 'required|date',
        ]);
        
        // Validar código si es necesario
        if ($this->activeTab === 'codigo' && empty($this->code_discount)) {
            $this->generateRandomCode();
        }
        
        $data = [
            'code_discount' => $this->activeTab === 'codigo' ? $this->code_discount : null,
            'description' => $this->description,
            'valor_discount' => $this->valor_discount,
            'discount_value_type' => $this->discount_value_type,
            'id_market' => $this->id_market,
            'id_type_discount' => $this->id_type_discount,
            'id_method_discount' => $this->id_method_discount,
            'id_elegibility_discount' => $this->id_elegibility_discount,
            'id_requirement_discount' => $this->id_requirement_discount,
            'amount' => $this->id_requirement_discount == 3 ? $this->minimum_purchase_amount : null,
            'minimum_quantity' => $this->id_requirement_discount == 2 ? $this->minimum_quantity : null,
            'minimum_purchase_amount' => $this->id_requirement_discount == 3 ? $this->minimum_purchase_amount : null,
            'usage_limit' => $this->limit_usage ? $this->number_usage_max : null,
            'number_usage_max' => $this->limit_usage ? $this->number_usage_max : null,
            'usage_per_customer' => $this->limit_per_customer ? 1 : null,
            'limit_per_customer' => $this->limit_per_customer,
            'combine_with_product' => $this->combine_with_product,
            'combine_with_order' => $this->combine_with_order,
            'combine_with_shipping' => $this->combine_with_shipping,
            'channel_promotion' => $this->channel_promotion,
            'fecha_inicio_uso' => $this->fecha_inicio_uso,
            'hora_inicio_uso' => $this->hora_inicio_uso,
            'fecha_fin_uso' => $this->set_end_date ? $this->fecha_fin_uso : null,
            'hora_fin_uso' => $this->set_end_date ? $this->hora_fin_uso : null,
            'id_status_discount' => $this->id_status_discount,
            'selected_products' => json_encode($this->selected_products),
            'selected_collections' => json_encode($this->selected_collections),
            'selected_customers' => json_encode($this->selected_customers),
            'selected_segments' => json_encode($this->selected_segments),
            'applies_to' => $this->applies_to,
        ];

        try {
            if ($this->isEdit) {
                $discount = DiscountModel::findOrFail($this->discountId);
                $discount->update($data);
                session()->flash('message', 'Descuento actualizado exitosamente.');
            } else {
                $data['used_count'] = 0;
                DiscountModel::create($data);
                session()->flash('message', 'Descuento creado exitosamente.');
            }

            return redirect()->route('discounts');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al guardar el descuento: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $typeDiscounts = TypeDiscount::all();
        $methodDiscounts = MethodDiscount::all();
        $elegibilityDiscounts = ElegibilityDiscount::all();
        $requirementDiscounts = RequirementDiscount::all();
        $statusDiscounts = StatusDiscount::all();
        $markets = Markets::all();
        
        $products = Products::query()
            ->when($this->searchProducts, function($query) {
                $query->where('name', 'like', '%' . $this->searchProducts . '%');
            })
            ->limit(50)
            ->get();
            
        $collections = CollectionsPage::query()
            ->when($this->searchCollections, function($query) {
                $query->where('name', 'like', '%' . $this->searchCollections . '%');
            })
            ->limit(50)
            ->get();
            
        $customers = Customers::query()
            ->when($this->searchCustomers, function($query) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->searchCustomers . '%')
                      ->orWhere('email', 'like', '%' . $this->searchCustomers . '%');
                });
            })
            ->limit(50)
            ->get();
            
        // Obtener segmentos únicos con el conteo de clientes
        $segments = \DB::table('customer_segments')
            ->select('id', 'name', 'description', \DB::raw('COUNT(id_customer) as customers_count'))
            ->when($this->searchSegments, function($query) {
                $query->where('name', 'like', '%' . $this->searchSegments . '%');
            })
            ->whereNull('deleted_at')
            ->groupBy('name', 'description', 'id')
            ->orderBy('name')
            ->limit(50)
            ->get()
            ->unique('name')
            ->map(function($segment) {
                // Para cada segmento único, recalcular el conteo total
                $totalCustomers = \DB::table('customer_segments')
                    ->where('name', $segment->name)
                    ->whereNotNull('id_customer')
                    ->whereNull('deleted_at')
                    ->count();
                
                $segment->customers_count = $totalCustomers;
                return $segment;
            });

        return view('livewire.discount.create-discount', [
            'typeDiscounts' => $typeDiscounts,
            'methodDiscounts' => $methodDiscounts,
            'elegibilityDiscounts' => $elegibilityDiscounts,
            'requirementDiscounts' => $requirementDiscounts,
            'statusDiscounts' => $statusDiscounts,
            'markets' => $markets,
            'products' => $products,
            'collections' => $collections,
            'customers' => $customers,
            'segments' => $segments,
        ]);
    }
}
