<?php

namespace App\Observers;

use App\Models\Processo;
use App\Support\ActorContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use OwenIt\Auditing\Models\Audit;

class ProcessoObserver
{
    public function creating(Processo $processo): void
    {
        if (empty($processo->user_id)) {
            $processo->user_id = ActorContext::id();
        }

        if ($processo->getTable() === 'processos') {
            $processo->NrProcesso = Processo::generateNewProcesso($processo->empresa_id);
        }

        Audit::create([
            'user_type' => ActorContext::primaryRole() ?? 'sem-perfil',
            'user_id' => ActorContext::id(),
            'event' => 'novo_processo',
            'new_values' => ['message' => 'Usuário registrou um novo processo ' . $processo->NrProcesso],
            'url' => ActorContext::requestUrl(),
            'ip_address' => ActorContext::ipAddress(),
            'user_agent' => ActorContext::userAgent(),
            'auditable_type' => ActorContext::user() ? get_class(ActorContext::user()) : null,
            'auditable_id' => ActorContext::id(),
        ]);

        Log::info('Novo processo criado', [
            'NrProcesso' => $processo->NrProcesso,
            'user_id' => ActorContext::id(),
            'empresa_id' => $processo->empresa_id,
            'data' => now(),
        ]);
    }

    public function updating(Processo $processo): void
    {
        if ($processo->isDirty(['Estado'])) {
            Log::info('Alteração no processo:', [
                'processo_id' => $processo->id,
                'user_id' => ActorContext::id(),
                'alteracao' => 'Estado alterado de ' . $processo->getOriginal('Estado') . ' para ' . $processo->Estado,
                'data' => now(),
            ]);
        }

        if ($processo->Estado === 'concluido' && $processo->isDirty('Estado')) {
            throw new \Exception('Não é permitido alterar o estado de um processo concluído.');
        }

        $alteracoes = $processo->getDirty();
        $originais = $processo->getOriginal();
        $detalhes = [];

        foreach ($alteracoes as $campo => $valorNovo) {
            $detalhes[$campo] = [
                'antes' => $originais[$campo] ?? null,
                'depois' => $valorNovo,
            ];
        }

        Audit::create([
            'user_type' => ActorContext::primaryRole() ?? 'sem-perfil',
            'user_id' => ActorContext::id(),
            'event' => 'Actualização do Processo ' . $processo->NrProcesso,
            'old_values' => $originais,
            'new_values' => $detalhes,
            'url' => ActorContext::requestUrl(),
            'ip_address' => ActorContext::ipAddress(),
            'user_agent' => ActorContext::userAgent(),
            'auditable_type' => get_class($processo),
            'auditable_id' => $processo->id,
        ]);
    }

    public function deleting(Processo $processo): void
    {
        if ($processo->Estado === ['Retido', 'Finalizado']) {
            throw new \Exception('Processos concluídos não podem ser excluídos.');
        }

        Log::info('Processo excluído', [
            'processo_id' => $processo->id,
            'user_id' => ActorContext::id(),
            'motivo' => request('motivo') ?? 'Não especificado',
            'data' => now(),
        ]);

        DB::table('processos_historico')->insert($processo->toArray());
    }
}
