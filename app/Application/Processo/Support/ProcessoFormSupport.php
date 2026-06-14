<?php

declare(strict_types=1);

namespace App\Application\Processo\Support;

use App\Domains\Banco\Services\BancoListService;
use App\Domains\Licenciamento\Enums\TipoTransporte as TipoTransporteEnum;
use App\Domains\Processo\Enums\EstadoProcessoEnum;
use App\Domains\Processo\Enums\FormaPagamentoEnum;
use App\Models\CondicaoPagamento;
use App\Models\Empresa;
use App\Models\Estancia;
use App\Models\MercadoriaLocalizacao;
use App\Models\Pais;
use App\Models\Porto;
use App\Models\RegiaoAduaneira;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

final class ProcessoFormSupport
{
    public function options(Empresa $empresa): array
    {
        return [
            'clientes' => $this->tenantCustomers($empresa),
            'exportadores' => $this->tenantExportadores($empresa),
            'estancias' => Schema::hasTable('estancias') ? Estancia::query()->orderBy('desc_estancia')->get() : collect(),
            'paises' => Pais::query()->orderBy('pais')->get(),
            'portos' => Schema::hasTable('portos') ? Porto::query()->orderBy('porto')->get() : collect(),
            'localMercadoria' => Schema::hasTable('mercadoria_localizacaos') ? MercadoriaLocalizacao::query()->orderBy('descricao')->get() : collect(),
            'condicaoPagamentoOptions' => Schema::hasTable('condicao_pagamentos') ? CondicaoPagamento::query()->orderBy('descricao')->get() : collect(),
            'listaBancos' => BancoListService::getOptions(),
            'tipoProcessoOptions' => Schema::hasTable('regiao_aduaneiras') ? RegiaoAduaneira::query()->orderBy('descricao')->get() : collect(),
            'EstadoOptions' => EstadoProcessoEnum::cases(),
            'tipoTransporte' => TipoTransporteEnum::cases(),
            'formaPagamentoOptions' => FormaPagamentoEnum::cases(),
        ];
    }

