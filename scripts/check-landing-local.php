<?php

// Optional read-only smoke check of the actual WelcomeController and local plans.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$connectionName = config('database.default');
$connectionConfig = config('database.connections.'.$connectionName);
if (! in_array($connectionConfig['host'] ?? null, ['localhost', '127.0.0.1', '::1'], true) || ! empty($connectionConfig['url'])) {
    fwrite(STDERR, "Skipped: this check requires a local database host without a connection URL override.\n");
    exit(2);
}
config(['session.driver' => 'array', 'cache.default' => 'array', 'mail.default' => 'array', 'filesystems.default' => 'local']);
$app->instance('request', Illuminate\Http\Request::create('http://127.0.0.1:8123/'));
Illuminate\Support\Facades\URL::forceRootUrl('http://127.0.0.1:8123');
Illuminate\Support\Facades\Http::preventStrayRequests();
Illuminate\Support\Facades\Mail::fake();
Illuminate\Support\Facades\DB::connection()->beforeExecuting(function (string $query): void {
    if (! preg_match('/^\s*(select|show)\b/i', $query)) throw new RuntimeException('Non-read query blocked.');
});
try {
    $view = app(App\Http\Controllers\WebPage\WelcomeController::class)->index();
    $html = $view->render();
    if (! str_contains($html, 'A sua operação aduaneira')) throw new RuntimeException('Landing content not rendered.');
    $directory = storage_path('app/landing-qa');
    if (! is_dir($directory)) mkdir($directory, 0777, true);
    file_put_contents($directory.'/live.html', $html);
    echo 'Actual WelcomeController rendered successfully with '.$view->getData()['planos']->count()." local plans (SELECT queries only).\n";
} catch (Throwable $error) {
    fwrite(STDERR, 'Local smoke check failed: '.get_class($error).". No database changes performed.\n");
    exit(1);
}
