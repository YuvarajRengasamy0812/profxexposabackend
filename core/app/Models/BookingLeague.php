<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingLeague extends Model
{
    use HasFactory;

    protected $table = 'booking_leagues';

  protected $fillable = [
  'name',
    'email',
    'phone',
    'country',
    'company',
    'role',
    'referral_code',
    'referred_by_user_id',
    'referred_by_league_id',
    'referrer_name',
    'own_referral_code',
    'own_referral_link'
];



}
