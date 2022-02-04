<?php

namespace Hmones\LaravelDigest\Tests;

use Hmones\LaravelDigest\Facades\Digest;
use Hmones\LaravelDigest\LaravelDigestServiceProvider;
use Hmones\LaravelDigest\Mail\DefaultMailable;
use Hmones\LaravelDigest\Models\Digest as DigestModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as Test;

class TestCase extends Test
{
    use RefreshDatabase;

    public function test_digest_emails_are_successfully_to_database(): void
    {
        $data = ['first', 'second'];
        $this->addEmails($data);
        $this->assertEquals(DigestModel::count(), 2);
        $this->assertEquals(DigestModel::all()->pluck('data')->toArray(), $data);
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelDigestServiceProvider::class,
        ];
    }

    protected function addEmails(array $data, string $batch = 'testBatch', string $frequency = null): void
    {
        foreach ($data as $record) {
            Digest::add($batch, DefaultMailable::class, $record, $frequency);
        }
    }
}
