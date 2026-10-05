<?php
namespace App\Imports;

use App\Application\Importacao\RowsImport;
use App\Application\Processo\Actions\CriarProcessoAction;
use App\Application\Processo\DTOs\CriarProcessoDTO;
use App\Application\Processo\Support\ProcessoFormSupport;
use Illuminate\Support\Facades\Validator;

class ProcessosImport extends RowsImport
{
    protected function fields(): array { return array_values(array_diff(array_keys(app(ProcessoFormSupport::class)->rules($this->empresa->id)), ['DataPartida'])); }
    protected function create(array $data): int
    {
        foreach (array_keys($data) as $field) {
            $field = ['NrDAR' => 'N_Dar', 'NrMarcaFiscal' => 'MarcaFiscal'][$field] ?? $field;
            if (! \Illuminate\Support\Facades\Schema::hasColumn('processos', $field)) {
                throw new \InvalidArgumentException('Campo indisponível no schema: ' . $field);
            }
        }
        $data += ['Estado' => 'Aberto', 'DataAbertura' => now()->toDateString()];
        $support = app(ProcessoFormSupport::class);
        $validated = Validator::make($data, $support->rules($this->empresa->id))->validate();
        app(\App\Application\Importacao\ImportReferences::class)->validate($this->empresa, $data['customer_id'] ?? null, $data['exportador_id'] ?? null);
        if (($data['Estado'] ?? 'Aberto') !== 'Aberto') { throw new \InvalidArgumentException('A importação cria apenas processos abertos.'); }
        return app(CriarProcessoAction::class)->execute(CriarProcessoDTO::fromArray($validated + ['empresa_id' => $this->empresa->id, 'user_id' => $this->actor->id]))->id;
    }
}
