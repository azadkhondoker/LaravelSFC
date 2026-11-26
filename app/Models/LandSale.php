<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LandSale extends Model
{
    protected $fillable = [
        'area_sold',
        'sale_price',
        'buyer_name',
        'buyer_phone',
        'sold_at',
        'deed_path',
        'deed_name',
    ];

    protected function casts(): array
    {
        return [
            'area_sold' => 'decimal:3',
            'sale_price' => 'decimal:2',
            'sold_at' => 'date',
        ];
    }

    public function land(): BelongsTo
    {
        return $this->belongsTo(LandInvestment::class, 'land_investment_id');
    }
}
