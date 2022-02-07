<?php

namespace Hmones\LaravelDigest\Tests\Feature;

use Hmones\LaravelDigest\Mail\DefaultMailable;
use Hmones\LaravelDigest\Models\Digest as DigestModel;
use Hmones\LaravelDigest\Tests\TestCase;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class FrequencyDigestTest extends TestCase
{
    use DatabaseTransactions;

    protected $testData = [
        ['name' => 'First'],
        ['name' => 'Second'],
        ['name' => 'Third'],
    ];

    public function test_frequency_digest_email_sending_events_are_registered(): void
    {
        $schedule = app()->make(Schedule::class);
        $frequency = collect($schedule->events())->pluck('expression')->toArray();
        $commands = collect($schedule->events())->pluck('command')->map(function ($item) {
            return Str::after($item, '\'artisan\' ');
        })->toArray();
        $this->assertEquals($commands, [
            'digest:send daily',
            'digest:send weekly',
            'digest:send monthly',
        ]);
        $this->assertEquals($frequency, [
            '0 0 * * *',
            '0 0 * * 1',
            '0 0 1 * *'
        ]);
    }

    public function test_frequency_digest_emails_sending_events_are_not_sent_when_not_enabled(): void
    {
        config(['laravel-digest.frequency.enabled' => false]);
        $this->addEmails($this->testData, 'testBatch', DigestModel::DAILY);
        $this->addEmails($this->testData, 'testBatch', DigestModel::WEEKLY);
        $this->addEmails($this->testData, 'testBatch', DigestModel::MONTHLY);
        $this->artisan('digest:send daily')->expectsOutput('The digest frequency option is not enabled from the configuration, please enable it first');
        $this->artisan('digest:send weekly')->expectsOutput('The digest frequency option is not enabled from the configuration, please enable it first');
        $this->artisan('digest:send monthly')->expectsOutput('The digest frequency option is not enabled from the configuration, please enable it first');
    }

    public function test_daily_emails_sent_successfully(): void
    {
        $this->addEmails($this->testData, 'testBatch', DigestModel::DAILY);
        $this->artisan('digest:send daily')
            ->expectsOutput('Sending daily emails')
            ->expectsOutput('Daily emails sent');
        Mail::assertQueued(DefaultMailable::class, fn ($mail) => $mail->data === $this->testData);
        $this->assertEquals(DigestModel::count(), 0);
    }

    public function test_weekly_emails_sent_successfully(): void
    {
        $this->addEmails($this->testData, 'testBatch', DigestModel::WEEKLY);
        $this->artisan('digest:send weekly')
            ->expectsOutput('Sending weekly emails')
            ->expectsOutput('Weekly emails sent');
        Mail::assertQueued(DefaultMailable::class, fn ($mail) => $mail->data === $this->testData);
        $this->assertEquals(DigestModel::count(), 0);
    }

    public function test_monthly_emails_sent_successfully(): void
    {
        $this->addEmails($this->testData, 'testBatch', DigestModel::MONTHLY);
        $this->artisan('digest:send monthly')
            ->expectsOutput('Sending monthly emails')
            ->expectsOutput('Monthly emails sent');
        Mail::assertQueued(DefaultMailable::class, fn ($mail) => $mail->data === $this->testData);
        $this->assertEquals(DigestModel::count(), 0);
    }

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }
}
