<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    protected $fillable = [
        'member_id',
        'loan_client_id',
        'amount',
        'interest_rate',
        'duration_months',
        'start_date',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'interest_rate' => 'decimal:3',
            'start_date' => 'date',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(LoanClient::class, 'loan_client_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(LoanPayment::class);
    }

    public function guarantors(): HasMany
    {
        return $this->hasMany(LoanGuarantor::class)->orderBy('position');
    }

    public function collaterals(): HasMany
    {
        return $this->hasMany(LoanCollateral::class);
    }
}
