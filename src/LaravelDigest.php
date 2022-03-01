<?php

namespace Hmones\LaravelDigest;

use Hmones\LaravelDigest\Models\Digest as Model;
use Illuminate\Support\Facades\Mail;

class LaravelDigest
{
    protected $method;
    protected $digests;
    protected $frequencies;
    protected $customFrequencies;

    public function __construct()
    {
        $this->method = config('laravel-digest.method', 'queue');
        $this->frequencies = array_keys(array_filter(config('laravel-digest.frequency'), fn ($key) => $key !== 'enabled', ARRAY_FILTER_USE_KEY));
        $this->customFrequencies = array_filter(config('laravel-digest.frequency'), fn ($key) => ! in_array($key, [
            'enabled',
            'daily',
            'weekly',
            'monthly',
        ]), ARRAY_FILTER_USE_KEY);
    }

    public function getCustomFrequencies(): array
    {
        return $this->customFrequencies;
    }

    public function getFrequencies(): array
    {
        return $this->frequencies;
    }

    public function add(string $batch, string $mailable, $data, $frequency = null): bool
    {
        if (! $this->isValidFrequency($frequency) || ! $this->isValidMailable($mailable)) {
            return false;
        }

        $frequency = $frequency ?? config('laravel-digest.amount.threshold');

        Model::create(compact(['batch', 'mailable', 'frequency', 'data']));

        if (in_array($frequency, $this->frequencies)) {
            return true;
        }

        $batchRecords = Model::where('batch', $batch)->whereNotIn('frequency', $this->frequencies)->latest();

        if (config('laravel-digest.amount.enabled') && $batchRecords->count() >= $frequency) {
            $this->sendBatch($this->method, $mailable, $batchRecords->pluck('data')->toArray());
            $batchRecords->delete();
        }

        return true;
    }

    protected function sendBatch(string $method, string $mailable, array $data): void
    {
        Mail::$method(new $mailable($data));
    }

    protected function isValidFrequency($frequency): bool
    {
        return (config('laravel-digest.frequency.enabled') && in_array($frequency, $this->frequencies))
            || (config('laravel-digest.amount.enabled') && ((int) $frequency !== 0 || is_null($frequency)));
    }

    protected function isValidMailable(string $mailable): bool
    {
        return class_exists($mailable);
    }
}
