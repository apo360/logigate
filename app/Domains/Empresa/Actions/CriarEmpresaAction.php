<?php

namespace App\Domains\Empresa\Actions;

use App\Domains\Empresa\Data\EmpresaData;
use App\Domains\Empresa\Repositories\EmpresaRepositoryInterface;
use App\Application\Arquivo\Actions\CriarPastaEmpresaAction;
use App\Models\Empresa;
use Illuminate\Support\Facades\DB;

final class CriarEmpresaAction
{
    public function __construct(
        private readonly EmpresaRepositoryInterface $empresas,
        private readonly GerarCodigoContaEmpresaAction $gerarConta,
        private readonly CriarPastaEmpresaAction $criarPastaEmpresa,
    ) {
    }

    public function execute(EmpresaData $data): Empresa
    {
        $actor = auth()->user();
        abort_unless($actor instanceof \App\Models\User
            && \App\Support\TenantContext::empresaId($actor)
            && $actor->hasRole('Administrador')
            && \App\Support\BusinessAuthorization::allows($actor, 'empresas.create'), 403);
        // Snapshot effective business authority before entering the new company.
        $permissions = $actor->getAllPermissions()->filter(fn ($p) => \App\Support\CompanyRbac::isBusinessPermission($p))->unique('id')->all();
        return $this->persist($data, $actor, $permissions);
    }

    /** The existing public Fortify registration contract creates the first company. */
    public function executeForNewUser(EmpresaData $data, \App\Models\User $creator): Empresa
    {
        abort_unless(! auth()->check() && $creator->wasRecentlyCreated
            && DB::transactionLevel() > 0 && ! $creator->empresas()->exists(), 403);
        // Bootstrap against the business permission catalog, not global role grants/templates.
        $permissions = \Spatie\Permission\Models\Permission::where('guard_name', 'web')->get()
            ->filter(fn ($p) => \App\Support\CompanyRbac::isBusinessPermission($p))->all();
        return $this->persist($data, $creator, $permissions);
    }

    private function persist(EmpresaData $data, \App\Models\User $actor, array $permissions): Empresa
    {
        return DB::transaction(function () use ($data, $actor, $permissions): Empresa {
            
            $attributes = $data->toAttributes();

            $attributes['conta'] ??= $this->gerarConta->execute();

            $empresa = $this->empresas->create($attributes);
            $empresa->users()->attach($actor->id, ['conta' => $empresa->conta]);
            \App\Support\CompanyRbac::within((int) $empresa->id, function () use ($empresa, $actor, $permissions): void {
                $role = \Spatie\Permission\Models\Role::query()->create([
                    'name' => 'Administrador', 'guard_name' => 'web', 'empresa_id' => $empresa->id,
                ]);
                $role->syncPermissions($permissions);
                $actor->assignRole($role);
            });

            DB::afterCommit(fn () => $this->criarPastaEmpresa->execute($empresa));

            return $empresa;
        });
    }
}
