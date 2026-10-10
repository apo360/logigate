<?php

use App\Livewire\MenuBuilder;
use App\Livewire\SubscriptionWidget;
use App\Models\Plano;
use App\Models\Subscricao;
use App\Support\MenuTree;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

if (PHP_SAPI === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);

        return;
    }
    if (str_starts_with($path, '/build/')) {
        return false;
    }
    if ($path === '/livewire/livewire.js') {
        header('Content-Type: application/javascript');
        readfile(__DIR__.'/../vendor/livewire/livewire/dist/livewire.js');

        return;
    }
    if ($path === '/') {
        header('Content-Type: text/html');
        readfile(__DIR__.'/../storage/app/layout-compatibility-qa/index.html');

        return;
    }
    http_response_code(404);

    return;
}
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
config(['app.url' => 'http://127.0.0.1:8129', 'session.driver' => 'array', 'cache.default' => 'array', 'mail.default' => 'array']);
Http::preventStrayRequests();
Mail::fake();
URL::forceRootUrl('http://127.0.0.1:8129');
Carbon\Carbon::setTestNow('2026-10-10 12:00:00');
class LayoutWidgetReview extends SubscriptionWidget
{
    public function mountRequiresActiveEmpresa(): void {}

    public function mount($status = 'ativa'): void
    {
        $this->subscricao = new Subscricao;
        $this->subscricao->setRawAttributes(['data_inicio' => '2026-10-01', 'data_expiracao' => '2026-10-11', 'status' => $status]);
        $this->subscricao->setRelation('plano', (new Plano)->setRawAttributes(['nome' => 'Plano de demonstração']));
    }
}
class LayoutBuilderReview extends MenuBuilder
{
    public function mount(): void
    {
        $this->menusTree = MenuTree::build([
            ['id' => 1, 'parent_id' => null, 'menu_name' => 'Operações', 'route' => 'dashboard'],
            ['id' => 2, 'parent_id' => 1, 'menu_name' => 'Processos', 'route' => 'processos.index'],
            ['id' => 3, 'parent_id' => null, 'menu_name' => 'Clientes', 'route' => 'customers.index'],
        ]);
    }
}

Livewire\Livewire::component('widget-review', LayoutWidgetReview::class);
Livewire\Livewire::component('builder-review', LayoutBuilderReview::class);
$widgets = '';
foreach (['ativa', 'pendente', 'cancelada'] as $status) {
    $widgets .= '<div class="review-topbar flex h-16 items-center gap-4 border-b p-2">'.Livewire\Livewire::mount('widget-review', ['status' => $status]).'</div>';
}
$builder = Livewire\Livewire::mount('builder-review');
$html = Blade::render('<!doctype html><html lang="pt-AO"><head><meta name="viewport" content="width=device-width, initial-scale=1">@vite(["resources/css/app.css", "resources/js/app.js"]) @livewireStyles <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script></head><body><main>{!! $widgets !!}{!! $builder !!}</main>@livewireScripts</body></html>', compact('widgets', 'builder'));
$output = storage_path('app/layout-compatibility-qa');
if (! is_dir($output)) {
    mkdir($output, 0777, true);
}
file_put_contents($output.'/index.html', $html);
echo "Anonymous component fixtures rendered; no database or payment operations.\n";
