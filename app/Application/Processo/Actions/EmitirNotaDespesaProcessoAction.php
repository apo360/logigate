<?php

namespace App\Application\Processo\Actions;

use App\Application\Processo\Services\EmolumentoTarifaTotalsService;
use App\Application\Processo\Services\ProcessoJasperService;
use App\Application\Processo\Services\ProcessoTenantAccessService;
use App\Models\EmolumentoTarifa;
use App\Models\Processo;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

final class EmitirNotaDespesaProcessoAction
{
    public function __construct(
        private readonly ProcessoTenantAccessService $tenantAccess,
        private readonly ProcessoJasperService $jasper,
        private readonly EmolumentoTarifaTotalsService $totals,
    ) {
    }

    public function execute(User $user, Processo $processo): array
    {
        abort_unless($this->tenantAccess->canAccess($user, $processo), 404);
        Gate::forUser($user)->authorize('print', $processo);

        $processo->loadMissing([
            'empresa',
            'cliente',
            'paisOrigem',
            'paisDestino',
            'transporte',
            'emolumentoTarifa',
        ]);

        $tarifa = $processo->emolumentoTarifa
            ?? EmolumentoTarifa::query()->where('processo_id', $processo->id)->first();
        $empresa = $processo->empresa ?? $user->empresas()->first();
        $filename = 'nota_despesa_' . $this->safeName($processo->NrProcesso) . '.pdf';
        $outputName = pathinfo($filename, PATHINFO_FILENAME);
        $outputDirectory = storage_path('app/reports/processos/' . $processo->id);

        $path = $this->jasper->generatePdf('nota_despesa.jrxml', $outputDirectory, $outputName, [
            'Empresa' => $empresa->Empresa ?? '',
            'Designacao' => 'Despachante Oficial',
            'Cedula' => $empresa->Cedula ?? '',
            'NIF' => $empresa->NIF ?? '',
            'P_user' => $user->name ?? '',
            'Endereco_completo' => $empresa->Endereco_completo ?? '',
            'Provincia' => optional($empresa?->provincia)->Nome ?? '',
            'logotipo' => $empresa?->Logotipo ? public_path('logos/' . $empresa->Logotipo) : '',
            'Cliente' => optional($processo->cliente)->CompanyName ?? '',
            'Ref_Cliente' => optional($processo->cliente)->RefCliente ?? '',
            'Cli_NIF' => optional($processo->cliente)->CustomerTaxID ?? '',
            'ProcessoID' => $processo->id,
            'NrProcesso' => $processo->NrProcesso ?? '',
            'ContaDespacho' => $processo->ContaDespacho ?? '',
            'Cambio' => $processo->Cambio ?? '0.00',
            'ValorAduaneiro' => $processo->ValorAduaneiro ?? '0.00',
            'Fob_total' => $processo->cif ?? '0.00',
            'Moeda' => $processo->Moeda ?? '',
            'NrDU' => $processo->NrDU ?? '',
            'N_Dar' => $processo->N_Dar ?? '',
            'DataAbertura' => $processo->DataAbertura ?? '',
            'MarcaFiscal' => $processo->MarcaFiscal ?? '',
            'BLC_Porte' => $processo->BLC_Porte ?? '',
            'Pais_origem' => optional($processo->paisOrigem)->pais ?? '',
            'Pais_destino' => optional($processo->paisDestino)->pais ?? '',
            'TipoTransporte' => optional($processo->transporte)->descricao ?? '',
            'registo_transporte' => $processo->registo_transporte ?? '',
            'nacionalidade_transporte' => optional($processo->paisOrigem)->pais ?? '',
            'DataChegada' => $processo->DataChegada ?? '',
            'direitos' => $tarifa->direitos ?? '0.00',
            'emolumentos' => $tarifa->emolumentos ?? '0.00',
            'porto' => $tarifa->porto ?? '0.00',
            'terminal' => $tarifa->terminal ?? '0.00',
            'lmc' => $tarifa->lmc ?? '0.00',
            'navegacao' => $tarifa->navegacao ?? '0.00',
            'inerentes' => $tarifa->inerentes ?? '0.00',
            'frete' => $tarifa->frete ?? '0.00',
            'carga_descarga' => $tarifa->carga_descarga ?? '0.00',
            'deslocacao' => $tarifa->deslocacao ?? '0.00',
            'selos' => $tarifa->selos ?? '0.00',
            'iva_aduaneiro' => $tarifa->iva_aduaneiro ?? '0.00',
            'iec' => $tarifa->iec ?? '0.00',
            'impostoEstatistico' => $tarifa->impostoEstatistico ?? '0.00',
            'juros_mora' => $tarifa->juros_mora ?? '0.00',
            'multas' => $tarifa->multas ?? '0.00',
            'caucao' => $tarifa->caucao ?? '0.00',
            'honorario' => $tarifa->honorario ?? '0.00',
            'honorario_iva' => $tarifa->honorario_iva ?? '0.00',
            'orgaos_ofiais' => $tarifa->orgaos_ofiais ?? '0.00',
            'guia_fiscal' => $this->totals->guiaFiscal($tarifa),
        ]);

        return [
            'path' => $path,
            'filename' => $filename,
            'mime' => 'application/pdf',
        ];
    }

    private function safeName(?string $value): string
    {
        return preg_replace('/[^A-Za-z0-9_-]+/', '-', (string) ($value ?: 'processo'));
    }
}
