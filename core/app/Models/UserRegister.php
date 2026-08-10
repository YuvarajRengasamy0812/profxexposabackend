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
        'profile_photo',
        'referral_code',
        'referral_link',
        'influencer_referral_code',
        'referred_by_influencer_id',
    ];

    protected $hidden = ['password'];
}
