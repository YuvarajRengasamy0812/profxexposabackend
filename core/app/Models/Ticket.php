<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $table = 'tickets';

    protected $fillable = [
        'ticket_type',
        'user_id',
        'refer_code',
        'refer_count'
    ];

    // Relationships
    public function users()
    {
        return $this->hasMany(TicketUser::class, 'ticket_id', 'id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class, 'ticket_id', 'id');
    }
}
