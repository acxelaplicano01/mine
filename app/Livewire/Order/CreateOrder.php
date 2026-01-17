<?php

namespace App\Livewire\Order;

use App\Models\Order\Drafts;
use App\Models\Order\DraftItems;
use App\Models\Product\OrderPurchase\ConditionPay;
use Livewire\Component;
use App\Models\Product\Products;
use App\Models\Customer\Customers;
use App\Models\Market\Markets;
use App\Models\Money\Moneda;
use App\Models\Discount\Discounts;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.collapsable')]
class CreateOrder extends Component
{
    public $product_id;
    public $id_customer;
    public $quantity = 1;
    public $subtotal_price = 0;
    public $total_price = 0;
    public $note;
    public $id_status_order = 1;
    public $id_market;
    public $id_discount;
    public $id_etiqueta;
    public $id_moneda;
    
    public $showProductModal = false;
    public $showNoteModal = false;
    public $showDiscountModal = false;
    public $searchProduct = '';
    public $selectedProducts = [];
    public $tempSelectedProducts = [];
    public $searchFilter = 'todo';
    public $showPaymentTerms = false;
    public $payment_terms = 'reception';
    public $searchCustomer = '';
    public $noteText = '';
    public $notes = [];
    public $id_condiciones_pago;
    public $paymentDueDate;
    public $paymentTermName;
    public $selectedConditionType;
    public $fecha_emision;
    public $fecha_vencimiento;
    
    // Propiedades para descuentos
    public $searchDiscount = '';
    public $selectedDiscountCode = null;
    public $selectedManualDiscounts = [];
    public $applyAutomaticDiscounts = false;
    public $addCustomDiscount = false;
    public $customDiscountType = 'monto';
    public $customDiscountValue = 0;
    public $customDiscountReason = '';
    public $customDiscountVisibleToCustomer = true;
    public $discountAmount = 0;
    public $appliedAutomaticDiscounts = [];
    public $showDiscountSearch = false;
    
    // Propiedades para impuestos
    public $showTaxDropdown = false;
    public $chargeTaxes = true;

    public function mount()
    {
        $this->calculateTotal();
        
        // Buscar mercado de Honduras y establecerlo por defecto
        $hondurasMarket = Markets::where('name', 'like', '%Honduras%')
            ->orWhere('name', 'like', '%hondureño%')
            ->orWhere('name', 'like', '%hondureno%')
            ->first();
        
        // Si no encuentra Honduras, usar el mercado del usuario autenticado
        if (!$hondurasMarket) {
            $user = auth()->user();
            if ($user && $user->id_market) {
                $hondurasMarket = Markets::find($user->id_market);
            }
        }
        
        // Si el usuario no tiene mercado, usar el primer mercado disponible
        if (!$hondurasMarket) {
            $hondurasMarket = Markets::first();
        }
        
        if ($hondurasMarket) {
            $this->id_market = $hondurasMarket->id;
            $this->id_moneda = $hondurasMarket->id_moneda;
        }


    }

    public function updatedIdMarket($value)
    {
        // Actualizar moneda cuando cambia el mercado
        if ($value) {
            $market = Markets::find($value);
            if ($market && $market->id_moneda) {
                $this->id_moneda = $market->id_moneda;
            }
        }
        // Recalcular descuentos automáticos ya que pueden cambiar por mercado
        if ($this->applyAutomaticDiscounts) {
            $this->appliedAutomaticDiscounts = $this->getAutomaticDiscounts()->toArray();
            $this->applyDiscount();
        }
    }

    public function updatedIdCustomer($value)
    {
        // Validar descuentos manuales ya seleccionados
        if ($value && count($this->selectedManualDiscounts) > 0) {
            $invalidDiscounts = [];
            
            foreach ($this->selectedManualDiscounts as $index => $discount) {
                // Verificar límite de uso por cliente
                if (isset($discount['usage_per_customer']) && $discount['usage_per_customer']) {
                    $timesUsedByCustomer = \DB::table('order_discount')
                        ->join('orders', 'order_discount.order_id', '=', 'orders.id')
                        ->where('order_discount.discount_id', $discount['id'])
                        ->where('orders.id_customer', $value)
                        ->count();
                    
                    if ($timesUsedByCustomer >= $discount['usage_per_customer']) {
                        $invalidDiscounts[] = $discount['code_discount'];
                        unset($this->selectedManualDiscounts[$index]);
                    }
                }
            }
            
            // Reindexar el array
            $this->selectedManualDiscounts = array_values($this->selectedManualDiscounts);
            
            // Mostrar mensaje si se removieron descuentos
            if (count($invalidDiscounts) > 0) {
                session()->flash('warning', 'Los siguientes descuentos fueron removidos porque este cliente ya los utilizó: ' . implode(', ', $invalidDiscounts));
            }
        }
        
        // Recalcular descuentos automáticos ya que pueden cambiar por cliente
        if ($this->applyAutomaticDiscounts) {
            $this->appliedAutomaticDiscounts = $this->getAutomaticDiscounts()->toArray();
            $this->applyDiscount();
        }
    }

