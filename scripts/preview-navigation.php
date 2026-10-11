<?php

require dirname(__DIR__).'/vendor/autoload.php';
$environment = Dotenv\Dotenv::parse(file_get_contents(dirname(__DIR__).'/.env'));
if (($environment['APP_ENV'] ?? '') !== 'testing' || ($environment['DB_DATABASE'] ?? '') !== 'logigate_testing'
    || ! in_array($environment['DB_HOST'] ?? '', ['127.0.0.1', 'localhost'], true) || ! empty($environment['DB_URL'])) {
    throw new RuntimeException('Preview restricted to the confirmed local application.');
}
unset($environment);
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (config('database.connections.mysql.database') !== 'logigate_testing'
    || ! in_array(config('database.connections.mysql.host'), ['127.0.0.1', 'localhost'], true)) {
    throw new RuntimeException('Unexpected cached database configuration.');
}
config(['cache.default' => 'array', 'session.driver' => 'array']);
$directory = storage_path('app/navigation-rollout');
if (! is_dir($directory)) mkdir($directory, 0777, true);
$memberships = Illuminate\Support\Facades\DB::table('empresa_users')->select('empresa_id', 'user_id')->distinct()->get();
$count = 0;
foreach ($memberships as $membership) {
    $user = App\Models\User::findOrFail($membership->user_id);
    Illuminate\Support\Facades\Auth::setUser($user);
    session()->put('empresa_id', (int) $membership->empresa_id);
    App\Support\CompanyRbac::activate();
    $modules = app(App\Services\EffectiveModuleResolver::class)->ids((int) $membership->empresa_id);
    $navigation = app(App\Services\NavigationResolver::class);
    $groups = $navigation->groups($user, $navigation->dynamic($user, $modules), false);
    $sidebar = view('livewire.menu-dinamico', ['navigationGroups' => $groups])->render();
    $html = Illuminate\Support\Facades\Blade::render('<!doctype html><html lang="pt-AO"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">@vite(["resources/css/app.css", "resources/js/app.js"])@livewireStyles<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"><style>body{display:flex;margin:0;min-height:100vh;background:#f4f7fb}aside{width:270px;max-width:100vw;flex-shrink:0;background:#061d3d;color:white;padding:24px 12px}main{padding:40px;flex:1;min-width:0}h1{font-size:28px;font-weight:700}p{margin-top:16px;max-width:640px}#destination{background:white;padding:24px;margin-top:24px;border-radius:12px}@media(max-width:600px){body{display:block}aside{width:100%}main{padding:20px}}</style></head><body><aside><div class="mb-6 px-2 text-lg font-bold">LogiGate</div>{!! $sidebar !!}</aside><main><h1>Menu integrado</h1><p>Blade real, com módulos e permissões efectivos de um cenário local. Os cliques nesta pré-visualização mostram o destino sem executar operações.</p><div id="destination">Escolha um item do menu.</div></main>@livewireScripts<script>document.addEventListener("click",function(e){const a=e.target.closest("aside a");if(!a)return;e.preventDefault();document.getElementById("destination").textContent=a.textContent.trim()+" → "+a.getAttribute("href");});</script></body></html>', compact('sidebar'));
    file_put_contents($directory.'/preview-'.(++$count).'.html', $html);
}
echo 'Previews rendered: '.$count."\n";
