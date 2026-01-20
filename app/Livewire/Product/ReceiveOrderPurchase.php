<?php

namespace App\Livewire\Product;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Product\OrderPurchase\OrderPurchases;

#[Layout('components.layouts.collapsable')]
class ReceiveOrderPurchase extends Component
{
    public $orderId;
    public $order;
    public $productos = [];
    
    // Totales
    public $total_recibido = 0;
    public $total_orden = 0;
    
    public function mount($id)
    {
        $this->orderId = $id;
        $this->order = OrderPurchases::with(['distribuidor'])->findOrFail($id);
        
        // Verificar que la orden esté en estado solicitado
        if ($this->order->estado !== 'solicitado') {
            session()->flash('error', 'Solo se puede recibir inventario de órdenes en estado solicitado');
            return redirect()->route('orders_purchases');
        }
        
        // Cargar productos de la orden
        $this->productos = $this->order->productos ?? [];
        $this->total_orden = $this->order->total;
        
        // Inicializar cantidad recibida para cada producto
        foreach ($this->productos as $index => $producto) {
            $this->productos[$index]['cantidad_recibida'] = 0;
        }
    }
    
    public function updateCantidadRecibida($index, $cantidad)
    {
        if (isset($this->productos[$index])) {
            $this->productos[$index]['cantidad_recibida'] = max(0, intval($cantidad));
            $this->calculateTotal();
        }
    }
    
    private function calculateTotal()
    {
        $this->total_recibido = 0;
        
        foreach ($this->productos as $producto) {
            $cantidadRecibida = $producto['cantidad_recibida'] ?? 0;
            $costo = $producto['costo'] ?? 0;
            $this->total_recibido += $cantidadRecibida * $costo;
        }
    }
    
    public function recibirInventario()
    {
        // Validar que al menos un producto tenga cantidad recibida
        $hasReceived = false;
        foreach ($this->productos as $producto) {
            if (($producto['cantidad_recibida'] ?? 0) > 0) {
                $hasReceived = true;
                break;
            }
        }
        
        if (!$hasReceived) {
            session()->flash('error', 'Debes recibir al menos un producto');
            return;
        }
        
        // Aquí iría la lógica para actualizar el inventario
        // Por ahora solo actualizamos el estado de la orden
        
        $this->order->update([
            'estado' => 'recibido',
            'productos' => $this->productos,
        ]);
        
        session()->flash('message', 'Inventario recibido exitosamente');
        return redirect()->route('orders_purchases');
    }
    
    public function cancel()
    {
        return redirect()->route('orders_purchases');
    }

    public function render()
    {
        return view('livewire.producto.order-purchase.receive-order-purchase');
    }
}
