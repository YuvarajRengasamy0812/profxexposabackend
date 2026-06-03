<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    // This must match the actual table in DB
    protected $table = 'coupon_codes'; 

    protected $primaryKey = 'coupon_id';

    public $timestamps = true;

    protected $fillable = [
        'coupon_name',
        'percentage',
        'coupon_code',
        'status',
    ];
}
