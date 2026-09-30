<?php

declare(strict_types=1);

namespace App\Application\Processo\Actions;

use App\Application\Processo\DTOs\AsycudaExportResult;
use App\Application\Processo\Services\ProcessoTenantAccessService;
use App\Infrastructure\Integrations\Asycuda\AsycudaExportValidator;
use App\Infrastructure\Integrations\Asycuda\AsycudaJsonParser;
use App\Infrastructure\Integrations\Asycuda\AsycudaJsonSerializer;
use App\Infrastructure\Integrations\Asycuda\AsycudaProcessoMapper;
use App\Models\Processo;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use RuntimeException;

final class ExportarDeclaracaoAsycudaAction
{
    public function __construct(private readonly ProcessoTenantAccessService $tenantAccess) {}

    public function execute(Processo $processo, ?string $transportIdentifier = null): AsycudaExportResult
    {
        $user = Auth::user();
        if (! $user instanceof User || ! $this->tenantAccess->canAccess($user, $processo)) {
            throw new AuthorizationException('Sem autorização para exportar este processo.');
        }
        $processo->loadMissing([
            'empresa', 'cliente.endereco', 'exportador', 'estancia', 'tipoDeclaracao', 'transporte',
            'nacionalidadeNavio', 'paisOrigem', 'paisDestino', 'mercadorias.pautaAduaneira', 'mercadorias.contentores', 'contentores.mercadorias',
        ]);
        $empresa = $processo->empresa;
        $customer = $processo->cliente;
        $exportador = $processo->exportador;
        if (! $empresa || ! $customer || ! $exportador) throw new RuntimeException('Empresa, cliente e exportador são necessários para exportar.');

        $items = $processo->mercadorias->map(function ($m, $index) {
            $linkedIds = $m->contentores->map(fn ($container) => $container->pivot->asycuda_item_id)
                ->filter(fn ($id) => is_string($id) && \Illuminate\Support\Str::isUuid($id))->unique()->values();
            return [
                'id' => $m->id, 'asycuda_id' => $linkedIds->count() === 1 ? $linkedIds->first() : null, 'itemNumber' => $index + 1,
                'Descricao' => $m->Descricao,
                'codigo_aduaneiro' => $m->codigo_aduaneiro ?: $m->NCM_HS ?: $m->NCM_HS_Numero ?: $m->pautaAduaneira?->codigo,
                'valuationDetails' => [], 'adjustments' => [],
            ];
        })->all();
        $containers = $processo->contentores->map(fn ($c) => [
            'asycuda_id' => $c->asycuda_id, 'numero' => $c->numero, 'tipo' => $c->tipo, 'descricao' => $c->descricao,
            'indicador_carga' => $c->indicador_carga, 'peso_tara' => $c->peso_tara, 'peso_bruto' => $c->peso_bruto,
            'volume_bruto' => $c->volume_bruto, 'unidade_volume_bruto' => $c->unidade_volume_bruto,
            'numero_volumes' => $c->numero_volumes, 'descarregado' => $c->descarregado, 'possui_selo' => $c->possui_selo,
            'resselado' => $c->resselado, 'asycuda_declaration_item_id' => $c->asycuda_declaration_item_id,
            'mercadorias' => $c->mercadorias->map(fn ($m) => [
                'id' => $m->id, 'mercadoria_id' => $m->id, 'asycuda_link_id' => $m->pivot->asycuda_link_id,
                'codigo_item' => $m->pivot->codigo_item,
            ])->all(),
        ])->all();
        $address = $customer->endereco;
        $mapped = (new AsycudaProcessoMapper())->mapExport(
            ['id' => $processo->id, 'tipo_declaracao' => ['abrev' => $processo->tipoDeclaracao?->abrev, 'codigo' => $processo->tipoDeclaracao?->codigo],
                'TipoTransporte' => $processo->transporte ? (string) $processo->transporte->id : null, 'transport_identifier' => filled($transportIdentifier) ? trim($transportIdentifier) : null,
                'nacionalidade_transporte' => $processo->nacionalidadeNavio?->codigo, 'registo_transporte' => $processo->registo_transporte,
                'estancia' => ['code' => $processo->estancia?->cod_estancia], 'paises' => ['origem' => $processo->paisOrigem?->codigo, 'destino' => $processo->paisDestino?->codigo]],
            $empresa->only(['NIF', 'Empresa', 'Cedula', 'Email', 'Contacto_fixo', 'Endereco_completo', 'Provincia', 'Cidade']),
            ['CustomerTaxID' => $customer->CustomerTaxID, 'CompanyName' => $customer->CompanyName, 'Endereco' => $address ? implode(', ', array_filter([$address->BuildingNumber, $address->StreetName, $address->AddressDetail, $address->City, $address->Province])) : null, 'Pais' => $customer->nacionality],
            $exportador->only(['ExportadorTaxID', 'Exportador', 'Endereco', 'Pais']), $items, $containers,
        );
        // Apply confirmed office and transport paths after the pure mapping layer.
        $mapped['offices'] = ['clearance' => ['code' => $processo->estancia?->cod_estancia]];
        $mapped['declarantContactDetails'] = array_filter([
            'email' => $empresa->Email,
            'name' => $empresa->Empresa,
            'phone' => $empresa->Contacto_fixo ?: $empresa->Contacto_movel,
        ], fn ($value) => filled($value));
        $validation = (new AsycudaExportValidator())->validate($mapped);
        if ($validation['errors'] !== []) throw new RuntimeException(implode(' ', $validation['errors']));
        $json = (new AsycudaJsonSerializer())->serialize($mapped);
        $roundTrip = (new AsycudaJsonParser())->parse($json);
        if (! $roundTrip->success) throw new RuntimeException('O JSON produzido não passou novamente pelo parser ASYCUDA.');
        $number = preg_replace('/[^A-Za-z0-9_-]+/', '-', (string) $processo->NrProcesso) ?: (string) $processo->id;
        return new AsycudaExportResult('asycuda-processo-' . Str::limit(trim($number, '-'), 80, '') . '.json', $json, $validation['warnings']);
    }
}
