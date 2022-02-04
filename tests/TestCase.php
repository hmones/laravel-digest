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
        $this->addEmails([['first'], ['second']]);
        $this->assertEquals(DigestModel::count(), 2);
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
            Digest::add($batch, DefaultMailable::class, $record);
        }
    }
}