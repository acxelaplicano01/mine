<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\Product\Products;
use App\Models\Customer\Customers;
use App\Models\Envio\Envios;
use App\Models\Market\Markets;
use App\Services\CustomerSegmentService;

class Orders extends Model
{
    use SoftDeletes;

    protected $table = 'orders';

    protected $fillable = [
        'user_id',
        'id_customer',
        'total_price',
        'subtotal_price',
        'id_market',
        'id_discount',
        'id_envio',
        'id_impuesto',
        'id_moneda',
        'id_condiciones_pago',
        'fecha_emision',
        'fecha_vencimiento',
        'note',
        'id_etiqueta',
        'id_status_prepared_order',
        'id_status_order',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'subtotal_price' => 'decimal:2',
    ];

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItems::class, 'order_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customers::class, 'id_customer');
    }

    public function statusOrder()
    {
        return $this->belongsTo(StatusOrder::class, 'id_status_order');
    }

    public function statusPreparedOrder()
    {
        return $this->belongsTo(StatusPreparedOrder::class, 'id_status_prepared_order');
    }

    public function envio()
    {
        return $this->belongsTo(Envios::class, 'id_envio');
    }

    public function market()
    {
        return $this->belongsTo(Markets::class, 'id_market');
    }

    /**
     * Boot method para registrar eventos del modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Actualizar segmentos automáticos cuando se crea una orden
        static::created(function ($order) {
            if ($order->id_customer) {
                CustomerSegmentService::updateCustomerSegments($order->id_customer);
            }

            // Reducir inventario automáticamente
            foreach ($order->items as $item) {
                try {
                    \App\Services\InventoryService::reduceStock(
                        $item->product_id,
                        $item->quantity,
                        $item->variant_id,
                        $order->id
                    );
                } catch (\Exception $e) {
                    \Log::error('Error al reducir inventario: ' . $e->getMessage());
                }
            }
        });

        // Actualizar segmentos automáticos cuando se elimina una orden
        static::deleted(function ($order) {
            if ($order->id_customer) {
                CustomerSegmentService::updateCustomerSegments($order->id_customer);
            }

            // Devolver inventario al eliminar pedido
            foreach ($order->items as $item) {
                try {
                    \App\Services\InventoryService::returnStock(
                        $item->product_id,
                        $item->quantity,
                        $item->variant_id,
                        $order->id
                    );
                } catch (\Exception $e) {
                    \Log::error('Error al devolver inventario: ' . $e->getMessage());
                }
            }
        });
    }
}
