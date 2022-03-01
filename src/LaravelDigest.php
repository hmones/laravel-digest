<?php

namespace Hmones\LaravelDigest;

use Hmones\LaravelDigest\Models\Digest as Model;
use Illuminate\Support\Facades\Mail;

class LaravelDigest
{
    protected $method;
    protected $digests;
    protected $frequencies;

    public function __construct()
    {
        $this->method = config('laravel-digest.method', 'queue');
        $this->frequencies = $this->getFrequencies();
    }

    public function getCustomFrequencies(): array
    {
        $frequencies = config('laravel-digest.frequency');
        unset($frequencies['enabled'], $frequencies['daily'], $frequencies['weekly'], $frequencies['monthly']);

        return $frequencies;
    }

    public function getFrequencies(): array
    {
        return array_keys(array_filter(config('laravel-digest.frequency'), fn ($key) => $key !== 'enabled', ARRAY_FILTER_USE_KEY));
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
            $this->sendBatch($batchRecords->pluck('data')->toArray(), $mailable, $this->method);
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
        return (in_array($frequency, $this->getFrequencies()) && config('laravel-digest.frequency.enabled'))
            || (is_int($frequency) && $frequency !== 0 && config('laravel-digest.amount.enabled'))
            || (is_null($frequency) && config('laravel-digest.amount.enabled'));
    }

    protected function isValidMailable(string $mailable): bool
    {
        return class_exists($mailable);
    }
}
