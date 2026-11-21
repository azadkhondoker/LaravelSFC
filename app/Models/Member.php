<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $fillable = [
        'member_code',
        'name',
        'email',
        'gender',
        'nid',
        'phone',
        'address',
        'joining_date',
        'status',
    ];
    protected $casts = ['joining_date' => 'date'];
    protected static function booted()
    {
        static::creating(function ($member) {
            $lastMember = Member::orderBy('id', 'desc')->first();
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
