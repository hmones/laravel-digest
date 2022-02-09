<?php

namespace Hmones\LaravelDigest\Tests\Feature;

use Hmones\LaravelDigest\Mail\DefaultMailable;
use Hmones\LaravelDigest\Models\Digest as DigestModel;
use Hmones\LaravelDigest\Tests\TestCase;
use Illuminate\Support\Facades\Mail;

class AmountDigestTest extends TestCase
{
    public function test_digest_emails_are_sent_successfully_after_threshold_with_empty_data(): void
    {
        $emptyData = [null, null, null];
        $this->addEmails($emptyData);
        Mail::assertQueued(DefaultMailable::class, fn ($mail) => $mail->data === $emptyData);
        $this->assertEquals(DigestModel::count(), 0);
    }

    public function test_digest_emails_are_sent_successfully_after_threshold(): void
    {
        $this->addEmails($this->testData);
        Mail::assertQueued(DefaultMailable::class, fn ($mail) => $mail->data === $this->testData);
        $this->assertEquals(DigestModel::count(), 0);
    }

    public function test_digest_emails_with_no_frequency_are_sent_successfully_after_threshold(): void
    {
        $this->addEmails(['name' => 'Fourth'], 'testBatch', DigestModel::DAILY);
        $this->addEmails($this->testData);
        Mail::assertQueued(DefaultMailable::class, fn ($mail) => $mail->data === $this->testData);
        $this->assertEquals(DigestModel::count(), 1);
    }

    public function test_digest_emails_are_not_sent_if_threshold_option_not_enabled(): void
    {
        config(['laravel-digest.amount.enabled' => false]);
        $this->addEmails($this->testData);
        Mail::assertNothingQueued();
        $this->assertEquals(DigestModel::count(), 3);
    }

    public function test_digest_emails_are_not_queued_if_method_option_is_set_to_send(): void
    {
        config(['laravel-digest.method' => 'send']);
        $this->addEmails($this->testData);
        Mail::assertSent(DefaultMailable::class, fn ($mail) => $mail->data === $this->testData);
        $this->assertEquals(DigestModel::count(), 0);
    }

    public function test_digest_emails_with_custom_threshold_are_sent_successfully_after_threshold(): void
    {
        config(['laravel-digest.amount.threshold' => 10]);
        $this->addEmails($this->testData, 'testBatch', 3);
        Mail::assertQueued(DefaultMailable::class, fn ($mail) => $mail->data === $this->testData);
        $this->assertEquals(DigestModel::count(), 0);
    }

    public function test_digest_emails_with_custom_threshold_are_not_sent_before_threshold(): void
    {
        config(['laravel-digest.amount.threshold' => 3]);
        $this->addEmails($this->testData, 'testBatch', 4);
        Mail::assertNothingQueued();
        $this->assertEquals(DigestModel::count(), 3);
    }

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        config(['laravel-digest.amount.threshold' => 3]);
    }
}
