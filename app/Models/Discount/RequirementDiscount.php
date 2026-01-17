<?php

namespace App\Models\Discount;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequirementDiscount extends Model
{
    use SoftDeletes;
    
    protected $table = 'requirement_discounts';

    protected $fillable = [
        'name',
        'description',
    ];

    public function discounts()
    {
        return $this->hasMany(Discounts::class, 'id_requirement_discount');
    }
}
