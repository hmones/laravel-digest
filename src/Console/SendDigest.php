<?php

namespace Hmones\LaravelDigest\Console;

use Hmones\LaravelDigest\Models\Digest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendDigest extends Command
{
    protected $signature = 'digest:send {frequency : Takes one of three values daily, weekly and monthly}';

    protected $description = 'Send Digest Emails Stored in the Database';

    public function handle(): void
    {
        if (! config('laravel-digest.frequency.enabled')) {
            $this->info('The digest frequency option is not enabled from the configuration, please enable it first');

            return;
        }

        $frequency = $this->argument('frequency');

        if (! in_array($frequency, ['daily', 'weekly', 'monthly'])) {
            $this->error('The frequency you selected is not available!');

            return;
        }

        $this->info('Sending '.$frequency.' emails');
        $this->sendBatches($frequency);
        $this->deleteBatches($frequency);
        $this->info(ucfirst($frequency).'emails sent');
    }

    protected function sendBatches(string $frequency): void
    {
        $batches = Digest::where('frequency', $frequency)->orderBy('created_at', 'desc')->groupBy('batch')->get();

        foreach ($batches as $batch) {
            $mailable = optional($batch->first())->mailable;
            $method = config('laravel-digest.method');
            $data = $batch->pluck('data')->toArray();

            Mail::$method(new $mailable($data));
        }
    }

    protected function deleteBatches(string $frequency): void
    {
        Digest::where('frequency', $frequency)->delete();
    }
}
