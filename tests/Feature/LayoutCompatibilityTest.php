<?php

namespace Tests\Feature;

use App\Livewire\MenuBuilder;
use App\Livewire\SubscriptionWidget;
use App\Models\Menu;
use App\Models\Plano;
use App\Models\Subscricao;
use App\Models\User;
use App\Support\MenuTree;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class LayoutCompatibilityTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        config(['session.driver' => 'array', 'cache.default' => 'array', 'mail.default' => 'array']);
        Http::preventStrayRequests();
        Mail::fake();
        $actor = new MenuCompatibilityActor;
        $actor->setRawAttributes(['id' => 1, 'name' => 'Utilizador de teste']);
        Auth::setUser($actor);
        Carbon::setTestNow(Carbon::parse('2026-10-10 12:00:00'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function widget(?string $start, ?string $end, string $status = 'ativa'): SubscriptionWidget
    {
        $widget = new SubscriptionWidget;
        $subscription = new Subscricao;
        $subscription->setRawAttributes(['id' => 1, 'data_inicio' => $start, 'data_expiracao' => $end, 'status' => $status]);
        $widget->subscricao = $subscription;

        return $widget;
    }

    public function test_fractional_days_and_recent_expiration_keep_correct_sign(): void
    {
        $future = $this->widget('2026-10-01', '2026-10-11');
        $this->assertSame(1, $future->diasRestantes);
        $this->assertFalse($future->expirada);
        $past = $this->widget('2026-10-01', '2026-10-10');
        $this->assertSame(-1, $past->diasRestantes);
        $this->assertTrue($past->expirada);
    }

    public function test_missing_dates_and_invalid_intervals_do_not_invent_progress(): void
    {
        $widget = $this->widget(null, null);
        $this->assertNull($widget->diasRestantes);
        $this->assertFalse($widget->expirada);
        $this->assertSame(0, $widget->percentualRestante);
        $this->assertSame(0, $this->widget('2026-11-01', '2026-10-01')->percentualRestante);
        $this->assertSame(100, $this->widget('2026-10-11', '2026-11-11')->percentualRestante);
        $this->assertSame(0, $this->widget('2026-09-01', '2026-10-01')->percentualRestante);
    }

    public function test_tree_has_no_duplicate_children_or_inaccessible_orphan_roots(): void
    {
        $rows = [['id' => 1, 'parent_id' => null], ['id' => 2, 'parent_id' => 1], ['id' => 3, 'parent_id' => 99], ['id' => 4, 'parent_id' => 5], ['id' => 5, 'parent_id' => 4]];
        $tree = MenuTree::build($rows);
        $this->assertCount(1, $tree);
        $this->assertSame(2, $tree[0]['children'][0]['id']);
        $this->assertCount(1, $tree[0]['children']);
        $this->assertCount(2, MenuTree::build($rows, true));
        $this->assertFalse(MenuTree::active(['route' => null], 'dashboard'));
        $this->assertTrue(MenuTree::active(['route' => null, 'children' => [['route' => 'dashboard']]], 'dashboard'));
    }

    public function test_grouped_menu_renders_without_view_queries_or_missing_module_class(): void
    {
        $menus = array_map(fn ($id) => ['id' => $id, 'parent_id' => null, 'module_id' => 1, 'route' => 'missing.legacy.route', 'icon' => 'fa fa-folder', 'menu_name' => 'Menu '.$id, 'children' => []], range(1, 11));
        $html = view('livewire.menu-dinamico', ['modulosAtivos' => [1, 2], 'menusPrincipais' => $menus, 'menusPorModulo' => [1 => $menus], 'nomesModulos' => [1 => 'Operações'], 'facturacaoHongayetuActiva' => false])->render();
        $this->assertStringContainsString('Operações', $html);
        $this->assertStringContainsString('Menu 11', $html);
    }

    public function test_builder_saves_using_modules_and_dispatches_livewire_three_event(): void
    {
        $moduleId = DB::table('modules')->insertGetId(['module_name' => 'Teste']);
        Livewire::test(MenuBuilder::class)->call('create')->set('menu_name', 'Menu de teste')->set('module_id', $moduleId)->set('route', 'dashboard')->call('save')->assertHasNoErrors()->assertDispatched('toast', type: 'success', message: 'Menu salvo.');
        $this->assertSame(1, Menu::count());
    }

    public function test_reorder_is_atomic_and_rejects_cycles(): void
    {
        $first = Menu::create(['menu_name' => 'A', 'parent_id' => null, 'order_priority' => 0]);
        $second = Menu::create(['menu_name' => 'B', 'parent_id' => null, 'order_priority' => 1]);
        $component = Livewire::test(MenuBuilder::class);
        $component->call('saveOrder', [['id' => $first->id, 'parent_id' => $second->id, 'order' => 0], ['id' => $second->id, 'parent_id' => $first->id, 'order' => 0]])->assertHasErrors('parent_id');
        $this->assertNull($first->fresh()->parent_id);
        $this->assertNull($second->fresh()->parent_id);
        $component->call('saveOrder', [['id' => $first->id, 'parent_id' => null, 'order' => 0], ['id' => $second->id, 'parent_id' => $first->id, 'order' => 0]])->assertDispatched('toast', type: 'success', message: 'Ordem atualizada.');
        $this->assertSame($first->id, (int) $second->fresh()->parent_id);
    }

    public function test_permission_is_checked_again_on_actions(): void
    {
        $component = Livewire::test(MenuBuilder::class);
        Auth::user()->allowed = false;
        $component->call('saveOrder', [])->assertForbidden();
        $this->assertSame(0, Menu::count());
    }

    public function test_pending_and_cancelled_widget_states_are_not_presented_as_active(): void
    {
        Livewire::test(CompatibilityWidgetFixture::class, ['status' => 'pendente'])->assertSee('Pagamento pendente')->assertSee('Pagar');
        Livewire::test(CompatibilityWidgetFixture::class, ['status' => 'cancelada'])->assertSee('Cancelada')->assertSee('Ver planos')->assertDontSee('Expira ');
        Livewire::test(CompatibilityWidgetFixture::class, ['status' => 'ativa'])->assertSee('Expira 11/10/2026');
    }
}

class CompatibilityWidgetFixture extends SubscriptionWidget
{
    public function mountRequiresActiveEmpresa(): void {}

    public function mount($status = 'ativa'): void
    {
        $this->subscricao = new Subscricao;
        $this->subscricao->setRawAttributes(['data_inicio' => '2026-10-01', 'data_expiracao' => '2026-10-11', 'status' => $status]);
        $this->subscricao->setRelation('plano', (new Plano)->setRawAttributes(['nome' => 'Plano de teste']));
    }
}

class MenuCompatibilityActor extends User
{
    public bool $allowed = true;

    public function can($abilities, $arguments = []): bool
    {
        return $this->allowed;
    }
}
