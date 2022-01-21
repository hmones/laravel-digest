<?php

namespace Hmones\LaravelDigest;

use Illuminate\Support\Facades\DB;

class LaravelDigest
{
    protected $batch;
    protected $batchId;
    protected $mailable;
    protected $data;
    protected $digests;
    protected $frequency;
    protected $time;
    protected $day;

    public function __construct(string $batch, string $mailable, array $data, string $frequency = null, string $time = null, int $day = null)
    {
        $this->batchId = $batch;
        $this->mailable = $mailable;
        $this->data = $data;
        $this->frequency = $frequency;
        $this->time = $time;
        $this->day = $day;
        $this->digests = DB::table('digests');
        $this->batch = $this->digests->where('batch', $batch);
    }

    public function add(): ?array
    {
        $count = $this->storeEmail();

        return $count >= config('laravel-digest.amount.threshold') && config('laravel-digest.amount.threshold') ? $this->getData() : null;
    }

    protected function storeEmail(): int
    {
        $this->digests->insert([
            'batch'    => $this->batchId,
            'mailable' => $this->mailable,
            'data'     => $this->data
        ]);

        return $this->batch->count();
    }

    protected function getData(): array
    {
        return $this->batch->pluck('data')->toArray();
    }
}