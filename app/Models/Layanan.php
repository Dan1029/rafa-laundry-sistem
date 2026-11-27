<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Layanan extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'unit',
        'price_per_unit',
        'turnaround_hours',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price_per_unit' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function pesanans(): HasMany
    {
        return $this->hasMany(Pesanan::class, 'service_id');
    }
}
