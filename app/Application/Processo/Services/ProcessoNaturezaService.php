<?php

declare(strict_types=1);

namespace App\Application\Processo\Services;

use App\Application\Processo\Support\ExportacaoCrudeFormSupport;
use App\Application\Processo\Support\ProcessoFormSupport;
use App\Domains\Processo\Enums\NaturezaOperacaoProcesso;
use App\Models\Processo;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class ProcessoNaturezaService
{
    public function assertCreate(array $data): void
    {
        if (! Schema::hasColumn('processos', 'natureza_operacao')) {
            throw new \RuntimeException('Actualize o schema da natureza de operação antes de criar processos.');
        }
        $natureza = NaturezaOperacaoProcesso::from($data['natureza_operacao']);
        $this->validate($data, $natureza);
    }

    public function assertUpdate(Processo $processo, array $changes): void
    {
        if (array_key_exists('natureza_operacao', $changes)
            && $changes['natureza_operacao'] !== $processo->natureza_operacao) {
            throw ValidationException::withMessages(['natureza_operacao' => 'A natureza da operação não pode ser alterada pela edição do processo.']);
        }

        // Registos anteriores à classificação mantêm os seus dados, sem conversão automática.
        $natureza = NaturezaOperacaoProcesso::tryFrom((string) $processo->natureza_operacao);
        if ($natureza !== null) {
            $this->validate(array_replace($processo->getAttributes(), $changes), $natureza);
        }
    }

    private function validate(array $data, NaturezaOperacaoProcesso $natureza): void
    {
        $support = app(ExportacaoCrudeFormSupport::class);
        $common = app(ProcessoFormSupport::class);
        Validator::make($data,
            $natureza === NaturezaOperacaoProcesso::EXPORTACAO_CRUDE ? $support->rules() : $support->generalRules(),
            $common->messages(), $common->attributes()
        )->validate();
    }
}
