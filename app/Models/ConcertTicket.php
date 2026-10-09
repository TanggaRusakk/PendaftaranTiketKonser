<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConcertTicket extends Model
{
    protected $fillable = [
        'booking_code',
        'full_name',
        'email',
        'phone',
        'identity_number',
        'ticket_tier',
        'ticket_price',
        'quantity',
        'subtotal',
        'tax_amount',
        'admin_fee',
        'total_price',
        'payment_method',
        'status',
        'notes',
    ];

    protected $casts = [
        'ticket_price' => 'integer',
        'quantity' => 'integer',
        'subtotal' => 'integer',
        'tax_amount' => 'integer',
        'admin_fee' => 'integer',
        'total_price' => 'integer',
    ];
}
