<?php

use App\Models\Menu;
use App\Models\Module;
use App\Support\MenuTree;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
config(['cache.default' => 'array', 'session.driver' => 'array']);
$rows = Menu::orderBy('order_priority')->orderBy('id')->get(['id', 'parent_id', 'module_id', 'menu_name', 'route', 'icon', 'permission'])->toArray();
$modules = Module::pluck('module_name', 'id')->all();
$inventory = [];
foreach ($rows as $row) {
    $route = $row['route'] ? Route::getRoutes()->getByName($row['route']) : null;
    $inventory[] = $row + ['route_exists' => (bool) $route, 'uri' => $route?->uri(), 'methods' => $route?->methods(), 'required_parameters' => $route ? array_values(array_filter($route->parameterNames(), fn ($name) => ! str_contains($route->uri(), '{'.$name.'?}'))) : []];
}
$tree = MenuTree::build($rows);
$sidebar = view('livewire.menu-dinamico', ['menusPrincipais' => $tree, 'modulosAtivos' => array_keys($modules), 'menusPorModulo' => collect($tree)->groupBy('module_id')->map->all()->all(), 'nomesModulos' => $modules, 'facturacaoHongayetuActiva' => false])->render();
$output = storage_path('app/menu-ux-review');
if (! is_dir($output)) mkdir($output, 0777, true);
file_put_contents($output.'/inventory.json', json_encode(['app_url' => config('app.url'), 'scope' => 'All configured menu rows; no tenant/permission filtering; integration hidden', 'modules' => $modules, 'menus' => $inventory], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
$html = Blade::render('<!doctype html><html lang="pt-AO"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">@vite(["resources/css/app.css", "resources/js/app.js"])@livewireStyles<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"><style>body{display:flex;margin:0;height:100vh}aside{width:256px;flex-shrink:0;background:#061d3d;color:white;overflow:auto;padding:20px 12px}main{padding:40px;flex:1;background:#f4f7fb}h1{font-size:28px;font-weight:700}p{margin-top:18px;max-width:650px}#destination{background:white;padding:24px;margin-top:24px;border-radius:12px}</style></head><body><aside><div class="mb-6 px-2 text-lg font-bold">LogiGate · Menu actual</div>{!! $sidebar !!}</aside><main><h1>Revisão do menu lateral</h1><p>Visualização do Blade real com todos os menus configurados. A visibilidade efectiva depende da empresa, subscrição e permissões. Os destinos são inspeccionados sem executar operações.</p><div id="destination">Abra os grupos e seleccione os itens para inspeccionar o destino.</div></main>@livewireScripts<script>document.addEventListener("click",function(e){const a=e.target.closest("aside a");if(!a)return;const href=a.getAttribute("href");if(href!=="#"){e.preventDefault();const panel=document.getElementById("destination");panel.textContent=a.textContent.trim()+" → "+href;}});</script></body></html>', compact('sidebar'));
file_put_contents($output.'/actual.html', $html);
echo json_encode(['menu_count' => count($rows), 'root_count' => count($tree), 'invalid_routes' => array_values(array_filter($inventory, fn ($row) => $row['route'] && $row['route'] !== '#' && ! $row['route_exists'])), 'output' => $output], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
