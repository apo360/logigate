<?php

declare(strict_types=1);

namespace App\Application\Processo\Actions;

use App\Application\Contentor\Actions\ImportarContentorAsycudaAction;
use App\Application\Mercadoria\Actions\CriarMercadoriaAction;
use App\Application\Mercadoria\DTOs\MercadoriaData;
use App\Application\Processo\DTOs\AsycudaImportResolvedData;
use App\Application\Processo\DTOs\CriarProcessoDTO;
use App\Application\Processo\Services\ProcessoTenantAccessService;
use App\Domains\Processo\Enums\EstadoProcessoEnum;
use App\Models\Contentor;
use App\Models\Customer;
use App\Models\Exportador;
use App\Models\Pais;
use App\Models\PautaAduaneira;
use App\Models\RegiaoAduaneira;
use App\Models\TipoTransporte;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ImportarDeclaracaoAsycudaAction
{
    public function __construct(
        private readonly ProcessoTenantAccessService $tenantAccess,
        private readonly CriarProcessoAction $criarProcesso,
        private readonly CriarMercadoriaAction $criarMercadoria,
        private readonly ImportarContentorAsycudaAction $criarContentor,
    ) {
    }

    public function execute(AsycudaImportResolvedData $input): \App\Models\Processo
    {
        return DB::transaction(function () use ($input) {
            $user = Auth::user();
            if (! $user instanceof User) throw new AuthorizationException('Sessão inválida.');
            $empresaId = $this->tenantAccess->empresaIdFor($user);
            if (! $empresaId || ! $this->tenantAccess->canCreateForEmpresa($user, $empresaId)) throw new AuthorizationException('Empresa activa inválida.');

            $mapped = $input->mapped;
            $resolved = $input->resolutions;
            foreach ($mapped['mercadorias'] ?? [] as $index => $_item) {
                if (! array_key_exists($index, $resolved['item_prices'] ?? []) || ! is_numeric($resolved['item_prices'][$index]) || (float) $resolved['item_prices'][$index] < 0) {
                    throw ValidationException::withMessages(['importacao' => 'Confirme um preço unitário interno válido para cada mercadoria.']);
                }
            }
            $customerId = (int) ($resolved['customer_id'] ?? 0);
            $exportadorId = (int) ($resolved['exportador_id'] ?? 0);
            $customer = Customer::query()->forEmpresa($empresaId)->whereKey($customerId)->first();
            $exportador = Exportador::query()->whereKey($exportadorId)->where(function ($q) use ($empresaId) {
                $q->where('empresa_id', $empresaId)->orWhereHas('empresas', fn ($pivot) => $pivot->where('empresas.id', $empresaId));
            })->first();
            if (! $customer || ! $exportador) throw new AuthorizationException('Cliente ou exportador não pertence à empresa activa.');

            $tipo = RegiaoAduaneira::query()->find($resolved['tipo_processo_id'] ?? null);
            $estancia = \App\Models\Estancia::query()->find($resolved['estancia_id'] ?? null);
            $transporte = TipoTransporte::query()->find($resolved['tipo_transporte_id'] ?? null);
            if (! $tipo || ! $estancia || ! $transporte) throw new AuthorizationException('Uma referência do processo deixou de estar disponível.');
            $formaPagamento = $resolved['forma_pagamento'] ?? null;
            $codigoBanco = $resolved['codigo_banco'] ?? null;
            if (! $formaPagamento || ! $codigoBanco) throw ValidationException::withMessages(['importacao' => 'Forma de pagamento e banco são obrigatórios para criar o processo.']);

            $paisOrigem = isset($resolved['Pais_origem']) ? Pais::query()->find($resolved['Pais_origem']) : null;
            $paisDestino = isset($resolved['Pais_destino']) ? Pais::query()->find($resolved['Pais_destino']) : null;
            $nacionalidade = isset($resolved['nacionalidade_transporte']) ? Pais::query()->find($resolved['nacionalidade_transporte']) : null;
            $financialInternal = $resolved['financial_internal'] ?? [];

            $processo = $this->criarProcesso->execute(CriarProcessoDTO::fromArray([
                'customer_id' => $customer->id,
                'user_id' => $user->id,
                'empresa_id' => $empresaId,
                'exportador_id' => $exportador->id,
                'estancia_id' => $estancia->id,
                'TipoProcesso' => (string) $tipo->id,
                'DataAbertura' => now()->toDateString(),
                'Estado' => EstadoProcessoEnum::ABERTO->value,
                'Descricao' => 'Declaração ASYCUDA importada',
                'TipoTransporte' => $transporte->id,
                'registo_transporte' => $mapped['processo']['registo_transporte'] ?? null,
                'nacionalidade_transporte' => $nacionalidade?->id,
                'Pais_origem' => $paisOrigem?->id,
                'Pais_destino' => $paisDestino?->id,
                'forma_pagamento' => $formaPagamento,
                'codigo_banco' => $codigoBanco,
                'Moeda' => $resolved['moeda'] ?? null,
                'Cambio' => $financialInternal['Cambio'] ?? null,
                'ValorTotal' => $financialInternal['ValorTotal'] ?? null,
                'ValorAduaneiro' => $financialInternal['ValorAduaneiro'] ?? null,
                // Mercadoria creation aggregates manually confirmed item prices; an explicit FOB value is applied after that aggregation.
                'fob_total' => 0,
                'frete' => $financialInternal['frete'] ?? null,
                'seguro' => $financialInternal['seguro'] ?? null,
                'cif' => $financialInternal['cif'] ?? null,
            ]));

            $mercadoriasPorUuid = [];
            foreach ($mapped['mercadorias'] ?? [] as $index => $item) {
                $pauta = PautaAduaneira::query()->find($resolved['pautas'][$index] ?? null);
                if (! $pauta) throw ValidationException::withMessages(['importacao' => 'Uma pauta deixou de estar disponível.']);
                $quantity = $item['supplementary_units'][0]['quantity'] ?? $item['quantidade'] ?? 1;
                $unit = $item['supplementary_units'][0]['code'] ?? $item['unidade'] ?? 'UN';
                $mercadoria = $this->criarMercadoria->execute(MercadoriaData::fromLivewire([
                    'codigo_aduaneiro' => $pauta->codigo,
                    'descricao' => $item['descricao'] ?? null,
                    'quantidade' => max(0.01, (float) $quantity),
                    'peso' => max(0, (float) ($item['peso_liquido'] ?? 0)),
                    'unidade' => substr((string) $unit, 0, 10),
                    'ncm_hs' => $item['codigo_aduaneiro'] ?? null,
                    'ncm_hs_numero' => $item['codigo_aduaneiro'] ?? null,
                    'preco_unitario' => $resolved['item_prices'][$index],
                    'contentor_ids' => [],
                    'pauta_change_source' => 'import',
                ], 'processo', (int) $processo->id));
                $externalId = $item['external_id'] ?? null;
                if ($externalId !== null) $mercadoriasPorUuid[$externalId] = $mercadoria;
            }

            $contentoresPorUuid = [];
            foreach ($mapped['contentores'] ?? [] as $index => $container) {
                if (Contentor::query()->where('processo_id', $processo->id)->where('numero', $container['numero'])->exists()) {
                    throw ValidationException::withMessages(['importacao' => 'Há números de contentor duplicados no ficheiro.']);
                }
                $contentor = $this->criarContentor->execute((int) $processo->id, $container);
                $externalId = $container['external_id'] ?? null;
                if ($externalId !== null) $contentoresPorUuid[$externalId] = $contentor;
            }

            foreach ($mapped['contentorMercadorias'] ?? [] as $link) {
                $contentor = $contentoresPorUuid[$link['contentor_external_id'] ?? ''] ?? null;
                $mercadoria = $mercadoriasPorUuid[$link['mercadoria_external_id'] ?? ''] ?? null;
                if (! $contentor || ! $mercadoria) throw ValidationException::withMessages(['importacao' => 'Uma ligação contentor/mercadoria não pôde ser resolvida.']);
                $contentor->mercadorias()->attach($mercadoria->id, [
                    'asycuda_item_id' => $link['asycuda_item_id'] ?? null,
                    'asycuda_link_id' => $link['asycuda_link_id'] ?? null,
                    'codigo_item' => $link['codigo_item'] ?? null,
                ]);
            }

            if (array_key_exists('fob_total', $financialInternal)) {
                DB::table('processos')->where('id', $processo->id)->update(['fob_total' => $financialInternal['fob_total']]);
            }

            return $processo->refresh();
        });
    }
}
