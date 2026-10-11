<?php

namespace App\Providers;

use App\Application\Arquivo\Policies\DocumentoPolicy;
use App\Application\Arquivo\Repositories\DocumentoRepositoryInterface;
use App\Application\Arquivo\Repositories\EloquentDocumentoRepository;
use App\Application\Billing\Contracts\GpoPaymentGateway;
use App\Application\Billing\Contracts\RefPaymentGateway;
use App\Application\Mercadoria\Repositories\EloquentMercadoriaRepository;
use App\Application\Mercadoria\Repositories\MercadoriaRepositoryInterface;
use App\Application\PautaAduaneira\IA\OpenAIPautaSuggestionProvider;
use App\Application\PautaAduaneira\IA\PautaSuggestionProviderInterface;
use App\Domains\Banco\Repositories\EmpresaBancoRepositoryInterface;
use App\Domains\ClientePortal\Repositories\ClientePortalRepositoryInterface;
use App\Domains\ClientePortal\Repositories\EloquentClientePortalRepository;
use App\Domains\Customers\Repositories\CustomerRepositoryInterface;
use App\Domains\Customers\Repositories\EloquentCustomerRepository;
use App\Domains\Empresa\Policies\EmpresaPolicy;
use App\Domains\Empresa\Repositories\EloquentEmpresaRepository;
use App\Domains\Empresa\Repositories\EmpresaRepositoryInterface;
use App\Domains\Exportadores\Repositories\EloquentExportadorRepository;
use App\Domains\Exportadores\Repositories\ExportadorRepositoryInterface;
use App\Domains\FacturacaoIntegracao\Clients\HongayetuFacturacaoClientInterface;
use App\Domains\Integracoes\Repositories\EloquentEmpresaIntegracaoRepository;
use App\Domains\Integracoes\Repositories\EmpresaIntegracaoRepositoryInterface;
use App\Domains\Licenciamento\Repositories\EloquentLicenciamentoRepository;
use App\Domains\Licenciamento\Repositories\LicenciamentoRepositoryInterface;
use App\Domains\PautaAduaneira\Repositories\EloquentPautaAduaneiraRepository;
use App\Domains\PautaAduaneira\Repositories\PautaAduaneiraRepositoryInterface;
use App\Domains\Processo\Repositories\EloquentProcessoRepository;
use App\Domains\Processo\Repositories\ProcessoRepositoryInterface;
use App\Domains\Produtos\Repositories\EloquentProdutoRepository;
use App\Domains\Produtos\Repositories\ProdutoRepositoryInterface;
use App\Domains\Usuarios\Policies\UsuarioEmpresaPolicy;
use App\Domains\Usuarios\Repositories\EloquentUsuarioRepository;
use App\Domains\Usuarios\Repositories\UsuarioRepositoryInterface;
use App\Http\Middleware\EnsureModuleEntitlement;
use App\Infrastructure\FacturacaoIntegracao\Hongayetu\HttpHongayetuFacturacaoClient;
use App\Infrastructure\PaymentGateways\AppyPay\AppyPayGpoClient;
use App\Infrastructure\PaymentGateways\AppyPay\AppyPayRefClient;
use App\Infrastructure\Repositories\EloquentEmpresaBancoRepository;
use App\Models\ContaCorrente;
use App\Models\Customer;
use App\Models\CustomerAvenca;
use App\Models\DocumentoArquivo;
use App\Models\Empresa;
use App\Models\Exportador;
use App\Models\Licenciamento;
use App\Models\Mercadoria;
use App\Models\Processo;
use App\Models\ProductPrice;
use App\Models\Produto;
use App\Models\Subscricao;
use App\Models\User;
use App\Observers\ContaCorrenteObserver;
use App\Observers\CustomerObserver;
use App\Observers\ExportadorObserver;
use App\Observers\OperationalAuditObserver;
use App\Observers\ProcessoObserver;
use App\Observers\ProductObserver;
use App\Observers\ProductPriceObserver;
use App\Policies\ContaCorrentePolicy;
use App\Policies\CustomerAvencaPolicy;
use App\Policies\CustomerPolicy;
use App\Policies\ExportadorPolicy;
use App\Policies\LicenciamentoPolicy;
use App\Policies\MercadoriaPolicy;
use App\Policies\ProcessoPolicy;
use App\Policies\ProdutoPolicy;
use App\Support\BusinessAuthorization;
use App\Support\TenantContext;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(GpoPaymentGateway::class, AppyPayGpoClient::class);
        $this->app->bind(RefPaymentGateway::class, AppyPayRefClient::class);
        $this->app->bind(ProcessoRepositoryInterface::class, EloquentProcessoRepository::class);
        $this->app->bind(LicenciamentoRepositoryInterface::class, EloquentLicenciamentoRepository::class);
        $this->app->bind(EmpresaBancoRepositoryInterface::class, EloquentEmpresaBancoRepository::class);
        $this->app->bind(DocumentoRepositoryInterface::class, EloquentDocumentoRepository::class);
        $this->app->bind(ExportadorRepositoryInterface::class, EloquentExportadorRepository::class);
        $this->app->bind(ProdutoRepositoryInterface::class, EloquentProdutoRepository::class);
        $this->app->bind(MercadoriaRepositoryInterface::class, EloquentMercadoriaRepository::class);
        $this->app->bind(PautaAduaneiraRepositoryInterface::class, EloquentPautaAduaneiraRepository::class);
        $this->app->bind(PautaSuggestionProviderInterface::class, OpenAIPautaSuggestionProvider::class);
        $this->app->bind(EmpresaRepositoryInterface::class, EloquentEmpresaRepository::class);
        $this->app->bind(UsuarioRepositoryInterface::class, EloquentUsuarioRepository::class);
        $this->app->bind(EmpresaIntegracaoRepositoryInterface::class, EloquentEmpresaIntegracaoRepository::class);
        $this->app->bind(HongayetuFacturacaoClientInterface::class, HttpHongayetuFacturacaoClient::class);
        $this->app->bind(CustomerRepositoryInterface::class, EloquentCustomerRepository::class);
        $this->app->bind(ClientePortalRepositoryInterface::class, EloquentClientePortalRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Livewire::addPersistentMiddleware([EnsureModuleEntitlement::class]);
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
        // Security: register tenant-aware policies for core domain models.
        Gate::policy(Customer::class, CustomerPolicy::class);
        Gate::policy(CustomerAvenca::class, CustomerAvencaPolicy::class);
        Gate::policy(ContaCorrente::class, ContaCorrentePolicy::class);
        Gate::policy(DocumentoArquivo::class, DocumentoPolicy::class);
        Gate::policy(Empresa::class, EmpresaPolicy::class);
        Gate::policy(Licenciamento::class, LicenciamentoPolicy::class);
        Gate::policy(Processo::class, ProcessoPolicy::class);
        Gate::policy(Produto::class, ProdutoPolicy::class);
        Gate::policy(Exportador::class, ExportadorPolicy::class);
        Gate::policy(Mercadoria::class, MercadoriaPolicy::class);

        Gate::define('manageEmpresaUser', function (User $actor, Empresa $empresa, User $managed) {
            return app(UsuarioEmpresaPolicy::class)->manageUser($actor, $empresa, $managed);
        });

        Gate::define('manageGlobalPermissions', fn (User $actor): bool => false);
        Gate::define('manageUsers', function (User $actor, Empresa $empresa): bool {
            return app(UsuarioEmpresaPolicy::class)->manageEmpresaPermissions($actor, $empresa);
        });

        Gate::define('manageEmpresaPermissions', function (User $actor, Empresa $empresa) {
            return app(UsuarioEmpresaPolicy::class)->manageEmpresaPermissions($actor, $empresa);
        });

        // Restrict log access to privileged administrators only.
        Gate::define('viewLogs', function (User $user): bool {
            // The global log viewer has no company filter or explicit platform RBAC contract.
            return false;
        });

        // Security: authorize file keys strictly inside tenant namespace.
        Gate::define('accessTenantFile', function (User $user, string $key): bool {
            $empresaId = $user->empresaAtiva()?->id;

            if (! $empresaId) {
                return false;
            }

            return str_starts_with($key, "empresa/{$empresaId}/files/");
        });

        Gate::define('manageUser', function (User $actor, Empresa $empresa, User $managedUser): bool {
            return app(UsuarioEmpresaPolicy::class)->manageUser($actor, $empresa, $managedUser);
        });

        Gate::define('manageIntegrations', function (User $user, Empresa $empresa): bool {
            if (TenantContext::empresaId($user) !== (int) $empresa->id) {
                return false;
            }

            return BusinessAuthorization::allows($user, 'empresas.update');
        });

        // Security: explicit tenant-aware route model binding prevents cross-tenant IDOR.
        Route::bind('customer', function ($value) {
            $empresaId = TenantContext::empresaId();
            abort_if(! $empresaId, 404);

            $query = Customer::query();

            if (! Schema::hasColumn('customers', 'deleted_at')) {
                $query->withoutGlobalScope(SoftDeletingScope::class);
            }

            return $query
                ->whereKey($value)
                ->where(function ($query) use ($empresaId): void {
                    $query->where('empresa_id', $empresaId);

                    if (Schema::hasTable('customers_empresas')) {
                        $query->orWhereHas('empresas', fn ($empresaQuery) => $empresaQuery->where('empresas.id', $empresaId));
                    }
                })
                ->firstOrFail();
        });

        Route::bind('processo', function ($value) {
            $empresaId = TenantContext::empresaId();
            abort_if(! $empresaId, 404);

            $query = Processo::query();

            if (! Schema::hasColumn('processos', 'deleted_at')) {
                $query->withoutGlobalScope(SoftDeletingScope::class);
            }

            return $query
                ->whereKey($value)
                ->where('empresa_id', $empresaId)
                ->firstOrFail();
        });

        Route::bind('licenciamento', function ($value) {
            $empresaId = TenantContext::empresaId();
            abort_if(! $empresaId, 404);

            return Licenciamento::query()
                ->whereKey($value)
                ->where('empresa_id', $empresaId)
                ->firstOrFail();
        });

        Route::bind('produto', function ($value) {
            $empresaId = TenantContext::empresaId();
            abort_if(! $empresaId, 404);

            return Produto::query()
                ->whereKey($value)
                ->where('empresa_id', $empresaId)
                ->firstOrFail();
        });

        Route::bind('subscricao', function ($value) {
            $empresaId = TenantContext::empresaId();
            abort_if(! $empresaId, 404);

            return Subscricao::query()
                ->whereKey($value)
                ->where('empresa_id', $empresaId)
                ->firstOrFail();
        });

        Route::bind('documentoArquivo', function ($value) {
            $empresaId = TenantContext::empresaId();
            abort_if(! $empresaId, 404);

            return DocumentoArquivo::query()->whereKey($value)->where('empresa_id', $empresaId)->firstOrFail();
        });

        Route::bind('empresa', function ($value) {
            $user = Auth::user();

            if (! $user) {
                abort(404);
            }

            return Empresa::query()
                ->whereKey($value)
                ->whereHas('users', fn ($query) => $query->where('users.id', $user->id))
                ->firstOrFail();
        });

        ProductPrice::observe(ProductPriceObserver::class);
        Produto::observe(ProductObserver::class);
        ContaCorrente::observe(ContaCorrenteObserver::class);
        Customer::observe(CustomerObserver::class);
        Exportador::observe(ExportadorObserver::class);
        Processo::observe(ProcessoObserver::class);
        foreach ([Customer::class, Exportador::class, Processo::class, Licenciamento::class, Mercadoria::class] as $model) {
            $model::observe(OperationalAuditObserver::class);
        }
    }
}
