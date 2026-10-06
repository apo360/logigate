<?php

// Anonymous static preview. Operational routes and all writes remain disabled.
if (PHP_SAPI === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (str_starts_with($path, '/build/') || str_starts_with($path, '/dist/img/LandingPage/') || $path === '/favicon.ico') return false;
    $file = match ($path) { '/mercado' => 'marketplace.html', '/consultar-pauta-aduaneira' => isset($_GET['codigo']) ? 'pauta-selected.html' : 'pauta.html', default => null };
    if (!$file) { http_response_code(404); echo 'Application endpoints disabled in preview.'; return; }
    header('Content-Type: text/html; charset=UTF-8'); readfile(__DIR__.'/../storage/app/pauta-marketplace-qa/'.$file); return;
}
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['session.driver' => 'array', 'cache.default' => 'array', 'mail.default' => 'array']);
Illuminate\Support\Facades\Http::preventStrayRequests(); Illuminate\Support\Facades\Mail::fake();
$app->instance('request', Illuminate\Http\Request::create('http://127.0.0.1:8125/mercado'));
Illuminate\Support\Facades\URL::forceRootUrl('http://127.0.0.1:8125');
$output = storage_path('app/pauta-marketplace-qa'); if (!is_dir($output)) mkdir($output, 0777, true);
$selection = new App\Models\PautaAduaneira(['codigo' => '0203.11.00', 'descricao' => 'Mercadoria fictícia para demonstração']); $selection->id = 1;
$profiles = [(object) ['id' => 1, 'public_name' => 'Prestador de demonstração', 'public_location' => 'Luanda', 'operations' => 18, 'active_months' => 7, 'last_operation' => '2026-09-15'], (object) ['id' => 2, 'public_name' => 'Especialista de demonstração', 'public_location' => null, 'operations' => null, 'active_months' => null, 'last_operation' => null]];
$view = view('WebSite.marketplace', ['selection' => $selection, 'filters' => [], 'choices' => null, 'directory' => ['ready' => true, 'start' => now()->startOfMonth()->subMonths(12), 'end' => now()->startOfMonth(), 'profiles' => new Illuminate\Pagination\LengthAwarePaginator($profiles, 2, 12)], 'errors' => new Illuminate\Support\ViewErrorBag()]);
file_put_contents($output.'/marketplace.html', $view->render());
$pautaModel=new App\Models\PautaAduaneira();
$pautaModel->setRawAttributes(['id'=>1,'codigo'=>'0203.11.00','descricao'=>'Mercadoria fictícia para demonstração','iva'=>'14','ieq'=>'0'],true);
$pautaItem=app(App\Application\PautaAduaneira\Services\PublicPautaCatalogue::class)->item($pautaModel);
$pautaListing=['success'=>true,'data'=>[$pautaItem],'meta'=>['total'=>1,'shown'=>1,'per_page'=>20,'current_page'=>1,'last_page'=>1,'catalogue_empty'=>false]];
$pautaData=['filters'=>[],'listing'=>$pautaListing,'selection'=>null,'directory'=>null,'errors'=>new Illuminate\Support\ViewErrorBag()];
file_put_contents($output.'/pauta.html',view('WebSite.consultar_pauta',$pautaData)->render());
$pautaData['selection']=$pautaItem;
$pautaData['directory']=['ready'=>true,'start'=>now()->startOfMonth()->subMonths(12),'end'=>now()->startOfMonth(),'profiles'=>new Illuminate\Pagination\LengthAwarePaginator($profiles,2,3)];
file_put_contents($output.'/pauta-selected.html',view('WebSite.consultar_pauta',$pautaData)->render());
echo "Anonymous views rendered; no database access or real submissions.\n";
