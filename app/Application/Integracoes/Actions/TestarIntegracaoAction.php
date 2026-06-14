<?php

namespace App\Application\Integracoes\Actions;

use App\Application\FacturacaoIntegracao\Clients\HongayetuFacturacaoClient;
use App\Application\Integracoes\DTOs\ResultadoTesteIntegracaoDTO;
use App\Domains\Integracoes\Enums\ProvedorIntegracaoEnum;
use App\Models\Empresa;
use App\Models\EmpresaIntegracao;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

final readonly class TestarIntegracaoAction
{
    public function __construct(private HongayetuFacturacaoClient $facturacaoClient)
    {
    }

    public function execute(User $actor, Empresa $empresa, EmpresaIntegracao $integracao): ResultadoTesteIntegracaoDTO
    {
        Gate::forUser($actor)->authorize('manageIntegrations', $empresa);
        abort_unless((int) $integracao->empresa_id === (int) $empresa->id, 403);

        try {
            $result = match ($integracao->provedor) {
                ProvedorIntegracaoEnum::HongayetuFacturacao => $this->facturacaoClient->test($integracao),
                default => ResultadoTesteIntegracaoDTO::success('Configuração base validada. Teste externo ainda não implementado para este provider.'),
            };
        } catch (\Throwable $exception) {
            $result = ResultadoTesteIntegracaoDTO::failure('Falha no teste da integração: ' . $exception->getMessage());
        }

        $integracao->markTestResult($result->success, $result->message);

        return $result;
    }
}
