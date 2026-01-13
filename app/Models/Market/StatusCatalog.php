<?php

namespace App\Models\Market;

use Illuminate\Database\Eloquent\Model;

class StatusCatalog extends Model
{
    protected $table = 'status_catalogs';

    protected $fillable = [
        'name',
        'description',
    ];

    protected $hidden = [
        'created_by',
        'deleted_by',
        'updated_at',
        'deleted_at'
    ];
}
