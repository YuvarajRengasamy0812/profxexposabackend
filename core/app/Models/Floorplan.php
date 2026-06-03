<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Floorplan extends Model
{
    use HasFactory;

    protected $table = 'floorplans';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'company',
        'referal_code',
        'boothno',
        'boothtitle',
        'boothsize',
        'boothammount',
        'paymenttype',
        'file',
        'networktype'
    ];
}
