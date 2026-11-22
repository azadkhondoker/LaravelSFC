<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class posts extends Model
{
    protected $fillable = [
        'title',
        'content',
        'post_date',
    ];

    protected function casts(): array
    {
        return [
            'post_date' => 'date',
        ];
    }
}
