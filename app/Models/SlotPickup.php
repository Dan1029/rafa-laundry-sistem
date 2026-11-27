<?php

namespace App\Models;

use Database\Factories\SlotPickupFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SlotPickup extends Model
{
    /** @use HasFactory<SlotPickupFactory> */
    use HasFactory;

    protected $table = 'transaksis';

    protected $fillable = [
        'transaction_date',
        'start_time',
        'end_time',
        'max_bookings',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
        ];
    }

    public function pesanans(): HasMany
    {
        return $this->hasMany(Pesanan::class, 'slot_id');
    }
}
