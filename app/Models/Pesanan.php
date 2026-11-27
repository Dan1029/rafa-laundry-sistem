<?php

namespace App\Models;

use Database\Factories\PesananFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pesanan extends Model
{
    /** @use HasFactory<PesananFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_code',
        'user_id',
        'service_id',
        'slot_id',
        'customer_name',
        'customer_phone',
        'pickup_address',
        'pickup_date',
        'fulfillment_type',
        'quantity',
        'total_price',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'pickup_date' => 'date',
            'quantity' => 'decimal:2',
            'total_price' => 'decimal:2',
        ];
    }

    public function layanan(): BelongsTo
    {
        return $this->belongsTo(Layanan::class, 'service_id');
    }

    public function slotPickup(): BelongsTo
    {
        return $this->belongsTo(SlotPickup::class, 'slot_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
