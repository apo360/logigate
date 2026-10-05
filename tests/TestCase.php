<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (getenv('V1_TEST_DATABASE')) {
            $expected = getenv('V1_TEST_DATABASE');
            if (! preg_match('/^logigate_testing_v1_[a-f0-9]{10}$/', $expected)
                || ! app()->environment('testing')
                || config('database.connections.mysql.database') !== $expected
                || ! in_array(config('database.connections.mysql.host'), ['localhost', '127.0.0.1'], true)
                || \Illuminate\Support\Facades\DB::connection()->getDatabaseName() !== $expected) {
                throw new \RuntimeException('Refusing tests outside disposable sandbox.');
            }
            \Illuminate\Support\Facades\Http::preventStrayRequests();
            \Illuminate\Support\Facades\Storage::fake('s3');
            \Illuminate\Support\Facades\Storage::fake('local');
        }
    }
}
