<?php

namespace App\Providers;

use App\Domains\Banco\Repositories\EmpresaBancoRepositoryInterface;
use App\Application\Arquivo\Policies\DocumentoPolicy;
use App\Application\Arquivo\Repositories\DocumentoRepositoryInterface;
use App\Application\Arquivo\Repositories\EloquentDocumentoRepository;
use App\Application\Mercadoria\Repositories\EloquentMercadoriaRepository;
use App\Application\Mercadoria\Repositories\MercadoriaRepositoryInterface;
use App\Domains\Licenciamento\Repositories\EloquentLicenciamentoRepository;
use App\Domains\Licenciamento\Repositories\LicenciamentoRepositoryInterface;
use App\Domains\Processo\Repositories\EloquentProcessoRepository;
use App\Domains\Processo\Repositories\ProcessoRepositoryInterface;
use App\Domains\Exportadores\Repositories\EloquentExportadorRepository;
use App\Domains\Exportadores\Repositories\ExportadorRepositoryInterface;
use App\Domains\Produtos\Repositories\EloquentProdutoRepository;
use App\Domains\Produtos\Repositories\ProdutoRepositoryInterface;
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
use App\Policies\ProcessoPolicy;
use App\Policies\ProdutoPolicy;
use App\Policies\SalesInvoicePolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Security: register tenant-aware policies for core domain models.
        Gate::policy(Customer::class, CustomerPolicy::class);
        Gate::policy(DocumentoArquivo::class, DocumentoPolicy::class);
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

        // Security: explicit tenant-aware route model binding prevents cross-tenant IDOR.
        Route::bind('customer', function ($value) {
            return Customer::query()->whereKey($value)->firstOrFail();
        });

        Route::bind('processo', function ($value) {
            return Processo::query()->whereKey($value)->firstOrFail();
        });

        Route::bind('licenciamento', function ($value) {
            return Licenciamento::query()->whereKey($value)->firstOrFail();
        });

        Route::bind('produto', function ($value) {
            return Produto::query()->whereKey($value)->firstOrFail();
        });

        Route::bind('subscricao', function ($value) {
            return Subscricao::query()->whereKey($value)->firstOrFail();
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
