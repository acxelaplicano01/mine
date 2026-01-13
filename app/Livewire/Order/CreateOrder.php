<?php

namespace App\Livewire\Order;

use App\Models\Order\Drafts;
use App\Models\Order\DraftItems;
use Livewire\Component;
use App\Models\Product\Products;
use App\Models\Customer\Customers;
use App\Models\Market\Markets;
use App\Models\Money\Moneda;
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
    public $searchProduct = '';
    public $selectedProducts = [];
    public $tempSelectedProducts = [];
    public $searchFilter = 'todo';
    public $showPaymentTerms = false;
    public $payment_terms = 'reception';
    public $searchCustomer = '';
    public $noteText = '';
    public $notes = [];

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
        foreach ($this->selectedProducts as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        $this->subtotal_price = $subtotal;
        $tax = $this->subtotal_price * 0.12; // VAT 12%
        $this->total_price = $this->subtotal_price + $tax;
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
        $this->closeProductModal();
    }
    
    public function removeProduct($index)
    {
        unset($this->selectedProducts[$index]);
        $this->selectedProducts = array_values($this->selectedProducts);
        $this->calculateTotal();
    }
    
    public function updateQuantity($index, $quantity)
    {
        if (isset($this->selectedProducts[$index]) && $quantity > 0) {
            $this->selectedProducts[$index]['quantity'] = $quantity;
            $this->calculateTotal();
        }
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

        session()->flash('message', 'Pedido creado exitosamente con estado pendiente de pago.');

        return redirect()->route('orders');
    }

    public function markAsPaid()
    {
        $this->validate([
            'id_customer' => 'required|exists:customers,id',
            'selectedProducts' => 'required|array|min:1',
            'total_price' => 'required|numeric|min:0',
        ]);

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

        session()->flash('message', 'Pedido creado y marcado como pagado exitosamente.');

        return redirect()->route('orders');
    }

    public function cancel()
    {
        return redirect()->route('orders.index');
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

        return view('livewire.order.partials.createOrder', [
            'products' => Products::with('variants')->get(),
            'customers' => $customers,
            'markets' => Markets::with('moneda')->get(),
            'monedas' => Moneda::all(),
        ]);
    }
}
