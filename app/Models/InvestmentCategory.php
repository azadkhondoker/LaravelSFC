<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvestmentCategory extends Model
{
    protected $fillable = ['name'];

    public function investments(): HasMany
    {
        return $this->hasMany(OtherInvestment::class);
    }
}
