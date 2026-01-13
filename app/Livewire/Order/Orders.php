<?php

namespace App\Livewire\Order;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Order\Orders as OrderModel;
use App\Models\Product\Products;
use App\Models\Customer\Customers;
use App\Livewire\Traits\HasSavedViews;

#[Layout('components.layouts.collapsable')]
class Orders extends Component
{
    use WithPagination, HasSavedViews;

    // Propiedades del formulario
    public $orderId;
    public $user_id;
    public $product_id;
    public $id_customer;
    public $quantity = 1;
    public $total_price = 0;
    public $subtotal_price = 0;
    public $note;
    public $id_status_order;

    // Propiedades de control
    public $isEditing = false;
    public $showModal = false;
    public $showFilterDropdown = false;

    protected $rules = [
        'product_id' => 'required|exists:products,id',
        'id_customer' => 'required|exists:customers,id',
        'quantity' => 'required|integer|min:1',
        'total_price' => 'required|numeric|min:0',
        'note' => 'nullable|string',
    ];

    protected $messages = [
        'product_id.required' => 'El producto es obligatorio.',
        'id_customer.required' => 'El cliente es obligatorio.',
        'quantity.required' => 'La cantidad es obligatoria.',
        'quantity.min' => 'La cantidad debe ser al menos 1.',
        'total_price.required' => 'El precio total es obligatorio.',
    ];

    public function mount()
    {
        $this->user_id = auth()->id();
        $this->loadSavedViews();
    }
    
    /**
     * Define el tipo de vista para este componente
     */
    protected function getViewType(): string
    {
        return 'orders';
    }
    
    /**
     * Aplicar filtro específico a la consulta de orders
     */
    protected function applyFilterToQuery($query, $filter)
    {
        switch($filter['type']) {
            case 'estado_pago_pagado':
                $query->where('id_status_order', 1);
                break;
            case 'estado_pago_pendiente':
                $query->where('id_status_order', 2);
                break;
            case 'estado_pago_no_pagado':
                $query->where('id_status_order', '!=', 1);
                break;
            case 'estado_preparacion_no_preparado':
                $query->where('id_status_order', 2);
                break;
            case 'cliente':
                if(isset($filter['value'])) {
                    $query->where('id_customer', $filter['value']);
                }
                break;
            case 'producto':
                if(isset($filter['value'])) {
                    $query->whereHas('items', function($q) use ($filter) {
                        $q->where('product_id', $filter['value']);
                    });
                }
                break;
        }
        
        return $query;
    }
    
    public function toggleFilterDropdown()
    {
        $this->showFilterDropdown = !$this->showFilterDropdown;
        $this->filterSearch = '';
    }

    public function render()
    {
        $orders = OrderModel::with(['customer', 'items.product', 'items.variant', 'user', 'statusOrder', 'statusPreparedOrder', 'envio'])
            ->when($this->search, function($query) {
                $query->whereHas('customer', function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                })
                ->orWhereHas('items.product', function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                })
                ->orWhere('note', 'like', '%' . $this->search . '%');
            })
            ->when($this->activeFilter === 'no_pagados', function($query) {
                $query->where('id_status_order', '!=', 1);
            })
            ->when($this->activeFilter === 'no_preparados', function($query) {
                $query->where('id_status_order', 2);
            })
            ->when(count($this->activeFilters) > 0 && str_starts_with($this->activeFilter, 'custom_'), function($query) {
                $this->applySavedViewFilters($query);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $products = Products::all();
        $customers = Customers::all();

        return view('livewire.order.orders', [
            'orders' => $orders,
            'products' => $products,
            'customers' => $customers,
        ]);
    }

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
        $this->isEditing = false;
    }

    public function edit($id)
    {
        $order = OrderModel::findOrFail($id);
        
        $this->orderId = $order->id;
        $this->user_id = $order->user_id;
        $this->product_id = $order->product_id;
        $this->id_customer = $order->id_customer;
        $this->quantity = $order->quantity;
        $this->total_price = $order->total_price;
        $this->subtotal_price = $order->subtotal_price;
        $this->note = $order->note;
        $this->id_status_order = $order->id_status_order;
        
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function store()
    {
        $this->validate();

        OrderModel::create([
            'user_id' => $this->user_id,
            'product_id' => $this->product_id,
            'id_customer' => $this->id_customer,
            'quantity' => $this->quantity,
            'total_price' => $this->total_price,
            'subtotal_price' => $this->subtotal_price,
            'note' => $this->note,
            'id_status_order' => $this->id_status_order,
        ]);

        session()->flash('message', 'Pedido creado exitosamente');
        
        $this->resetForm();
        $this->showModal = false;
    }

    public function update()
    {
        $this->validate();

        $order = OrderModel::findOrFail($this->orderId);
        
        $order->update([
            'product_id' => $this->product_id,
            'id_customer' => $this->id_customer,
            'quantity' => $this->quantity,
            'total_price' => $this->total_price,
            'subtotal_price' => $this->subtotal_price,
            'note' => $this->note,
            'id_status_order' => $this->id_status_order,
        ]);

        session()->flash('message', 'Pedido actualizado exitosamente');

        $this->resetForm();
        $this->showModal = false;
    }

    public function delete($id)
    {
        $order = OrderModel::findOrFail($id);
        $order->delete();

        session()->flash('message', 'Pedido eliminado exitosamente');
    }

    public function cancel()
    {
        $this->resetForm();
        $this->showModal = false;
    }

    public function updatedProductId($value)
    {
        if ($value) {
            $product = Products::find($value);
            if ($product) {
                $this->calculateTotal();
            }
        }
    }

    public function updatedQuantity()
    {
        $this->calculateTotal();
    }

    private function calculateTotal()
    {
        if ($this->product_id && $this->quantity) {
            $product = Products::find($this->product_id);
            if ($product) {
                $this->subtotal_price = $product->price * $this->quantity;
                $this->total_price = $this->subtotal_price; // Aquí se pueden agregar impuestos, descuentos, etc.
            }
        }
    }

    private function resetForm()
    {
        $this->orderId = null;
        $this->product_id = null;
        $this->id_customer = null;
        $this->quantity = 1;
        $this->total_price = 0;
        $this->subtotal_price = 0;
        $this->note = null;
        $this->id_status_order = null;
        $this->isEditing = false;
        $this->resetErrorBag();
    }
}
