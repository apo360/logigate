<?php

namespace App\Application\Licenciamento\Actions;

use App\Models\Licenciamento;
use App\Domains\Licenciamento\Services\GeradorCodigoLicenciamentoService;
use Illuminate\Support\Facades\DB;

class DuplicarLicenciamentoAction
{
    public function __construct(private GeradorCodigoLicenciamentoService $geradorCodigo)
    {
    }

    public function execute(Licenciamento $original): Licenciamento
    {
        \Illuminate\Support\Facades\Gate::authorize('view', $original);
        \Illuminate\Support\Facades\Gate::authorize('create', Licenciamento::class);
        abort_unless(\App\Support\BusinessAuthorization::allows(auth()->user(), 'mercadorias.create'), 403);
        return DB::transaction(function () use ($original) {
            // Dados básicos, excluindo campos que não devem ser copiados
            $original = Licenciamento::query()->whereKey($original->id)->lockForUpdate()->firstOrFail();
            $dados = $original->only([
                'empresa_id', 'estancia_id', 'cliente_id', 'exportador_id', 'referencia_cliente',
                'factura_proforma', 'descricao', 'moeda', 'tipo_declaracao', 'tipo_transporte',
                'registo_transporte', 'nacionalidade_transporte', 'porto_entrada', 'peso_bruto',
                'metodo_avaliacao', 'codigo_volume', 'qntd_volume', 'forma_pagamento', 'codigo_banco',
                'fob_total', 'frete', 'seguro', 'cif', 'pais_origem', 'porto_origem',
            ]);
            $dados['txt_gerado'] = false;
            $dados['adicoes'] = 0;

            $dados['codigo_licenciamento'] = $this->geradorCodigo->gerar((int) $original->empresa_id);

            // Criar novo licenciamento
            $novo = Licenciamento::create($dados);

            // Duplicar mercadorias
            foreach ($original->mercadorias()->lockForUpdate()->get() as $merc) {
                $attributes = $merc->only([
                    'Descricao', 'Quantidade', 'Unidade', 'Qualificacao', 'Peso', 'preco_unitario',
                    'preco_total', 'codigo_aduaneiro', 'marca', 'modelo', 'chassis', 'ano_fabricacao',
                    'potencia', 'subcategoria_id', 'pauta_aduaneira_id', 'codigo_pautal_snapshot',
                    'descricao_pautal_snapshot', 'rg_snapshot', 'sadc_snapshot', 'ua_snapshot',
                    'iva_snapshot', 'ieq_snapshot', 'pauta_snapshot_at',
                ]);
                $novaMerc = \App\Models\Mercadoria::create($attributes + ['licenciamento_id' => $novo->id, 'Fk_Importacao' => null]);
                app(\App\Application\Mercadoria\Services\MercadoriaAgrupamentoService::class)->addOrUpdate($novaMerc);
            }

            // Duplicar mercadorias agrupadas

            // (Opcional) Duplicar documentos? Normalmente não, porque documentos são específicos.
            // Se quiser, pode replicar também, mas ajuste conforme regra de negócio.

            return $novo;
        });
    }

}
