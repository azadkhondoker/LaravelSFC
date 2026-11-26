<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanGuarantor extends Model
{
    protected $fillable = [
        'position',
        'name',
        'phone',
        'nid',
        'address',
        'liability_confirmed',
    ];

    protected function casts(): array
    {
        return [
            'liability_confirmed' => 'boolean',
        ];
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }
}
