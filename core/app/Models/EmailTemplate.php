<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    use HasFactory;
    const UPDATED_AT = null;

    protected $table = 'email_templates';
    
    protected $fillable = [
        'name',
        'template',
        'is_active',
        'created_at',
        'updated_at'
    ];
}
