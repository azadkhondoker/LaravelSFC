<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoanClient extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'nid',
        'address',
    ];

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }
}
