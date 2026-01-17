<?php

namespace App\Models\PointSale\Employee;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $table = 'employees';
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'position',
        'id_branch',
        'id_status_employee',
    ];
}
