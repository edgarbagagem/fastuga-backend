<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'status',
        'customer_id',
        'total_paid',
        'total_price',
        'date',
        'payment_reference',
        'payment_type',
        'delivered_by'
    ];
}
