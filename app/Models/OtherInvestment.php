<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OtherInvestment extends Model
{
    protected $fillable = [
        'investment_category_id',
        'name',
        'amount',
        'details',
        'receipt_path',
        'receipt_name',
        'invested_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'invested_at' => 'date',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(InvestmentCategory::class, 'investment_category_id');
    }
}
