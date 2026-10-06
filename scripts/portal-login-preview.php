<?php

// Fixture-only front-end review. Authentication and all POST routes are disabled.
if (PHP_SAPI === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (str_starts_with($path, '/css/website/') || str_starts_with($path, '/js/website/') || str_starts_with($path, '/dist/img/')) return false;
    if ($_SERVER['REQUEST_METHOD'] !== 'GET' || !in_array($path, ['/portal-cliente/Acesso', '/portal-cliente/Recuperar-Senha'], true)) { http_response_code(404); return; }
    $state = $path === '/portal-cliente/Recuperar-Senha' ? 'reset' : (($_GET['state'] ?? '') === 'error' ? 'error' : 'normal');
    header('Content-Type: text/html; charset=UTF-8');
    readfile(__DIR__.'/../storage/app/portal-login-qa/'.$state.'.html');
    return;
}
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['session.driver' => 'array', 'cache.default' => 'array', 'mail.default' => 'array']);
Illuminate\Support\Facades\Http::preventStrayRequests();
Illuminate\Support\Facades\Mail::fake();
Illuminate\Support\Facades\URL::forceRootUrl('http://127.0.0.1:8128');
$output = storage_path('app/portal-login-qa');
if (!is_dir($output)) mkdir($output, 0777, true);
foreach (['normal', 'error', 'reset'] as $state) {
    $request = Illuminate\Http\Request::create('http://127.0.0.1:8128/portal-cliente/'.($state === 'reset' ? 'Recuperar-Senha' : 'Acesso'));
    $request->setRouteResolver(fn () => app('router')->getRoutes()->match($request));
    $app->instance('request', $request);
    app('session')->flush();
    $request->setLaravelSession(app('session')->driver());
    if ($state === 'error') app('session')->flashInput(['login' => 'cliente@example.test', 'password' => 'must-not-render']);
    $errors = new Illuminate\Support\ViewErrorBag();
    if ($state === 'error') $errors->put('default', new Illuminate\Support\MessageBag(['login' => ['As credenciais informadas não correspondem aos nossos registos.'], 'password' => ['Introduza a palavra-passe.']]));
    $html = view('WebSite.ClienteAppPage.portal_login', ['errors' => $errors, 'status' => $state === 'reset' ? 'Para redefinir a senha do Portal Cliente, contacte o seu despachante.' : null])->render();
    $html = str_replace(['+244948242262', '+244 948 242 262', 'geral@hongayetu.com'], ['+244000000000', '+244 000 000 000', 'apoio@example.test'], $html);
    file_put_contents($output.'/'.$state.'.html', $html);
}
echo "Portal fixtures rendered without authentication or database access.\n";
