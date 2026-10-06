<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (!app()->environment('testing') || config('database.default') !== 'mysql'
    || !in_array(config('database.connections.mysql.host'), ['127.0.0.1', 'localhost'], true)
    || config('database.connections.mysql.database') !== 'logigate_testing'
    || Illuminate\Support\Facades\DB::connection()->getDatabaseName() !== 'logigate_testing') {
    throw new RuntimeException('Refusing migration outside confirmed local test database.');
}
Illuminate\Support\Facades\Http::preventStrayRequests();
Illuminate\Support\Facades\Mail::fake();
$status = Illuminate\Support\Facades\Artisan::call('migrate', ['--path' => 'database/migrations/2026_10_05_000001_create_marketplace_tables.php', '--no-interaction' => true]);
echo Illuminate\Support\Facades\Artisan::output();
exit($status);
