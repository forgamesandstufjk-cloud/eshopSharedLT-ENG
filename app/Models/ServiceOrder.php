<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceOrder extends Model
{
    use HasFactory;

    public const STATUS_AGREED = 'agreed';
    public const STATUS_DAROMAS = 'daromas';
    public const STATUS_READY_TO_SHIP = 'ready_to_ship';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const COMPLETION_PLATFORM = 'platform';
    public const COMPLETION_PRIVATE = 'private';

    public const SHIPMENT_PENDING = 'pending';
    public const SHIPMENT_NEEDS_REVIEW = 'needs_review';
    public const SHIPMENT_APPROVED = 'approved';
    public const SHIPMENT_REIMBURSED = 'reimbursed';

    public const PAYMENT_PENDING = 'pending';
    public const PAYMENT_PAID = 'paid';

    protected $fillable = [
        'listing_id',
        'seller_id',
        'buyer_id',
        'converted_order_id',
        'status',
        'completion_method',
        'is_anonymous',
        'buyer_code_snapshot',
        'original_listing_title',
        'original_listing_price',
        'final_price',
        'agreed_details',
        'notes',
        'shipping_notes',
        'custom_requirements',
        'timeline_notes',
        'admin_notes',
        'carrier',
        'package_size',
        'shipping_cents',
        'tracking_number',
        'proof_path',
        'shipment_status',
        'shipment_submitted_at',
        'shipment_approved_at',
        'started_at',
        'ready_to_ship_at',
        'completed_at',
        'last_status_change_at',
        'last_reminder_sent_at',
        'removed_from_board_at',
        'payment_status',
        'paid_at',
        'payment_provider',
        'payment_intent_id',
        'amount_charged_cents',
        'reimbursement_transfer_id',
    ];

    protected $casts = [
        'agreed_details' => 'array',
        'is_anonymous' => 'boolean',
        'original_listing_price' => 'decimal:2',
        'final_price' => 'decimal:2',
        'shipment_submitted_at' => 'datetime',
        'shipment_approved_at' => 'datetime',
        'started_at' => 'datetime',
        'ready_to_ship_at' => 'datetime',
        'completed_at' => 'datetime',
        'last_status_change_at' => 'datetime',
        'last_reminder_sent_at' => 'datetime',
        'removed_from_board_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function listing()
    {
        return $this->belongsTo(Listing::class, 'listing_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function convertedOrder()
    {
        return $this->belongsTo(Order::class, 'converted_order_id');
    }

    public function getLithuanianStatusAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_AGREED => 'Sutarta',
            self::STATUS_DAROMAS => 'Daroma',
            self::STATUS_READY_TO_SHIP => 'Paruošta išsiuntimui',
            self::STATUS_COMPLETED => 'Užbaigta',
            self::STATUS_CANCELLED => 'Atšaukta',
            default => 'Nežinoma',
        };
    }

    public function hasLinkedBuyer(): bool
{
    return !is_null($this->buyer_id);
}

public function canUsePlatformFlow(): bool
{
    return $this->hasLinkedBuyer() && !$this->is_anonymous;
}

public function isPaid(): bool
{
    return $this->payment_status === self::PAYMENT_PAID;
}
}
