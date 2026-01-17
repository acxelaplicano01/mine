<?php

namespace App\Models\Discount;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Discounts extends Model
{
    use SoftDeletes;
    
    protected $table = 'discounts';

    protected $fillable = [
        'code_discount',
        'description',
        'valor_discount',
        'discount_value_type',
        'amount',
        'usage_limit',
        'used_count',
        'id_market',
        'id_type_discount',
        'id_method_discount',
        'id_collection',
        'id_product',
        'id_customer',
        'una_vez_por_pedido',
        'id_elegibility_discount',
        'id_requirement_discount',
        'number_usage_max',
        'usage_per_customer',
        'fecha_inicio_uso',
        'hora_inicio_uso',
        'fecha_fin_uso',
        'hora_fin_uso',
        'accesible_channel_sales',
        'id_status_discount',
    ];

    protected $casts = [
        'accesible_channel_sales' => 'array',
        'fecha_inicio_uso' => 'datetime',
        'fecha_fin_uso' => 'datetime',
        'hora_inicio_uso' => 'datetime',
        'hora_fin_uso' => 'datetime',

    ];

    public function typeDiscount()
    {
        return $this->belongsTo(TypeDiscount::class, 'id_type_discount');
    }

    public function methodDiscount()
    {
        return $this->belongsTo(MethodDiscount::class, 'id_method_discount');
    }

    public function statusDiscount()
    {
        return $this->belongsTo(StatusDiscount::class, 'id_status_discount');
    }

    public function elegibilityDiscount()
    {
        return $this->belongsTo(ElegibilityDiscount::class, 'id_elegibility_discount');
    }

    public function requirementDiscount()
    {
        return $this->belongsTo(RequirementDiscount::class, 'id_requirement_discount');
    }
}
