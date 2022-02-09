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

    protected $dailyDigestCommand = 'digest:send daily';
    protected $weeklyDigestCommand = 'digest:send weekly';
    protected $monthlyDigestCommand = 'digest:send monthly';
    protected $customDigestCommand = 'digest:send custom';
    protected $batchName = 'testBatch';

    public function test_frequency_digest_email_sending_events_are_registered(): void
    {
        $schedule = app()->make(Schedule::class);
        $frequency = collect($schedule->events())->pluck('expression')->toArray();
        $commands = collect($schedule->events())->pluck('command')->map(function ($item) {
            return Str::after($item, '\'artisan\' ');
        })->toArray();
        $this->assertEquals($commands, [
            $this->dailyDigestCommand,
            $this->weeklyDigestCommand,
            $this->monthlyDigestCommand,
            $this->customDigestCommand,
        ]);
        $this->assertEquals($frequency, [
            '0 0 * * *',
            '0 0 * * 1',
            '0 0 1 * *',
            '0 0 1 1 *',
        ]);
    }

    public function test_frequency_digest_emails_sending_events_are_not_sent_when_not_enabled(): void
    {
        config(['laravel-digest.frequency.enabled' => false]);
        $message = 'The digest frequency option is not enabled from the configuration, please enable it first';
        $this->addEmails($this->testData, $this->batchName, DigestModel::DAILY);
        $this->addEmails($this->testData, $this->batchName, DigestModel::WEEKLY);
        $this->addEmails($this->testData, $this->batchName, DigestModel::MONTHLY);
        $this->artisan($this->dailyDigestCommand)->expectsOutput($message);
        $this->artisan($this->weeklyDigestCommand)->expectsOutput($message);
        $this->artisan($this->monthlyDigestCommand)->expectsOutput($message);
    }

    public function test_daily_emails_sent_successfully(): void
    {
        $this->addEmails($this->testData, $this->batchName, DigestModel::DAILY);
        $this->artisan($this->dailyDigestCommand)
            ->expectsOutput('Sending daily emails')
            ->expectsOutput('Daily emails sent');
        Mail::assertQueued(DefaultMailable::class, fn ($mail) => $mail->data === $this->testData);
        $this->assertEquals(DigestModel::count(), 0);
    }

    public function test_weekly_emails_sent_successfully(): void
    {
        $this->addEmails($this->testData, $this->batchName, DigestModel::WEEKLY);
        $this->artisan($this->weeklyDigestCommand)
            ->expectsOutput('Sending weekly emails')
            ->expectsOutput('Weekly emails sent');
        Mail::assertQueued(DefaultMailable::class, fn ($mail) => $mail->data === $this->testData);
        $this->assertEquals(DigestModel::count(), 0);
    }

    public function test_monthly_emails_sent_successfully(): void
    {
        $this->addEmails($this->testData, $this->batchName, DigestModel::MONTHLY);
        $this->artisan($this->monthlyDigestCommand)
            ->expectsOutput('Sending monthly emails')
            ->expectsOutput('Monthly emails sent');
        Mail::assertQueued(DefaultMailable::class, fn ($mail) => $mail->data === $this->testData);
        $this->assertEquals(DigestModel::count(), 0);
    }

    public function test_custom_emails_sent_successfully(): void
    {
        config(['laravel-digest.frequency' => [
            'enabled'   => true,
            'daily' => [
                'time'      => '00:00',
            ],
            'weekly' => [
                'time'      => '00:00',
                'day'       => 1,
            ],
            'monthly' => [
                'time'      => '00:00',
                'day'       => 1,
            ],
            'test' => '0 0 1 1 *',
        ]]);
        $this->addEmails($this->testData, $this->batchName, 'test');
        $this->artisan('digest:send test')
            ->expectsOutput('Sending test emails')
            ->expectsOutput('Test emails sent');
        Mail::assertQueued(DefaultMailable::class, fn ($mail) => $mail->data === $this->testData);
        $this->assertEquals(DigestModel::count(), 0);
    }

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }
}