    public function rules(int $empresaId, ?int $processoId = null): array
    {
        return [
            'customer_id' => [
                'required',
                'integer',
                Rule::exists('customers', 'id')->where(function ($query) use ($empresaId): void {
                    $query->where(function ($tenantQuery) use ($empresaId): void {
                        $tenantQuery->where('empresa_id', $empresaId);

                        if (Schema::hasTable('customers_empresas')) {
                            $tenantQuery->orWhereExists(function ($pivot) use ($empresaId): void {
                                $pivot->selectRaw('1')
                                    ->from('customers_empresas')
                                    ->whereColumn('customers_empresas.customer_id', 'customers.id')
                                    ->where('customers_empresas.empresa_id', $empresaId);
                            });
                        }
                    });
                }),
            ],
            'exportador_id' => [
                'required',
                'integer',
                Rule::exists('exportadors', 'id')->where(function ($query) use ($empresaId): void {
                    $query->where(function ($tenantQuery) use ($empresaId): void {
                        $tenantQuery->where('empresa_id', $empresaId);

                        if (Schema::hasTable('exportador_empresas')) {
                            $tenantQuery->orWhereExists(function ($pivot) use ($empresaId): void {
                                $pivot->selectRaw('1')
                                    ->from('exportador_empresas')
                                    ->whereColumn('exportador_empresas.exportador_id', 'exportadors.id')
                                    ->where('exportador_empresas.empresa_id', $empresaId);
                            });
                        }
                    });
                }),
            ],
            'estancia_id' => !Schema::hasColumn('processos', 'estancia_id')
                ? ['nullable', 'integer']
                : (Schema::hasTable('estancias')
                ? ['required', 'integer', 'exists:estancias,id']
                : ['required', 'integer']),
            'vinheta' => ['nullable', 'string', 'max:100', Rule::unique('processos', 'vinheta')->ignore($processoId)],
            'TipoProcesso' => Schema::hasTable('regiao_aduaneiras')
                ? ['required', 'exists:regiao_aduaneiras,id']
                : ['required', 'string'],
            'Estado' => ['required', Rule::enum(EstadoProcessoEnum::class)],
            'RefCliente' => ['nullable', 'string', 'max:200'],
            'Descricao' => ['nullable', 'string', 'max:1000'],
            'DataAbertura' => ['nullable', 'date'],
            'DataPartida' => ['nullable', 'date'],
            'DataChegada' => ['nullable', 'date'],
            'Moeda' => ['nullable', 'string', 'size:3'],
            'Cambio' => ['nullable', 'numeric', 'min:0'],
            'fob_total' => ['nullable', 'numeric', 'min:0'],
            'frete' => ['nullable', 'numeric', 'min:0'],
            'seguro' => ['nullable', 'numeric', 'min:0'],
            'cif' => ['nullable', 'numeric', 'min:0'],
            'ValorAduaneiro' => ['nullable', 'numeric', 'min:0'],
            'TipoTransporte' => Schema::hasTable('tipo_transportes')
                ? ['nullable', 'integer', 'exists:tipo_transportes,id']
                : ['nullable'],
            'registo_transporte' => ['nullable', 'string', 'max:100'],
            'nacionalidade_transporte' => ['nullable', 'exists:paises,id'],
            'NrDU' => ['nullable', 'string', 'max:100'],
            'NrDAR' => ['nullable', 'integer', 'min:0'],
            'NrMarcaFiscal' => ['nullable', 'string', 'max:50'],
            'BLC_Porte' => ['nullable', 'string', 'max:50'],
            'Pais_origem' => ['nullable', 'exists:paises,id'],
            'Pais_destino' => ['nullable', 'exists:paises,id'],
            'PortoOrigem' => ['nullable', 'string', 'max:100'],
            'porto_desembarque_id' => Schema::hasTable('portos')
                ? ['nullable', 'exists:portos,id']
                : ['nullable'],
            'localizacao_mercadoria_id' => Schema::hasTable('mercadoria_localizacaos')
                ? ['nullable', 'exists:mercadoria_localizacaos,id']
                : ['nullable'],
            'forma_pagamento' => ['nullable', 'string'],
            'codigo_banco' => ['nullable', 'string', 'max:10'],
            'condicao_pagamento_id' => Schema::hasTable('condicao_pagamentos')
                ? ['nullable', 'exists:condicao_pagamentos,id']
                : ['nullable'],
            'observacoes' => ['nullable', 'string', 'max:1000'],
            'peso_bruto' => ['nullable', 'numeric', 'min:0'],
            'quantidade_barris' => ['nullable', 'integer', 'min:0'],
            'data_carregamento' => ['nullable', 'date'],
            'valor_barril_usd' => ['nullable', 'numeric', 'min:0'],
            'num_deslocacoes' => ['nullable', 'string', 'max:100'],
            'rsm_num' => ['nullable', 'string', 'max:100'],
            'certificado_origem' => ['nullable', 'string', 'max:100'],
            'guia_exportacao' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function calculatedValues(mixed $fobTotal, mixed $frete, mixed $seguro, mixed $cambio): array
    {
        $cif = (float) ($fobTotal ?? 0) + (float) ($frete ?? 0) + (float) ($seguro ?? 0);

        return [
            'cif' => $cif,
            'ValorAduaneiro' => $cif * (float) ($cambio ?: 1),
        ];
    }

    private function tenantCustomers(Empresa $empresa): Collection
    {
        if (Schema::hasTable('customers_empresas')) {
            return $empresa->customers()->orderBy('CompanyName')->get();
        }

        $query = \App\Models\Customer::query();

        if (!Schema::hasColumn('customers', 'deleted_at')) {
            $query->withoutGlobalScope(SoftDeletingScope::class);
        }

        return $query
            ->where('empresa_id', $empresa->id)
            ->orderBy('CompanyName')
            ->get();
    }

    private function tenantExportadores(Empresa $empresa): Collection
    {
        if (Schema::hasTable('exportador_empresas')) {
            return $empresa->exportadors()->orderBy('Exportador')->get();
        }

        return \App\Models\Exportador::query()
            ->where('empresa_id', $empresa->id)
            ->orderBy('Exportador')
            ->get();
    }
}
