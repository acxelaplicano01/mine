<?php

namespace App\Models\Market;

use Illuminate\Database\Eloquent\Model;
use App\Models\Money\Moneda;

class Markets extends Model
{
    protected $table = 'markets';

    protected $fillable = [
        'name',
        'description',
        'domain',
        'id_moneda',
        'id_catalogo',
        'id_pais',
        'id_tienda_online',
        'id_status_market',
    ];

    protected $hidden = [
        'created_by',
        'deleted_by',
        'updated_at',
        'deleted_at'
    ];

    public function moneda()
    {
        return $this->belongsTo(Moneda::class, 'id_moneda');
    }
}