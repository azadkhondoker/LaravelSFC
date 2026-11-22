<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InstallmentPayment extends Model
{
    protected $fillable = [
        'receipt_no',
        'total_amount',
        'installment_amount',
        'installments_count',
        'paid_at',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'installment_amount' => 'decimal:2',
            'installments_count' => 'integer',
            'paid_at' => 'date',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class)->withTrashed();
    }

    public function installments(): HasMany
    {
        return $this->hasMany(Installment::class);
    }
}
