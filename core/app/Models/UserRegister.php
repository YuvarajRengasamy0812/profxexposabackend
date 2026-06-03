<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRegister extends Model
{
    protected $table = 'users_registers';

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
