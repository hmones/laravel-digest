<?php

namespace Hmones\LaravelDigest\Tests\Feature;

use Hmones\LaravelDigest\Mail\DefaultMailable;
use Hmones\LaravelDigest\Models\Digest as DigestModel;
use Hmones\LaravelDigest\Tests\TestCase;
use Illuminate\Support\Facades\Mail;

class AmountDigestTest extends TestCase
{
    protected $testData = [
        ['First Name'],
        ['Second Name'],
        ['Third Name']
    ];

    protected $thresholdConfKey = 'laravel-digest.amount.threshold';

    public function test_digest_emails_are_sent_successfully_after_threshold(): void
    {
        config([$this->thresholdConfKey => 3]);
        $this->addEmails($this->testData);
        Mail::assertQueued(DefaultMailable::class, fn ($mail) => $mail->data === $this->testData);
        $this->assertEquals(DigestModel::count(), 0);
    }

    public function test_digest_emails_are_not_sent_if_threshold_option_not_enabled(): void
    {
        config([$this->thresholdConfKey => 3, 'laravel-digest.amount.enabled' => false]);
        $this->addEmails($this->testData);
        Mail::assertNothingQueued();
        $this->assertEquals(DigestModel::count(), 3);
    }

    public function test_digest_emails_are_not_queued_if_method_option_is_set_to_send(): void
    {
        config([$this->thresholdConfKey => 3, 'laravel-digest.method' => 'send']);
        $this->addEmails($this->testData);
        Mail::assertSent(DefaultMailable::class, fn ($mail) => $mail->data === $this->testData);
        $this->assertEquals(DigestModel::count(), 0);
    }

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }
}
