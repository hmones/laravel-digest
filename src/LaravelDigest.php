<?php

namespace Hmones\LaravelDigest;

use Hmones\LaravelDigest\Models\Digest as Model;
use Illuminate\Support\Facades\Mail;

class LaravelDigest
{
    protected $method;
    protected $digests;

    public function __construct()
    {
        $this->method = config('laravel-digest.method');
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
        if (! $this->isValidInput($mailable, $frequency)) {
            return false;
        }

        if (is_null($frequency)) {
            $frequency = config('laravel-digest.amount.threshold');
        }

        $batchCount = $this->addToBatch($batch, $mailable, $frequency, $data);

        if (config('laravel-digest.amount.enabled') && $batchCount >= $frequency) {
            $this->sendBatch($batch, $mailable, $this->method);
            $this->deleteBatch($batch);
        }

        return true;
    }

    protected function addToBatch(string $batch, string $mailable, ?string $frequency, $data): int
    {
        Model::create(compact(['batch', 'mailable', 'frequency', 'data']));

        return Model::where('batch', $batch)->whereNotIn('frequency', $this->getFrequencies())->count();
    }

    protected function sendBatch($batch, $mailable, $method): void
    {
        $data = Model::where('batch', $batch)->whereNotIn('frequency', $this->getFrequencies())->latest()->pluck('data')->toArray();

        Mail::$method(new $mailable($data));
    }

    protected function deleteBatch(string $batch): void
    {
        Model::where('batch', $batch)->whereNotIn('frequency', $this->getFrequencies())->delete();
    }

    protected function isValidInput(string $mailable, ?string $frequency): bool
    {
        return class_exists($mailable) && (in_array($frequency, $this->getFrequencies()) || (int)$frequency !== 0 || is_null($frequency));
    }
}
