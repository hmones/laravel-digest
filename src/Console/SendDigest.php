<?php

namespace Hmones\LaravelDigest\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendDigest extends Command
{
    protected $signature = 'digest:send {frequency : Takes one of three values daily, weekly and monthly}';

    protected $description = 'Send Digest Emails Stored in the Database';

    public function handle(): void
    {
        $frequency = $this->argument('frequency');

        if (!in_array($frequency, ['daily', 'weekly', 'monthly'])) {
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
        $batches = DB::table('digests')->where('frequency', $frequency)->orderBy('created_at', 'desc')->groupBy('batch')->get();

        foreach ($batches as $batch) {
            $email = $batch->first();
            $method = config('laravel-digest.method');
            $data = $batch->pluck('data')->toArray();

            Mail::$method(resolve($email->mailable, $data));
        }
    }

    protected function deleteBatches(string $frequency): void
    {
        DB::table('digests')->where('frequency', $frequency)->delete();
    }
}
