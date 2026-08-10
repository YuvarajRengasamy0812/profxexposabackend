<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InfluencerRegister extends Model
{
    protected $table = 'influencer_registers';

    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'nationality',
        'company_name',
        'position_role',
        'password',
        'profile_photo',
        'referral_code',
        'referral_link',
        'submitted_referral_code',
        'referred_by_influencer_id',
        'status',
        'approval_message',
    ];

    protected $hidden = ['password'];
}
