<?php

namespace App\Providers;

use App\Domains\Banco\Repositories\EmpresaBancoRepositoryInterface;
use App\Application\Arquivo\Policies\DocumentoPolicy;
use App\Application\Arquivo\Repositories\DocumentoRepositoryInterface;
use App\Application\Arquivo\Repositories\EloquentDocumentoRepository;
use App\Application\Mercadoria\Repositories\EloquentMercadoriaRepository;
use App\Application\Mercadoria\Repositories\MercadoriaRepositoryInterface;
use App\Application\PautaAduaneira\IA\OpenAIPautaSuggestionProvider;
use App\Application\PautaAduaneira\IA\PautaSuggestionProviderInterface;
use App\Domains\Empresa\Policies\EmpresaPolicy;
use App\Domains\Empresa\Repositories\EloquentEmpresaRepository;
use App\Domains\Empresa\Repositories\EmpresaRepositoryInterface;
use App\Domains\FacturacaoIntegracao\Clients\HongayetuFacturacaoClientInterface;
use App\Domains\FacturacaoIntegracao\Clients\HttpHongayetuFacturacaoClient;
use App\Domains\Integracoes\Repositories\EloquentEmpresaIntegracaoRepository;
use App\Domains\Integracoes\Repositories\EmpresaIntegracaoRepositoryInterface;
use App\Domains\Licenciamento\Repositories\EloquentLicenciamentoRepository;
use App\Domains\Licenciamento\Repositories\LicenciamentoRepositoryInterface;
use App\Domains\Processo\Repositories\EloquentProcessoRepository;
use App\Domains\Processo\Repositories\ProcessoRepositoryInterface;
use App\Domains\Exportadores\Repositories\EloquentExportadorRepository;
use App\Domains\Exportadores\Repositories\ExportadorRepositoryInterface;
use App\Domains\Produtos\Repositories\EloquentProdutoRepository;
use App\Domains\Produtos\Repositories\ProdutoRepositoryInterface;
use App\Domains\PautaAduaneira\Repositories\EloquentPautaAduaneiraRepository;
use App\Domains\PautaAduaneira\Repositories\PautaAduaneiraRepositoryInterface;
use App\Domains\Usuarios\Policies\UsuarioEmpresaPolicy;
use App\Domains\Usuarios\Repositories\EloquentUsuarioRepository;
use App\Domains\Usuarios\Repositories\UsuarioRepositoryInterface;
use App\Infrastructure\Repositories\EloquentEmpresaBancoRepository;
use App\Models\Customer;
use App\Models\DocumentoArquivo;
use App\Models\Empresa;
use App\Models\Licenciamento;
use App\Models\Processo;
use App\Models\Produto;
use App\Models\SalesInvoice;
use App\Models\Subscricao;
use App\Models\User;
use App\Policies\CustomerPolicy;
use App\Policies\LicenciamentoPolicy;
use App\Policies\ProcessoPolicy;
use App\Policies\ProdutoPolicy;
use App\Policies\SalesInvoicePolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Security: register tenant-aware policies for core domain models.
        Gate::policy(Customer::class, CustomerPolicy::class);
        Gate::policy(DocumentoArquivo::class, DocumentoPolicy::class);
        Gate::policy(Empresa::class, EmpresaPolicy::class);
        Gate::policy(Licenciamento::class, LicenciamentoPolicy::class);
        Gate::policy(Processo::class, ProcessoPolicy::class);
        Gate::policy(Produto::class, ProdutoPolicy::class);
        Gate::policy(SalesInvoice::class, SalesInvoicePolicy::class);

        // Restrict log access to privileged administrators only.
        Gate::define('viewLogs', function (User $user): bool {
            return $user->hasAnyRole(['Administrador', 'Admin', 'admin'])
                || $user->getAllPermissions()->contains('name', 'viewLogs');
        });

        // Security: authorize file keys strictly inside tenant namespace.
        Gate::define('accessTenantFile', function (User $user, string $key): bool {
            $empresaId = $user->empresas()->value('empresas.id');

            if (!$empresaId) {
                return false;
            }

            return str_starts_with($key, "empresa/{$empresaId}/files/");
        });

        Gate::define('manageUser', function (User $actor, Empresa $empresa, User $managedUser): bool {
            return app(UsuarioEmpresaPolicy::class)->manageUser($actor, $empresa, $managedUser);
        });

        Gate::define('manageGlobalPermissions', function (User $actor, mixed ...$args): bool {
            return app(UsuarioEmpresaPolicy::class)->manageGlobalPermissions($actor);
        });

        Gate::define('manageIntegrations', function (User $user, Empresa $empresa): bool {
            return $user->empresas()->where('empresas.id', $empresa->id)->exists()
                && (
                    $user->hasAnyRole(['Administrador', 'Admin', 'admin', 'Gestor', 'Super Admin'])
                    || $user->getAllPermissions()->contains('name', 'manage integrations')
                );
        });

        // Security: explicit tenant-aware route model binding prevents cross-tenant IDOR.
        Route::bind('customer', function ($value) {
            $empresaId = Auth::user()?->empresas()->value('empresas.id');
            abort_if(!$empresaId, 404);

            $query = Customer::query();

            if (!Schema::hasColumn('customers', 'deleted_at')) {
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
            $empresaId = Auth::user()?->empresas()->value('empresas.id');
            abort_if(!$empresaId, 404);

            $query = Processo::query();

            if (!Schema::hasColumn('processos', 'deleted_at')) {
                $query->withoutGlobalScope(SoftDeletingScope::class);
            }

            return $query
                ->whereKey($value)
                ->where('empresa_id', $empresaId)
                ->firstOrFail();
        });

        Route::bind('licenciamento', function ($value) {
            $empresaId = Auth::user()?->empresas()->value('empresas.id');
            abort_if(!$empresaId, 404);

            return Licenciamento::query()
                ->whereKey($value)
                ->where('empresa_id', $empresaId)
                ->firstOrFail();
        });

        Route::bind('produto', function ($value) {
            $empresaId = Auth::user()?->empresas()->value('empresas.id');
            abort_if(!$empresaId, 404);

            return Produto::query()
                ->whereKey($value)
                ->where('empresa_id', $empresaId)
                ->firstOrFail();
        });

        Route::bind('subscricao', function ($value) {
            $empresaId = Auth::user()?->empresas()->value('empresas.id');
            abort_if(!$empresaId, 404);

            return Subscricao::query()
                ->whereKey($value)
                ->where('empresa_id', $empresaId)
                ->firstOrFail();
        });

        Route::bind('empresa', function ($value) {
            $user = Auth::user();

            if (!$user) {
                abort(404);
            }

            return Empresa::query()
                ->whereKey($value)
                ->whereHas('users', fn ($query) => $query->where('users.id', $user->id))
                ->firstOrFail();
        });

        \App\Models\ProductPrice::observe(\App\Observers\ProductPriceObserver::class);
        \App\Models\Produto::observe(\App\Observers\ProductObserver::class);
        \App\Models\SalesInvoice::observe(\App\Observers\DocumentoObserver::class);
        \App\Models\ContaCorrente::observe(\App\Observers\ContaCorrenteObserver::class);
        \App\Models\Customer::observe(\App\Observers\CustomerObserver::class);
        \App\Models\Exportador::observe(\App\Observers\ExportadorObserver::class);
        \App\Models\Processo::observe(\App\Observers\ProcessoObserver::class);
        \App\Models\Recibo::observe(\App\Observers\ReciboObserver::class);
    }
}