    public function updatedIdCondicionesPago($value)
    {
        if ($value) {
            $condicion = ConditionPay::find($value);
            if ($condicion) {
                $this->selectedConditionType = $condicion->type;
                $this->paymentTermName = $condicion->nombre_condicion;
                
                // Si es fecha fija, no establecer fechas automáticamente
                if ($condicion->type === 'fecha_fija') {
                    $this->fecha_emision = null;
                    $this->fecha_vencimiento = null;
                    $this->paymentDueDate = null;
                } 
                // Si es reception o envio, mostrar descripción en lugar de fecha
                elseif ($condicion->type === 'reception' || $condicion->type === 'envio') {
                    $this->fecha_emision = now()->format('Y-m-d');
                    $this->fecha_vencimiento = now()->format('Y-m-d');
                    $this->paymentDueDate = $condicion->descripcion_condicion;
                }
                else {
                    // Para neto_dias, establecer fecha de emisión como hoy y calcular vencimiento
                    $this->fecha_emision = now()->format('Y-m-d');
                    $this->recalcularFechaVencimiento();
                }
            }
        } else {
            $this->paymentDueDate = null;
            $this->paymentTermName = null;
            $this->selectedConditionType = null;
            $this->fecha_emision = null;
            $this->fecha_vencimiento = null;
        }
    }

    public function updatedFechaEmision()
    {
        // Solo recalcular si no es fecha fija
        if ($this->selectedConditionType !== 'fecha_fija') {
            $this->recalcularFechaVencimiento();
        }
    }

