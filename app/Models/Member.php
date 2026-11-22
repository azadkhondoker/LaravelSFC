<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'member_code',
        'name',
        'email',
        'gender',
        'nid',
        'phone',
        'address',
        'joining_date',
        'installment_amount',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'joining_date' => 'date',
            'installment_amount' => 'decimal:2',
        ];
    }

    public function installments(): HasMany
    {
        return $this->hasMany(Installment::class);
    }

    public function installmentPayments(): HasMany
    {
        return $this->hasMany(InstallmentPayment::class);
    }

    protected static function booted()
    {
        static::creating(function ($member) {
            $lastMember = Member::withTrashed()->orderBy('id', 'desc')->first();
            $lastMemberCode = $lastMember ? $lastMember->member_code : null;

            if ($lastMemberCode) {
                $lastNumber = (int) substr($lastMemberCode, 2);
                $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
                $member->member_code = 'M-' . $newNumber;
            } else {
                $member->member_code = 'M-0001';
            }
        });
    }
}
