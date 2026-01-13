<?php

namespace App\Models\Order;

use App\Models\Order\DraftItems;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\Customer\Customers;

class Drafts extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'id_customer',
        'subtotal_price',
        'total_price',
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
}
