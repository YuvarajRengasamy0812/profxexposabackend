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
        'networktype',
        'company_profile_name',
        'company_details',
        'company_url',
        'company_logo',
        'status',
        'approval_message',
        'approved_by',
        'booth_design',
        'booth_design_image'
    ];
}
