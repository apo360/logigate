<?php

// Isolated preview: no database queries and no dispatch of application endpoints.
if (PHP_SAPI === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (str_starts_with($path, '/build/') || str_starts_with($path, '/dist/img/LandingPage/') || $path === '/favicon.ico') return false;
    if ($path !== '/mercado') { http_response_code(404); echo 'Application endpoints are disabled in this preview.'; return; }
    header('Content-Type: text/html; charset=UTF-8');
    readfile(__DIR__.'/../storage/app/marketplace-qa/index.html');
    return;
}
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['session.driver' => 'array', 'cache.default' => 'array', 'mail.default' => 'array']);
Illuminate\Support\Facades\Http::preventStrayRequests();
Illuminate\Support\Facades\Mail::fake();
$app->instance('request', Illuminate\Http\Request::create('http://127.0.0.1:8124/mercado'));
Illuminate\Support\Facades\URL::forceRootUrl('http://127.0.0.1:8124');
$view = view('WebSite.marketplace', [
    'filters' => [], 'selection' => null, 'choices' => null,
    'directory' => ['ready' => false, 'start' => now()->startOfMonth()->subMonths(12), 'end' => now()->startOfMonth(), 'profiles' => new Illuminate\Pagination\LengthAwarePaginator([], 0, 12)],
    'errors' => new Illuminate\Support\ViewErrorBag(),
]);
$directory = storage_path('app/marketplace-qa');
if (! is_dir($directory)) mkdir($directory, 0777, true);
file_put_contents($directory.'/index.html', $view->render());
echo "Marketplace view rendered without database access.\n";
