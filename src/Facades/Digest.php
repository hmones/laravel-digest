<?php

namespace Hmones\LaravelDigest\Facades;

use Illuminate\Support\Facades\Facade;

class Digest extends Facade
{
    /**
     * @method static bool add(string $batch, string $mailable, array $data, string $frequency = null)
     */
    protected static function getFacadeAccessor(): string
    {
        return 'digest';
    }
}