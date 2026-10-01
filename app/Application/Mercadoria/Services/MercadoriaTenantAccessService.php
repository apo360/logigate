<?php

declare(strict_types=1);

namespace App\Application\Mercadoria\Services;

use App\Application\Licenciamento\Services\LicenciamentoTenantAccessService;
use App\Models\Licenciamento;
use App\Models\Mercadoria;
use App\Models\Processo;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

final class MercadoriaTenantAccessService
{
    public function __construct(
        private readonly LicenciamentoTenantAccessService $licenciamentos,
    ) {
    }

    public function authorizeContext(?User $user, string $context, int $parentId, ?string $permission = null): Licenciamento|Processo
    {
        $this->ensureAuthenticated($user);

        return match ($context) {
            'licenciamento' => $this->authorizeLicenciamento($user, $parentId, $permission),
            'processo' => $this->authorizeProcesso($user, $parentId, $permission),
            default => throw new AuthorizationException('Contexto de mercadoria inválido.'),
        };
    }

    public function authorizeLicenciamento(?User $user, int|Licenciamento $licenciamento, ?string $permission = null): Licenciamento
    {
        $this->ensureAuthenticated($user);
        $model = $licenciamento instanceof Licenciamento
            ? $licenciamento
            : (new Licenciamento())->newQueryWithoutScopes()->findOrFail($licenciamento);

        if (! $this->licenciamentos->canAccess($user, $model)) {
            throw new AuthorizationException('Este licenciamento não pertence à empresa ativa.');
        }

        $this->authorizeOptionalPermission($user, $permission);

        return $model;
    }

    public function authorizeMercadoria(?User $user, int|Mercadoria $mercadoria, string $context, int $parentId, ?string $permission = null): Mercadoria
    {
        $this->authorizeContext($user, $context, $parentId, $permission);

        $model = $mercadoria instanceof Mercadoria
            ? $mercadoria
            : Mercadoria::query()->findOrFail($mercadoria);

        $belongsToContext = match ($context) {
            'licenciamento' => (int) $model->licenciamento_id === $parentId,
            'processo' => (int) $model->Fk_Importacao === $parentId,
            default => false,
        };

        if (! $belongsToContext || ! $this->belongsToActiveEmpresa($user, $model)) {
            throw new AuthorizationException('Mercadoria fora do contexto informado.');
        }

        return $model;
    }

    public function belongsToActiveEmpresa(User $user, Mercadoria $mercadoria): bool
    {
        $empresaId = \App\Support\TenantContext::empresaId($user);
        if (! $empresaId) {
            return false;
        }
        // Every supplied owner must agree; a valid parent cannot mask a foreign one.
        $owners = [];
        if ($mercadoria->getAttribute('empresa_id') !== null) {
            $owners[] = (int) $mercadoria->getAttribute('empresa_id');
        }
        foreach (['Fk_Importacao' => Processo::class, 'licenciamento_id' => Licenciamento::class] as $key => $type) {
            if ($mercadoria->getAttribute($key) !== null) {
                $parent = (new $type())->newQueryWithoutScopes()->find($mercadoria->getAttribute($key));
                if (! $parent) {
                    return false;
                }
                $owners[] = (int) $parent->empresa_id;
            }
        }
        return $owners !== [] && count(array_filter($owners, fn (int $owner): bool => $owner !== $empresaId)) === 0;
    }

    private function authorizeProcesso(User $user, int $processoId, ?string $permission = null): Processo
    {
        $processo = (new Processo())->newQueryWithoutScopes()->findOrFail($processoId);
        $empresaId = $this->licenciamentos->empresaIdFor($user);

        if (! $empresaId || (int) $processo->empresa_id !== (int) $empresaId) {
            throw new AuthorizationException('Este processo não pertence à empresa ativa.');
        }

        $this->authorizeOptionalPermission($user, $permission);

        return $processo;
    }

    private function ensureAuthenticated(?User $user): void
    {
        if (! $user) {
            throw new AuthorizationException('Utilizador não autenticado.');
        }
    }

    private function authorizeOptionalPermission(User $user, ?string $permission): void
    {
        if (! $permission) {
            throw new AuthorizationException('Permissão obrigatória.');
        }
        if (! \App\Support\BusinessAuthorization::allows($user, $permission)) {
            throw new AuthorizationException('Sem permissão para esta operação.');
        }
    }
}
