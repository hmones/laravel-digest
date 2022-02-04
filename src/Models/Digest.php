<?php

namespace Hmones\LaravelDigest\Models;

use Illuminate\Database\Eloquent\Model;

class Digest extends Model
{
    public const DAILY = 'daily';
    public const MONTHLY = 'monthly';
    public const WEEKLY = 'weekly';

    protected $guarded = [];

    protected $casts = [
        'data' => 'array',
    ];
}
