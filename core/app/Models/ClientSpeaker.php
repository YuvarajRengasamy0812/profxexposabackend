<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientSpeaker extends Model
{
    use HasFactory;

    protected $table = 'client_speakers';

    protected $fillable = [
        'user_id',
        'email',
        'name',
        'designation',
        'company',
        'bio',
        'website',
        'linkedin',
        'instagram',
        'photo',
        'display_order',
        'status',
        'admin_message',
        'approved_by',
        'approved_at',
    ];
}
