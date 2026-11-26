<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanPayment extends Model
{
    protected $fillable = [
        'loan_id',
        'month_number',
        'due_date',
        'principal',
        'interest',
        'total_amount',
        'paid_at',
        'receipt_no',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'paid_at' => 'date',
            'principal' => 'decimal:2',
            'interest' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }
}
