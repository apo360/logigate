<?php
namespace App\Application\Importacao;

use App\Models\Empresa;
use App\Models\User;
use App\Support\CompanyRbac;
use App\Support\TenantContext;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

final class ImportExecutionContext
{
    public function run(User $actor, Empresa $empresa, string $model, callable $callback): mixed
    {
        abort_unless(TenantContext::userBelongsToEmpresa($actor, $empresa->id), 403);
        $guard = Auth::guard('web');
        $previousUser = $guard->user();
        $previousEmpresa = session('empresa_id');
        $previousCorrelation = request()->attributes->get('v1_operation_id');
        request()->attributes->set('v1_operation_id', (string) \Illuminate\Support\Str::uuid());
        $guard->setUser($actor);
        session()->put('empresa_id', (int) $empresa->id);
        try {
            return CompanyRbac::within($empresa->id, function () use ($actor, $model, $callback) {
                CompanyRbac::forgetUser($actor);
                Gate::forUser($actor)->authorize('create', $model);
                return $callback();
            });
        } finally {
            request()->attributes->set('v1_operation_id', $previousCorrelation);
            $previousUser ? $guard->setUser($previousUser) : $guard->forgetUser();
            $previousEmpresa === null ? session()->forget('empresa_id') : session()->put('empresa_id', $previousEmpresa);
            CompanyRbac::activate();
        }
    }
}
