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
        return DB::transaction(function () use ($original) {
            // Dados básicos, excluindo campos que não devem ser copiados
            $dados = $original->toArray();
            unset(
                $dados['id'],
                $dados['created_at'],
                $dados['updated_at'],
                $dados['txt_gerado'],      // reiniciar estado do TXT
                $dados['codigo_licenciamento'] // será gerado novamente
            );

            $dados['codigo_licenciamento'] = $this->geradorCodigo->gerar((int) $original->empresa_id);

            // Criar novo licenciamento
            $novo = Licenciamento::create($dados);

            // Duplicar mercadorias
            foreach ($original->mercadorias as $merc) {
                $novaMerc = $merc->replicate();
                $novaMerc->licenciamento_id = $novo->id;
                $novaMerc->save();
            }

            // Duplicar mercadorias agrupadas
            foreach ($original->mercadoriasAgrupadas as $agrup) {
                $novaAgrup = $agrup->replicate();
                $novaAgrup->licenciamento_id = $novo->id;
                $novaAgrup->save();
            }

            // (Opcional) Duplicar documentos? Normalmente não, porque documentos são específicos.
            // Se quiser, pode replicar também, mas ajuste conforme regra de negócio.

            return $novo;
        });
    }

}