    public function updatedFechaVencimiento()
    {
        // Cuando el usuario cambia manualmente la fecha de vencimiento, actualizar el texto
        if ($this->fecha_vencimiento) {
            $fechaVencimiento = \Carbon\Carbon::parse($this->fecha_vencimiento);
            setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'Spanish_Spain', 'Spanish');
            $this->paymentDueDate = strftime('%e de %B de %Y', $fechaVencimiento->timestamp);
            
            // Si es fecha fija, establecer fecha de emisión como hoy
            if ($this->selectedConditionType === 'fecha_fija') {
                $this->fecha_emision = now()->format('Y-m-d');
            }
        }
    }

    private function recalcularFechaVencimiento()
    {
        if ($this->fecha_emision && $this->id_condiciones_pago) {
            $condicion = ConditionPay::find($this->id_condiciones_pago);
            if ($condicion) {
                $fechaEmision = \Carbon\Carbon::parse($this->fecha_emision);
                $fechaVencimiento = $fechaEmision->copy()->addDays($condicion->dias_vencimiento);
                $this->fecha_vencimiento = $fechaVencimiento->format('Y-m-d');
                
                // Formatear para mostrar
                setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'Spanish_Spain', 'Spanish');
                $this->paymentDueDate = strftime('%e de %B de %Y', $fechaVencimiento->timestamp);
            }
        }
    }

    public function updatedProductId()
    {
        $this->calculateTotal();
    }

    public function updatedQuantity()
    {
        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        $subtotal = 0;
        // Calcular cantidad total de artículos
        $this->quantity = array_sum(array_column($this->selectedProducts, 'quantity'));

        // Calcular subtotal con descuentos aplicados a nivel de producto
        foreach ($this->selectedProducts as $item) {
            $itemSubtotal = $item['price'] * $item['quantity'];
            // Aplicar descuentos automáticos y manuales a este producto
            $itemDiscount = $this->getProductDiscount($item);
            $itemSubtotal -= $itemDiscount;
            $subtotal += $itemSubtotal;
        }

        $this->subtotal_price = $subtotal;

        // Solo aplicar descuento personalizado al total
        $customDiscount = 0;
        if ($this->addCustomDiscount && $this->customDiscountValue > 0) {
            if ($this->customDiscountType === 'porcentaje') {
                $customDiscount = ($this->subtotal_price * $this->customDiscountValue) / 100;
            } else {
                $customDiscount = $this->customDiscountValue;
            }
        }

        $this->discountAmount = $customDiscount;
        $subtotalAfterDiscount = $this->subtotal_price - $customDiscount;

        // Aplicar impuestos solo si chargeTaxes es true
        $tax = $this->chargeTaxes ? ($subtotalAfterDiscount * 0.12) : 0; // VAT 12%
        $this->total_price = $subtotalAfterDiscount + $tax;
    }

    protected function getProductDiscount($item)
    {
        $totalDiscount = 0;
        
        // Aplicar descuentos automáticos
        if ($this->applyAutomaticDiscounts && count($this->appliedAutomaticDiscounts) > 0) {
            foreach ($this->appliedAutomaticDiscounts as $discount) {
                if ($this->discountAppliesToProduct($discount, $item)) {
                    $itemSubtotal = $item['price'] * $item['quantity'];
                    
                    if ($discount['discount_value_type'] === 'percentage') {
                        $totalDiscount += ($itemSubtotal * $discount['valor_discount']) / 100;
                    } else {
                        // Para descuentos de monto fijo, distribuirlo proporcionalmente
                        $totalDiscount += $discount['valor_discount'] / count($this->selectedProducts);
                    }
                }
            }
        }
        
        // Aplicar descuentos manuales seleccionados
        if (count($this->selectedManualDiscounts) > 0) {
            foreach ($this->selectedManualDiscounts as $discount) {
                if ($this->discountAppliesToProduct($discount, $item)) {
                    $itemSubtotal = $item['price'] * $item['quantity'];
                    
                    if ($discount['discount_value_type'] === 'percentage') {
                        $totalDiscount += ($itemSubtotal * $discount['valor_discount']) / 100;
                    } else {
                        // Para descuentos de monto fijo, distribuirlo proporcionalmente
                        $totalDiscount += $discount['valor_discount'] / count($this->selectedProducts);
                    }
                }
            }
        }
        
        return $totalDiscount;
    }
    
    protected function discountAppliesToProduct($discount, $item)
    {
        // Si es descuento de pedido o envío gratis, no aplica a nivel de producto
        if (isset($discount['id_type_discount']) && in_array($discount['id_type_discount'], [3, 4])) {
            return false;
        }
        
        // Si tiene producto específico, verificar coincidencia
        if (isset($discount['id_product']) && $discount['id_product']) {
            return $item['id'] == $discount['id_product'];
        }
        
        // Si tiene colección, verificar si el producto pertenece a esa colección
        if (isset($discount['id_collection']) && $discount['id_collection']) {
            $product = \App\Models\Product\Products::find($item['id']);
            return $product && $product->id_collection == $discount['id_collection'];
        }
        
        // Si no tiene restricciones de producto/colección, aplica a todos
        return true;
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
    
    public function openNoteModal()
    {
        $this->showNoteModal = true;
        $this->noteText = '';
    }
    
    public function closeNoteModal()
    {
        $this->showNoteModal = false;
        $this->noteText = '';
    }
    
    public function addNote()
    {
        if (trim($this->noteText) !== '') {
            $this->notes[] = [
                'id' => uniqid(),
                'text' => $this->noteText,
                'created_at' => now()->format('d/m/Y H:i')
            ];
            $this->closeNoteModal();
        }
    }
    
    public function removeNote($noteId)
    {
        $this->notes = array_filter($this->notes, function($note) use ($noteId) {
            return $note['id'] !== $noteId;
        });
        $this->notes = array_values($this->notes);
    }

    public function removeCustomer()
    {
        $this->id_customer = null;;
    }
    
    public function toggleProductSelection($productId, $productName, $productPrice, $stock)
    {
        $key = 'product_' . $productId;
        
        if (isset($this->tempSelectedProducts[$key])) {
            unset($this->tempSelectedProducts[$key]);
        } else {
            $this->tempSelectedProducts[$key] = [
                'id' => $productId,
                'variant_id' => null,
                'name' => $productName,
                'price' => $productPrice,
                'stock' => $stock,
                'quantity' => 1,
            ];
        }
    }
    
    public function toggleVariantSelection($productId, $variantId, $productName, $variantName, $variantPrice, $stock)
    {
        $key = 'variant_' . $variantId;
        
        if (isset($this->tempSelectedProducts[$key])) {
            unset($this->tempSelectedProducts[$key]);
        } else {
            $this->tempSelectedProducts[$key] = [
                'id' => $productId,
                'variant_id' => $variantId,
                'name' => $productName . ' - ' . $variantName,
                'price' => $variantPrice,
                'stock' => $stock,
                'quantity' => 1,
            ];
        }
    }

    public function toggleAllVariants($productId, $productName)
    {
        // Cargar el producto con sus variantes
        $product = Products::with('variants')->find($productId);
        
        if (!$product || !$product->variants) {
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

        // Si todas están seleccionadas, deseleccionar todas
        if ($allSelected) {
            foreach ($product->variants as $variant) {
                $key = 'variant_' . $variant->id;
                unset($this->tempSelectedProducts[$key]);
            }
        } else {
            // Si no todas están seleccionadas, seleccionar todas
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
                    'price' => $variant->price,
                    'stock' => $variant->cantidad_inventario,
                    'quantity' => 1,
                ];
            }
        }
    }
    
    public function addSelectedProducts()
    {
        foreach ($this->tempSelectedProducts as $product) {
            $exists = false;
            foreach ($this->selectedProducts as $key => $item) {
                // Comparar por product_id Y variant_id para distinguir variantes diferentes
                if ($item['id'] == $product['id'] && $item['variant_id'] == $product['variant_id']) {
                    $this->selectedProducts[$key]['quantity']++;
                    $exists = true;
                    break;
                }
            }
            
            if (!$exists) {
                $this->selectedProducts[] = $product;
            }
        }
        
        $this->calculateTotal();
        // Recalcular descuentos automáticos si el checkbox está marcado
        if ($this->applyAutomaticDiscounts) {
            $this->appliedAutomaticDiscounts = $this->getAutomaticDiscounts()->toArray();
        }
        $this->applyDiscount(); // Recalcular descuentos después de agregar productos
        $this->closeProductModal();
    }
    
    public function removeProduct($index)
    {
        unset($this->selectedProducts[$index]);
        $this->selectedProducts = array_values($this->selectedProducts);
        $this->calculateTotal();
        // Recalcular descuentos automáticos si el checkbox está marcado
        if ($this->applyAutomaticDiscounts) {
            $this->appliedAutomaticDiscounts = $this->getAutomaticDiscounts()->toArray();
        }
        $this->applyDiscount(); // Recalcular descuentos después de eliminar productos
    }
    
    public function updateQuantity($index, $quantity)
    {
        if (isset($this->selectedProducts[$index]) && $quantity > 0) {
            $this->selectedProducts[$index]['quantity'] = $quantity;
            $this->calculateTotal();
            // Recalcular descuentos automáticos si el checkbox está marcado
            if ($this->applyAutomaticDiscounts) {
                $this->appliedAutomaticDiscounts = $this->getAutomaticDiscounts()->toArray();
            }
            $this->applyDiscount(); // Recalcular descuentos después de actualizar cantidad
        }
    }

    // Métodos para manejar el modal de descuentos
    public function openDiscountModal()
    {
        $this->resetDiscountForm();
        $this->showDiscountModal = true;
    }

    public function closeDiscountModal()
    {
        // Solo cerrar el modal sin resetear los descuentos aplicados
        $this->showDiscountModal = false;
    }

    public function cancelDiscountModal()
    {
        // Cerrar el modal y resetear todos los cambios
        $this->showDiscountModal = false;
        $this->resetDiscountForm();
    }

    public function resetDiscountForm()
    {
        $this->searchDiscount = '';
        $this->selectedDiscountCode = null;
        $this->selectedManualDiscounts = [];
        $this->applyAutomaticDiscounts = false;
        $this->addCustomDiscount = false;
        $this->customDiscountType = 'monto';
        $this->customDiscountValue = 0;
        $this->customDiscountReason = '';
        $this->customDiscountVisibleToCustomer = true;
        $this->appliedAutomaticDiscounts = [];
        $this->showDiscountSearch = false;
    }

    public function selectDiscountCode($discountCode)
    {
        if (!$discountCode) {
            return;
        }
        
        // Buscar el descuento completo
        $discount = Discounts::where('code_discount', $discountCode)
            ->where('id_status_discount', 1)
            ->first();
        
        if ($discount) {
            // Verificar límite de uso por cliente
            if ($discount->usage_per_customer && $this->id_customer) {
                $timesUsedByCustomer = \DB::table('order_discount')
                    ->join('orders', 'order_discount.order_id', '=', 'orders.id')
                    ->where('order_discount.discount_id', $discount->id)
                    ->where('orders.id_customer', $this->id_customer)
                    ->count();
                
                if ($timesUsedByCustomer >= $discount->usage_per_customer) {
                    session()->flash('error', 'Este descuento ya ha sido utilizado por este cliente. Límite: ' . $discount->usage_per_customer . ' uso(s) por cliente.');
                    $this->selectedDiscountCode = null;
                    return;
                }
            }
            
            // Verificar límite total de usos
            if ($discount->usage_limit && $discount->used_count >= $discount->usage_limit) {
                session()->flash('error', 'Este descuento ha alcanzado su límite máximo de usos.');
                $this->selectedDiscountCode = null;
                return;
            }
            
            // Verificar que no esté ya en la lista
            $exists = collect($this->selectedManualDiscounts)->contains('code_discount', $discountCode);
            
            if (!$exists) {
                $this->selectedManualDiscounts[] = $discount->toArray();
            }
        }
        
        // Resetear el select
        $this->selectedDiscountCode = null;
        // No llamar a applyDiscount aquí, esperar a que el usuario presione "Listo"
    }

    public function removeSelectedDiscountCode($discountCode)
    {
        $this->selectedManualDiscounts = array_filter(
            $this->selectedManualDiscounts,
            fn($d) => $d['code_discount'] !== $discountCode
        );
        $this->selectedManualDiscounts = array_values($this->selectedManualDiscounts);
        // No llamar a applyDiscount aquí, esperar a que el usuario presione "Listo"
    }

    public function updatedApplyAutomaticDiscounts($value)
    {
        if ($value) {
            // Cuando se marca, obtener y aplicar descuentos automáticos
            // Solo si hay productos seleccionados
            if (count($this->selectedProducts) > 0) {
                $this->appliedAutomaticDiscounts = $this->getAutomaticDiscounts()->toArray();
                // No llamar a applyDiscount aquí, esperar a que el usuario presione "Listo"
            } else {
                $this->appliedAutomaticDiscounts = [];
            }
        } else {
            // Si se desmarca, limpiar descuentos automáticos
            $this->appliedAutomaticDiscounts = [];
            $this->discountAmount = 0;
            $this->calculateTotal();
        }
    }

    public function applyDiscount()
    {
        // Recalcular el total con los descuentos seleccionados
        $this->calculateTotal();
        
        // Forzar actualización del componente para que el blade recalcule los descuentos inline
        $this->dispatch('$refresh');
        
        // Cerrar el modal después de aplicar
        $this->closeDiscountModal();
    }

    public function removeDiscount()
    {
        $this->discountAmount = 0;
        $this->id_discount = null;
        $this->calculateTotal();
    }

    public function removeAllDiscounts()
    {
        // Limpiar descuentos manuales
        $this->selectedManualDiscounts = [];
        
        // Deshabilitar descuentos automáticos
        $this->applyAutomaticDiscounts = false;
        $this->appliedAutomaticDiscounts = [];
        
        // Limpiar descuento personalizado
        $this->addCustomDiscount = false;
        $this->customDiscountValue = 0;
        $this->customDiscountType = 'percentage'; // Resetear al valor por defecto
        
        // Recalcular total
        $this->calculateTotal();
    }

    // Métodos para manejar el dropdown de impuestos
    public function toggleTaxDropdown()
    {
        $this->showTaxDropdown = !$this->showTaxDropdown;
    }

    public function closeTaxDropdown()
    {
        $this->showTaxDropdown = false;
    }

    public function applyTaxSettings()
    {
        $this->calculateTotal();
        $this->closeTaxDropdown();
    }

    public function saveDraft()
    {
        $this->validate([
            'id_customer' => 'required|exists:customers,id',
            'selectedProducts' => 'required|array|min:1',
        ]);

        // Crear el borrador principal
        $draft = Drafts::create([
            'user_id' => auth()->id(),
            'id_customer' => $this->id_customer,
            'subtotal_price' => $this->subtotal_price,
            'total_price' => $this->total_price,
            'note' => $this->note,
            'id_status_order' => $this->id_status_order,
            'id_market' => $this->id_market,
            'id_moneda' => $this->id_moneda,
            'id_condiciones_pago' => $this->id_condiciones_pago,
            'fecha_emision' => $this->fecha_emision,
            'fecha_vencimiento' => $this->fecha_vencimiento,
        ]);

        // Crear los items del borrador
        foreach ($this->selectedProducts as $item) {
            DraftItems::create([
                'draft_id' => $draft->id,
                'product_id' => $item['id'],
                'variant_id' => $item['variant_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'subtotal' => $item['price'] * $item['quantity'],
            ]);
        }

        session()->flash('message', 'Borrador guardado exitosamente.');

        return redirect()->route('orders');
    }

    public function store()
    {
        $this->validate([
            'id_customer' => 'required|exists:customers,id',
            'selectedProducts' => 'required|array|min:1',
            'total_price' => 'required|numeric|min:0',
        ]);

        try {
            // Crear el pedido principal con estado pendiente de pago (2)
            $order = \App\Models\Order\Orders::create([
                'user_id' => auth()->id(),
                'id_customer' => $this->id_customer,
                'subtotal_price' => $this->subtotal_price,
                'total_price' => $this->total_price,
                'note' => $this->note,
                'id_status_order' => 2, // Pendiente de pago
                'id_market' => $this->id_market,
                'id_moneda' => $this->id_moneda,
                'id_condiciones_pago' => $this->id_condiciones_pago,
                'fecha_emision' => $this->fecha_emision,
                'fecha_vencimiento' => $this->fecha_vencimiento,
            ]);

            // Crear los items del pedido
            foreach ($this->selectedProducts as $item) {
                \App\Models\Order\OrderItems::create([
                    'order_id' => $order->id,
                    'product_id' => $item['id'],
                    'variant_id' => $item['variant_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['price'] * $item['quantity'],
                ]);
            }

            // Registrar descuentos aplicados y actualizar contador
            $this->registerDiscounts($order);

            session()->flash('message', 'Pedido creado exitosamente con estado pendiente de pago.');

            return redirect()->route('orders');
            
        } catch (\Exception $e) {
            \Log::error('Error al crear pedido: ' . $e->getMessage());
            session()->flash('error', 'Error al crear el pedido: ' . $e->getMessage());
            return;
        }
    }

    public function markAsPaid()
    {
        $this->validate([
            'id_customer' => 'required|exists:customers,id',
            'selectedProducts' => 'required|array|min:1',
            'total_price' => 'required|numeric|min:0',
        ]);

        try {
            // Crear el pedido principal con estado pagado (1)
            $order = \App\Models\Order\Orders::create([
                'user_id' => auth()->id(),
                'id_customer' => $this->id_customer,
                'subtotal_price' => $this->subtotal_price,
                'total_price' => $this->total_price,
                'note' => $this->note,
                'id_status_order' => 1, // Pagado
                'id_market' => $this->id_market,
                'id_moneda' => $this->id_moneda,
                'id_condiciones_pago' => $this->id_condiciones_pago,
                'fecha_emision' => $this->fecha_emision,
                'fecha_vencimiento' => $this->fecha_vencimiento,
            ]);

            // Crear los items del pedido
            foreach ($this->selectedProducts as $item) {
                \App\Models\Order\OrderItems::create([
                    'order_id' => $order->id,
                    'product_id' => $item['id'],
                    'variant_id' => $item['variant_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['price'] * $item['quantity'],
                ]);
            }

            // Registrar descuentos aplicados y actualizar contador
            $this->registerDiscounts($order);

            session()->flash('message', 'Pedido creado y marcado como pagado exitosamente.');

            return redirect()->route('orders');
            
        } catch (\Exception $e) {
            \Log::error('Error al crear pedido como pagado: ' . $e->getMessage());
            session()->flash('error', 'Error al crear el pedido: ' . $e->getMessage());
            return;
        }
    }

    public function cancel()
    {
        return redirect()->route('orders.index');
    }

    protected function getAutomaticDiscounts()
    {
        $discounts = Discounts::query()
            ->when($this->id_market, function($query) {
                $query->where('id_market', $this->id_market);
            })
            ->where('id_status_discount', 1) // Solo activos
            // Filtrar por método automático (id_method_discount = 2)
            ->where('id_method_discount', 2)
            // Filtrar por fechas de validez
            ->where(function($query) {
                $query->whereNull('fecha_inicio_uso')
                      ->orWhere('fecha_inicio_uso', '<=', now());
            })
            ->where(function($query) {
                $query->whereNull('fecha_fin_uso')
                      ->orWhere('fecha_fin_uso', '>=', now());
            })
            ->get();

        // Filtrar manualmente por elegibilidad (requiere decodificar JSON)
        $discounts = $discounts->filter(function($discount) {
            // Elegibilidad 1: Todos los clientes
            if ($discount->id_elegibility_discount == 1) {
                return true;
            }
            
            // Elegibilidad 2: Segmentos de clientes específicos
            if ($discount->id_elegibility_discount == 2 && $this->id_customer) {
                $selectedSegments = is_string($discount->selected_segments) 
                    ? json_decode($discount->selected_segments, true) 
                    : $discount->selected_segments;
                
                if (!empty($selectedSegments)) {
                    $discountSegmentIds = array_column($selectedSegments, 'id');
                    
                    // Verificar si el cliente pertenece a alguno de los segmentos del descuento
                    $customerInSegment = \DB::table('customer_segments')
                        ->whereIn('id', $discountSegmentIds)
                        ->where('id_customer', $this->id_customer)
                        ->exists();
                    
                    if ($customerInSegment) {
                        return true;
                    }
                }
                return false;
            }
            
            // Elegibilidad 3: Clientes específicos
            if ($discount->id_elegibility_discount == 3 && $this->id_customer) {
                $selectedCustomers = is_string($discount->selected_customers) 
                    ? json_decode($discount->selected_customers, true) 
                    : $discount->selected_customers;
                
                if (!empty($selectedCustomers)) {
                    $discountCustomerIds = array_column($selectedCustomers, 'id');
                    return in_array($this->id_customer, $discountCustomerIds);
                }
                return false;
            }
            
            return false;
        });

        // Filtrar por requisitos mínimos de compra
        $discounts = $discounts->filter(function($discount) {
            // Requisito 1: Sin requisitos mínimos
            if ($discount->id_requirement_discount == 1) {
                return true;
            }
            
            // Requisito 2: Cantidad mínima de productos
            if ($discount->id_requirement_discount == 2) {
                $totalQuantity = array_sum(array_column($this->selectedProducts, 'quantity'));
                return $discount->minimum_quantity && $totalQuantity >= $discount->minimum_quantity;
            }
            
            // Requisito 3: Monto mínimo de compra
            if ($discount->id_requirement_discount == 3) {
                return $discount->minimum_purchase_amount && $this->subtotal_price >= $discount->minimum_purchase_amount;
            }
            
            return false;
        });

        // Filtrar descuentos que apliquen a los productos o colecciones del pedido
        $validDiscounts = [];
        
        foreach ($discounts as $discount) {
            // Verificar límite de uso por cliente
            if ($discount->usage_per_customer && $this->id_customer) {
                $timesUsedByCustomer = \DB::table('order_discount')
                    ->join('orders', 'order_discount.order_id', '=', 'orders.id')
                    ->where('order_discount.discount_id', $discount->id)
                    ->where('orders.id_customer', $this->id_customer)
                    ->count();
                
                if ($timesUsedByCustomer >= $discount->usage_per_customer) {
                    continue; // Saltar este descuento
                }
            }
            
            // Verificar límite total de usos
            if ($discount->usage_limit && $discount->used_count >= $discount->usage_limit) {
                continue; // Saltar este descuento
            }
            
            // id_type_discount: 1 = Descuento en productos específicos
            if ($discount->id_type_discount == 1) {
                // Verificar si algún producto del pedido coincide
                if ($discount->id_product) {
                    $hasProduct = collect($this->selectedProducts)
                        ->contains('id', $discount->id_product);
                    
                    if ($hasProduct) {
                        $validDiscounts[] = $discount;
                    }
                }
                // Si tiene colección, verificar si algún producto pertenece a esa colección
                elseif ($discount->id_collection) {
                    $hasProductInCollection = collect($this->selectedProducts)
                        ->map(function($item) {
                            $product = \App\Models\Product\Products::find($item['id']);
                            return $product ? $product->id_collection : null;
                        })
                        ->contains($discount->id_collection);
                    
                    if ($hasProductInCollection) {
                        $validDiscounts[] = $discount;
                    }
                }
                // Si no tiene producto ni colección específica, aplica a todos los productos
                else {
                    $validDiscounts[] = $discount;
                }
            }
            // id_type_discount: 3 = Descuento en el pedido (no requiere validación de productos)
            elseif ($discount->id_type_discount == 3) {
                $validDiscounts[] = $discount;
            }
            // id_type_discount: 4 = Envío gratis (no requiere validación de productos)
            elseif ($discount->id_type_discount == 4) {
                $validDiscounts[] = $discount;
            }
        }

        return collect($validDiscounts);
    }

    /**
     * Registrar los descuentos aplicados al pedido y actualizar contador de uso
     */
    protected function registerDiscounts($order)
    {
        // Registrar descuentos manuales
        foreach ($this->selectedManualDiscounts as $discount) {
            \DB::table('order_discount')->insert([
                'order_id' => $order->id,
                'discount_id' => $discount['id'],
                'discount_code' => $discount['code_discount'],
                'discount_amount' => $this->calculateDiscountAmount($discount),
                'discount_type' => 'manual',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Incrementar contador de uso
            Discounts::where('id', $discount['id'])->increment('used_count');
        }

        // Registrar descuentos automáticos
        if ($this->applyAutomaticDiscounts) {
            foreach ($this->appliedAutomaticDiscounts as $discount) {
                \DB::table('order_discount')->insert([
                    'order_id' => $order->id,
                    'discount_id' => $discount['id'],
                    'discount_code' => $discount['code_discount'],
                    'discount_amount' => $this->calculateDiscountAmount($discount),
                    'discount_type' => 'automatic',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Incrementar contador de uso
                Discounts::where('id', $discount['id'])->increment('used_count');
            }
        }

        // Registrar descuento personalizado
        if ($this->addCustomDiscount && $this->customDiscountValue > 0) {
            // Crear registro de descuento personalizado
            $customDiscount = Discounts::create([
                'code_discount' => 'CUSTOM-' . $order->id . '-' . now()->timestamp,
                'description' => $this->customDiscountReason ?: 'Descuento personalizado',
                'valor_discount' => $this->customDiscountValue,
                'discount_value_type' => $this->customDiscountType === 'porcentaje' ? 'percentage' : 'fixed',
                'id_market' => $this->id_market,
                'id_type_discount' => 3, // Tipo pedido
                'id_method_discount' => 1, // Método código
                'id_status_discount' => 1, // Activo
                'used_count' => 1,
            ]);

            \DB::table('order_discount')->insert([
                'order_id' => $order->id,
                'discount_id' => $customDiscount->id,
                'discount_code' => $customDiscount->code_discount,
                'discount_amount' => $this->customDiscountType === 'porcentaje' 
                    ? ($this->subtotal_price * $this->customDiscountValue / 100)
                    : $this->customDiscountValue,
                'discount_type' => 'custom',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Calcular el monto del descuento aplicado
     */
    protected function calculateDiscountAmount($discount)
    {
        if ($discount['discount_value_type'] === 'percentage') {
            return ($this->subtotal_price * $discount['valor_discount']) / 100;
        } else {
            return $discount['valor_discount'];
        }
    }

    public function render()
    {
        // Filtrar clientes por búsqueda
        $customers = Customers::query()
            ->when($this->searchCustomer, function($query) {
                $query->where('name', 'like', '%' . $this->searchCustomer . '%')
                      ->orWhere('email', 'like', '%' . $this->searchCustomer . '%');
            })
            ->limit(50)
            ->get();

        // Obtener todos los descuentos disponibles para el select (solo manuales, no automáticos)
        $availableDiscounts = Discounts::query()
            ->when($this->id_market, function($query) {
                $query->where('id_market', $this->id_market);
            })
            ->where('id_status_discount', 1) // Solo activos
            ->where('id_method_discount', '!=', 2) // Excluir automáticos (id_method_discount = 2)
            ->get();

        // Verificar si existen descuentos en el mercado (para mostrar el mensaje correcto)
        $hasDiscounts = Discounts::query()
            ->when($this->id_market, function($query) {
                $query->where('id_market', $this->id_market);
            })
            ->where('id_status_discount', 1)
            ->exists();

        return view('livewire.order.partials.createOrder', [
            'products' => Products::with('variants')->get(),
            'customers' => $customers,
            'markets' => Markets::with('moneda')->get(),
            'monedas' => Moneda::all(),
            'condicion_pago' => ConditionPay::all(),
            'availableDiscounts' => $availableDiscounts,
            'hasDiscounts' => $hasDiscounts,
        ]);
    }
}
