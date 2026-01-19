<?php

namespace App\Models\Order;

use App\Models\Order\DraftItems;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\Customer\Customers;
use App\Models\Market\Markets;
use App\Models\Envio\Envios;

class Drafts extends Model
{
    use SoftDeletes;

    protected $table = 'drafts';

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
        return $this->hasMany(DraftItems::class, 'draft_id');
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
}
