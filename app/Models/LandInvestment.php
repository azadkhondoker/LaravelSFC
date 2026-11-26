<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LandInvestment extends Model
{
    protected $fillable = [
        'name',
        'location',
        'purchase_price',
        'area',
        'area_unit',
        'deed_path',
        'purchased_at',
    ];

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'area' => 'decimal:3',
            'purchased_at' => 'date',
        ];
    }

    public function sales(): HasMany
    {
        return $this->hasMany(LandSale::class)->latest('sold_at');
    }
}
