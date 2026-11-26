<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanCollateral extends Model
{
    protected $fillable = [
        'asset_type',
        'description',
        'estimated_value',
        'document_path',
        'document_name',
    ];

    protected function casts(): array
    {
        return [
            'estimated_value' => 'decimal:2',
        ];
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }
}
