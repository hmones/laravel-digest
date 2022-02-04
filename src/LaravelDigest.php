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

    public function add(string $batch, string $mailable, $data, string $frequency = null): bool
    {
        if (!$this->isValidInput($mailable, $frequency)) {
            return false;
        }

        $batchCount = $this->addToBatch($batch, $mailable, $frequency, $data);

        if (config('laravel-digest.amount.enabled') && $batchCount >= config('laravel-digest.amount.threshold')) {
            $this->sendBatch($batch, $mailable, $this->method);
            $this->deleteBatch($batch);
        }

        return true;
    }

    protected function addToBatch(string $batch, string $mailable, ?string $frequency, $data): int
    {
        Model::create(compact(['batch', 'mailable', 'frequency', 'data']));

        return Model::where('batch', $batch)->count();
    }

    protected function sendBatch($batch, $mailable, $method): void
    {
        $data = Model::where('batch', $batch)->latest()->pluck('data')->toArray();

        Mail::$method(new $mailable($data));
    }

    protected function deleteBatch(string $batch): void
    {
        Model::where('batch', $batch)->delete();
    }

    protected function isValidInput(string $mailable, ?string $frequency): bool
    {
        return class_exists($mailable) && in_array($frequency, [null, Model::DAILY, Model::WEEKLY, Model::MONTHLY]);
    }
}