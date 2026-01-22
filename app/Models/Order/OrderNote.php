<?php
namespace App\Models\Order;

use Illuminate\Database\Eloquent\Model;

class OrderNote extends Model
{
    protected $table = 'order_notes';
    protected $fillable = ['order_id', 'note'];

    public function order()
    {
        return $this->belongsTo(Orders::class, 'order_id');
    }
}
