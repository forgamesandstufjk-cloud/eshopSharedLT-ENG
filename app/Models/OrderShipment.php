<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderShipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'seller_id',
        'carrier',
        'package_size',
        'shipping_cents',
        'status',
        'tracking_number',
        'proof_path',
        'reimbursement_transfer_id',
        'refunded_at',
        'refund_id',
        'refund_amount_cents',
        'refund_reason',
        'seller_transfer_id',
        'seller_transfer_reversal_id',
        'seller_transfer_reversed_cents',
        'refunded_at',
        'refund_id',
        'refund_amount_cents',
        'refund_reason',
        'seller_transfer_id',
        'seller_transfer_reversal_id',
        'seller_transfer_reversed_cents',
    ];

    protected $casts = [
        'refunded_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
}
