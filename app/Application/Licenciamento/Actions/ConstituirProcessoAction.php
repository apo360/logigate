<?php

namespace App\Application\Licenciamento\Actions;

use App\Application\Processo\Actions\CriarProcessoAction;
use App\Application\Processo\DTOs\CriarProcessoDTO;
use App\Domains\Processo\Enums\EstadoProcessoEnum;
use App\Models\Licenciamento;
use App\Models\Processo;
use App\Models\ProcLicenFactura;
use App\Models\Mercadoria;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class ConstituirProcessoAction
{
    public function __construct(private CriarProcessoAction $criarProcesso)
    {
    }

    /**
     * @throws \Exception
     */
    public function execute(Licenciamento $licenciamento, ?int $userId = null): Processo
    {
        $userId = $userId ?? Auth::id();

        return DB::transaction(function () use ($licenciamento, $userId) {
            if (Schema::hasTable('proc_licen_sales')) {
                $existe = ProcLicenFactura::where('licenciamento_id', $licenciamento->id)->first();

                if ($existe?->processo_id) {
                    $processoQuery = Processo::query();

                    if (!Schema::hasColumn('processos', 'deleted_at')) {
                        $processoQuery->withoutGlobalScope(SoftDeletingScope::class);
                    }

                    return $processoQuery
                        ->where('empresa_id', $licenciamento->empresa_id)
                        ->findOrFail((int) $existe->processo_id);
                }
            }

            $processo = $this->criarProcesso->execute(CriarProcessoDTO::fromArray([
                'ContaDespacho'       => $licenciamento->referencia_cliente,
                'RefCliente'          => $licenciamento->referencia_cliente,
                'estancia_id'         => $licenciamento->estancia_id,
                'Descricao'           => $licenciamento->descricao,
                'DataAbertura'        => now()->toDateString(),
                'TipoProcesso'        => $licenciamento->tipo_declaracao,
                'Estado'              => EstadoProcessoEnum::ABERTO,
                'customer_id'         => $licenciamento->cliente_id,
                'user_id'             => $userId,
                'empresa_id'          => $licenciamento->empresa_id,
                'exportador_id'       => $licenciamento->exportador_id,
                'forma_pagamento'     => $licenciamento->forma_pagamento,
                'fob_total'           => $licenciamento->fob_total,
                'frete'               => $licenciamento->frete,
                'seguro'              => $licenciamento->seguro,
                'codigo_banco'        => $licenciamento->codigo_banco,
                'peso_bruto'          => $licenciamento->peso_bruto,
                'TipoTransporte'      => $licenciamento->tipo_transporte,
                'registo_transporte'  => $licenciamento->registo_transporte,
                'nacionalidade_transporte' => $licenciamento->nacionalidade_transporte,
                'Moeda'               => $licenciamento->moeda,
                'Cambio'              => 1.0,
                'ValorTotal'          => $licenciamento->cif,
                'cif'                 => $licenciamento->cif,
                'ValorAduaneiro'      => $licenciamento->cif + $licenciamento->frete + $licenciamento->seguro,
            ]));

            if (Schema::hasTable('proc_licen_sales')) {
                ProcLicenFactura::updateOrCreate(
                    ['licenciamento_id' => $licenciamento->id],
                    [
                        'empresa_id' => $licenciamento->empresa_id,
                        'processo_id' => $processo->id,
                    ]
                );
            }

            if (Schema::hasTable('mercadorias') && Schema::hasColumn('mercadorias', 'Fk_Importacao')) {
                Mercadoria::where('licenciamento_id', $licenciamento->id)
                    ->update(['Fk_Importacao' => $processo->id]);
            }

            return $processo;
        });
    }
}
