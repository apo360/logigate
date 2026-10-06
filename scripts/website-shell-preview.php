<?php

// Static anonymous review only. No application endpoints are executed.
if (PHP_SAPI === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (str_starts_with($path, '/build/') || str_starts_with($path, '/css/website/') || str_starts_with($path, '/js/website/') || str_starts_with($path, '/dist/img/')) return false;
    $pages = ['/' => 'home', '/mercado' => 'marketplace', '/consultar-pauta-aduaneira' => 'pauta'];
    if (!isset($pages[$path])) { http_response_code(404); return; }
    header('Content-Type: text/html; charset=UTF-8');
    readfile(__DIR__.'/../storage/app/website-shell-qa/'.$pages[$path].'.html');
    return;
}
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['session.driver' => 'array', 'cache.default' => 'array', 'mail.default' => 'array']);
Illuminate\Support\Facades\Http::preventStrayRequests();
Illuminate\Support\Facades\Mail::fake();
require_once __DIR__.'/../tests/Support/LandingFixtures.php';
$output = storage_path('app/website-shell-qa');
if (!is_dir($output)) mkdir($output, 0777, true);
Illuminate\Support\Facades\URL::forceRootUrl('http://127.0.0.1:8127');
foreach ([['home', '/', 'welcome'], ['marketplace', '/mercado', 'WebSite.marketplace'], ['pauta', '/consultar-pauta-aduaneira', 'WebSite.consultar_pauta']] as [$name, $path, $view]) {
    $request = Illuminate\Http\Request::create('http://127.0.0.1:8127'.$path);
    $route = app('router')->getRoutes()->match($request);
    $request->setRouteResolver(fn () => $route);
    $app->instance('request', $request);
    $html = view($view, ['planos' => Tests\Support\LandingFixtures::plans(), 'filters' => [], 'selection' => null, 'choices' => null, 'directory' => ['ready' => false, 'start' => now()->startOfMonth()->subMonths(12), 'end' => now()->startOfMonth(), 'profiles' => new Illuminate\Pagination\LengthAwarePaginator([], 0, 12)], 'errors' => new Illuminate\Support\ViewErrorBag()])->render();
    $html = str_replace(['+244948242262', '+244 948 242 262', 'geral@hongayetu.com'], ['+244000000000', '+244 000 000 000', 'apoio@example.test'], $html);
    file_put_contents($output.'/'.$name.'.html', $html);
}
echo "Shared website preview rendered with anonymous fixtures.\n";
