<?php

namespace App\Livewire\Product;

use Livewire\Component;
use App\Services\InventoryService;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.collapsable')]
class LowStockAlerts extends Component
{
    public $showAll = false;
    public $limit = 5;

    public function toggleShowAll()
    {
        $this->showAll = !$this->showAll;
    }

    public function render()
    {
        $products = InventoryService::getLowStockProducts();
        
        if (!$this->showAll) {
            $products = $products->take($this->limit);
        }

        return view('livewire.producto.low-stock-alerts', [
            'lowStockProducts' => $products,
            'totalCount' => InventoryService::getLowStockProducts()->count(),
        ]);
    }
}
