<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Installment extends Model
{
    protected $fillable = [
        'member_id',
        'amount',
        'deposit_date',
        'paid_at',
        'note',
        'installment_payment_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'deposit_date' => 'date',
            'paid_at' => 'date',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(InstallmentPayment::class, 'installment_payment_id');
    }
}
