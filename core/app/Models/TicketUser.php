<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketUser extends Model
{
   protected $fillable = [
    'ticket_id',
    'name',
    'email',
    'phone',
    'user_id',
    'id_name',
    'id_number'
];


    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
}
