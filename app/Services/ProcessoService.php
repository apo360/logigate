<?php

namespace App\Services;

use App\Models\EmolumentoTarifa;
use App\Models\Processo;
use Illuminate\Support\Collection;

class ProcessoService
{
    public function validateForFinalization(Processo $processo): array
    {
        $emolumentoTarifa = EmolumentoTarifa::where('processo_id', $processo->id)->first();
        $erros = [];

        if (empty($processo->NrDU)) {
            $erros[] = 'O campo NrDU é obrigatório.';
        }
        if (empty($processo->BLC_Porte)) {
            $erros[] = 'O campo BLC_Porte é obrigatório.';
        }
        if (empty($processo->ValorAduaneiro)) {
            $erros[] = 'O campo ValorAduaneiro é obrigatório.';
        }
        if (empty($processo->cif)) {
            $erros[] = 'O campo CIF é obrigatório.';
        }
        if (empty($processo->Cambio)) {
            $erros[] = 'O campo Cambio é obrigatório.';
        }
        if ($processo->mercadorias()->count() === 0) {
            $erros[] = 'Deve haver pelo menos uma mercadoria associada ao processo.';
        }
        if (! $emolumentoTarifa || is_null($emolumentoTarifa->honorario) || $emolumentoTarifa->honorario < 0) {
            $erros[] = 'Os campos Honorários e Emolumentos Tarifa não podem ser nulo ou negativo.';
        }

        return $erros;
    }

    public function finalize(Processo $processo): Processo
    {
        $processo->Estado = 'finalizado';
        $processo->DataFecho = now();
        $processo->ContaDespacho = $this->gerarContaDespachoSequencial();
        $processo->save();

        return $processo->refresh();
    }

    public function listarNaoFinalizados(int $empresaId): Collection
    {
        return Processo::whereNotNull('NrDU')
            ->whereNotNull('BLC_Porte')
            ->whereNotNull('ValorAduaneiro')
            ->whereNotNull('cif')
            ->whereNotNull('Cambio')
            ->whereHas('mercadorias')
            ->whereHas('emolumentoTarifa', function ($query) {
                $query->whereNotNull('honorario')->where('honorario', '>=', 0);
            })
            ->where('Estado', '!=', 'finalizado')
            ->where('empresa_id', $empresaId)
            ->get();
    }

    private function gerarContaDespachoSequencial(): string
    {
        $anoCorrente = date('Y');
        $ultimaConta = Processo::whereYear('created_at', $anoCorrente)
            ->whereNotNull('ContaDespacho')
            ->orderByRaw("CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(ContaDespacho, '/', 1), '-', -1) AS UNSIGNED) DESC")
            ->first();

        $sequencial = 1;
        if ($ultimaConta) {
            preg_match('/\d+/', explode('/', $ultimaConta->ContaDespacho)[0], $match);
            $sequencial = isset($match[0]) ? (int) $match[0] + 1 : 1;
        }

        return 'CCD-' . str_pad($sequencial, 3, '0', STR_PAD_LEFT) . '/' . $anoCorrente;
    }
}
