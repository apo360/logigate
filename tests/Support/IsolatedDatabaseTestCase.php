<?php

namespace Tests\Support;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

abstract class IsolatedDatabaseTestCase extends TestCase
{
    private bool $transactionStarted = false;

    protected function setUp(): void
    {
        parent::setUp();
        self::assertTrue(app()->environment('testing'));
        self::assertSame('mysql', config('database.default'));
        self::assertContains(config('database.connections.mysql.host'), ['127.0.0.1', 'localhost']);
        self::assertSame('logigate_testing', config('database.connections.mysql.database'));
        self::assertSame('logigate_testing', DB::connection()->getDatabaseName());
        $unsafe = DB::select("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_TYPE = 'BASE TABLE' AND ENGINE <> 'InnoDB'");
        self::assertSame([], $unsafe, 'All test tables must support transaction rollback.');
        DB::beginTransaction();
        $this->transactionStarted = true;
    }

    protected function tearDown(): void
    {
        if ($this->transactionStarted) {
            DB::rollBack();
        }
        parent::tearDown();
    }
}
