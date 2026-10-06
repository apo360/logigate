<?php

// Fixture-only local review. No application endpoints or real submissions are served.
if (PHP_SAPI === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (str_starts_with($path, '/build/') || str_starts_with($path, '/dist/img/LandingPage/') || in_array($path, ['/dist/img/LOGIGATE.png', '/favicon.ico', '/favicon-32x32.png', '/favicon-16x16.png', '/apple-touch-icon.png'], true)) return false;
    if ($path !== '/consultar-pauta-aduaneira') { http_response_code(404); echo 'Application endpoints disabled in preview.'; return; }
    header('Content-Type: text/html; charset=UTF-8');
    readfile(__DIR__.'/../storage/app/pauta-consulta-qa/'.(isset($_GET['pauta_id']) ? 'selected.html' : 'index.html'));
    return;
}
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['session.driver'=>'array','cache.default'=>'array','mail.default'=>'array']);
Illuminate\Support\Facades\Http::preventStrayRequests(); Illuminate\Support\Facades\Mail::fake();
$app->instance('request', Illuminate\Http\Request::create('http://127.0.0.1:8126/consultar-pauta-aduaneira'));
Illuminate\Support\Facades\URL::forceRootUrl('http://127.0.0.1:8126');
$output = storage_path('app/pauta-consulta-qa'); if (!is_dir($output)) mkdir($output,0777,true);
$catalogue = app(App\Application\PautaAduaneira\Services\PublicPautaCatalogue::class);
$items = [];
foreach(range(1,31) as $id) {
    $model = new App\Models\PautaAduaneira();
    $model->setRawAttributes(['id'=>$id,'codigo'=>sprintf('0203.%02d.00',$id),'descricao'=>'Mercadoria de demonstração '.$id,'uq'=>'kg','iva'=>$id===1?null:'0','ieq'=>'14','rg'=>'5','sadc'=>'N/A','ua'=>null,'requisitos'=>'Requisito fictício para revisão visual','observacao'=>null,'updated_at'=>'2026-01-02 10:00:00'],true);
    $items[] = $catalogue->item($model);
}
file_put_contents($output.'/fixtures.json',json_encode($items,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
$listing=['success'=>true,'data'=>array_slice($items,0,20),'meta'=>['total'=>31,'shown'=>20,'current_page'=>1,'last_page'=>2,'per_page'=>20,'catalogue_empty'=>false]];
$profiles=[(object)['id'=>1,'public_name'=>'Prestador de demonstração','public_location'=>'Luanda','operations'=>18,'active_months'=>7,'last_operation'=>'2026-09-15'],(object)['id'=>2,'public_name'=>'Especialista de demonstração','public_location'=>null,'operations'=>null,'active_months'=>null,'last_operation'=>null]];
foreach([false,true] as $selected) {
    $directory=$selected?['start'=>now()->startOfMonth()->subMonths(12),'end'=>now()->startOfMonth(),'profiles'=>new Illuminate\Pagination\LengthAwarePaginator($profiles,2,3)]:null;
    $filters=['q'=>'','tipo'=>'auto','per_page'=>20]; if($selected)$filters+=['pauta_id'=>1,'codigo'=>$items[0]['codigo']];
    $view=view('WebSite.consultar_pauta',['filters'=>$filters,'listing'=>$listing,'selection'=>$selected?$items[0]:null,'directory'=>$directory,'errors'=>new Illuminate\Support\ViewErrorBag()]);
    $html=str_replace(['+244948242262','+244 948 242 262','geral@hongayetu.com'],['+244000000000','+244 000 000 000','apoio@example.test'],$view->render());
    file_put_contents($output.'/'.($selected?'selected.html':'index.html'),$html);
}
echo "Anonymous Pauta fixtures rendered without database access or real integrations.\n";
