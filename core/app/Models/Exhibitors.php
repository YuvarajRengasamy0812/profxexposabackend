<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exhibitors extends Model
{
    protected $table = 'exhibitors_registers';

    protected $fillable = [
        'full_name',
        'email',
        'company_name',
        'phone',
        'user_type',
        'nationality',
        'password',
        'special_requirements',
        'sponsor_package',
        'products_services',
    ];

    protected $hidden = ['password'];
}
