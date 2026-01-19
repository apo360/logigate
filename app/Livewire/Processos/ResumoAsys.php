<?php

namespace App\Livewire\Processos;

use App\Models\Processo;
use App\Models\MercadoriaAgrupada;
use Livewire\Component;

class ResumoAsys extends Component
{
    public Processo $processo;

    // ✅ VALORES PRINCIPAIS
    public float $cif = 0;
    public float $valorAduaneiro = 0;
    public float $totalDespesas = 0;
    public float $totalImpostos = 0;
    public float $totalGeral = 0;
    public float $valorAduaneiroMoedaNacional = 0;

    // ✅ RESUMO MERCADORIAS
    public int $totalItens = 0;
    public float $pesoTotal = 0;
    public float $fobMercadorias = 0;
    public int $agrupamentosUnicos = 0;
    public array $mercadoriasAgrupadas = [];

    // ✅ TAXAS E IMPOSTOS DETALHADOS
    public array $despesasDetalhadas = [];
    public array $impostosDetalhados = [];

    // ✅ PERCENTAGENS
    public float $percentDespesas = 0;
    public float $percentImpostos = 0;
    public float $impostosSobreValorAduaneiro = 0;

    public function mount(Processo $processo)
    {
        // Carregar relações úteis
        $this->processo = $processo->loadMissing([
            'cliente',
            'exportador',
            'paisOrigem',
            'paisDestino',
            'portoDesembarque',
            'emolumentoTarifa',
            'mercadorias',
            'mercadorias.subcategoria',
        ]);

        $this->calcularTotais();
        $this->calcularResumoMercadorias();
        $this->calcularDetalhesImpostos();
        $this->calcularPercentagens();
    }

    protected function calcularTotais(): void
    {
        $fob    = (float) ($this->processo->fob_total ?? 0);
        $frete  = (float) ($this->processo->frete ?? 0);
        $seguro = (float) ($this->processo->seguro ?? 0);
        $cambio = (float) ($this->processo->Cambio ?? 1); // Default 1 se não houver

        $this->cif = $fob + $frete + $seguro;

        // Se tiver ValorAduaneiro gravado, respeita; senão calcula
        if (!is_null($this->processo->ValorAduaneiro) && $this->processo->ValorAduaneiro > 0) {
            $this->valorAduaneiro = (float) $this->processo->ValorAduaneiro;
        } else {
            $this->valorAduaneiro = $this->cif * $cambio;
        }

        // Converter para moeda nacional se houver câmbio
        $this->valorAduaneiroMoedaNacional = $this->valorAduaneiro * $cambio;

        // Emolumentos / impostos
        $em = $this->processo->emolumentoTarifa;

        if ($em && $em->exists) {
            $this->calcularDespesasDetalhadas($em);
            $this->calcularImpostosDetalhados($em);
            
            $this->totalDespesas = array_sum($this->despesasDetalhadas);
            $this->totalImpostos = array_sum($this->impostosDetalhados);
        } else {
            $this->totalDespesas = 0;
            $this->totalImpostos = 0;
            $this->despesasDetalhadas = [];
            $this->impostosDetalhados = [];
        }

        $this->totalGeral = $this->valorAduaneiro + $this->totalDespesas + $this->totalImpostos;
    }

    protected function calcularDespesasDetalhadas($emolumentoTarifa): void
    {
        $this->despesasDetalhadas = [
            'Porto' => (float) ($emolumentoTarifa->porto ?? 0),
            'Terminal' => (float) ($emolumentoTarifa->terminal ?? 0),
            'LMC' => (float) ($emolumentoTarifa->lmc ?? 0),
            'Navegação' => (float) ($emolumentoTarifa->navegacao ?? 0),
            'Frete Local' => (float) ($emolumentoTarifa->frete ?? 0),
            'Despesas Inerentes' => (float) ($emolumentoTarifa->inerentes ?? 0),
            'Deslocação' => (float) ($emolumentoTarifa->deslocacao ?? 0),
            'Carga/Descarga' => (float) ($emolumentoTarifa->carga_descarga ?? 0),
            'Caução' => (float) ($emolumentoTarifa->caucao ?? 0),
            'Selos' => (float) ($emolumentoTarifa->selos ?? 0),
        ];
    }

    protected function calcularImpostosDetalhados($emolumentoTarifa): void
    {
        $this->impostosDetalhados = [
            'Direitos Aduaneiros' => (float) ($emolumentoTarifa->direitos ?? 0),
            'IEC' => (float) ($emolumentoTarifa->iec ?? 0),
            'Emolumentos' => (float) ($emolumentoTarifa->emolumentos ?? 0),
            'IVA Aduaneiro' => (float) ($emolumentoTarifa->iva_aduaneiro ?? 0),
            'Imposto Estatístico' => (float) ($emolumentoTarifa->impostoEstatistico ?? 0),
            'Honorário Despachante' => (float) ($emolumentoTarifa->honorario ?? 0),
            'IVA sobre Honorário' => (float) ($emolumentoTarifa->honorario_iva ?? 0),
        ];
    }

    protected function calcularResumoMercadorias(): void
    {
        $this->totalItens = $this->processo->mercadorias->count();
        $this->pesoTotal = $this->processo->mercadorias->sum('Peso');
        $this->fobMercadorias = $this->processo->mercadorias->sum('preco_total');

        // Agrupamentos únicos por código aduaneiro
        $this->agrupamentosUnicos = $this->processo->mercadorias
            ->pluck('codigo_aduaneiro')
            ->unique()
            ->count();

        // Carregar agrupamentos do banco
        $this->mercadoriasAgrupadas = MercadoriaAgrupada::where('processo_id', $this->processo->id)
            ->with(['mercadorias' => function($query) {
                $query->select('id', 'codigo_aduaneiro', 'Descricao', 'Quantidade', 'Peso', 'preco_total');
            }])
            ->get()
            ->map(function($agrupamento) {
                return [
                    'codigo_aduaneiro' => $agrupamento->codigo_aduaneiro,
                    'descricao' => $agrupamento->mercadorias->first()->Descricao ?? 'Não informado',
                    'quantidade_total' => $agrupamento->quantidade_total,
                    'peso_total' => $agrupamento->peso_total,
                    'preco_total' => $agrupamento->preco_total,
                    'quantidade_itens' => $agrupamento->mercadorias->count(),
                ];
            })
            ->toArray();
    }

    protected function calcularPercentagens(): void
    {
        if ($this->valorAduaneiro > 0) {
            $this->percentDespesas = ($this->totalDespesas / $this->valorAduaneiro) * 100;
            $this->percentImpostos = ($this->totalImpostos / $this->valorAduaneiro) * 100;
            $this->impostosSobreValorAduaneiro = ($this->totalImpostos / $this->valorAduaneiro) * 100;
        } else {
            $this->percentDespesas = 0;
            $this->percentImpostos = 0;
            $this->impostosSobreValorAduaneiro = 0;
        }
    }

    protected function calcularDetalhesImpostos(): void
    {
        $emolumentoTarifa = $this->processo->emolumentoTarifa;

        if ($emolumentoTarifa && $emolumentoTarifa->exists) {
            $this->calcularImpostosDetalhados($emolumentoTarifa);
        } else {
            $this->impostosDetalhados = [];
        }
    }

    // ✅ PROPRIEDADES COMPUTADAS PARA A VIEW

    public function getCifFormatadoProperty(): string
    {
        return number_format($this->cif, 2, ',', '.');
    }

    public function getValorAduaneiroFormatadoProperty(): string
    {
        return number_format($this->valorAduaneiro, 2, ',', '.');
    }

    public function getTotalGeralFormatadoProperty(): string
    {
        return number_format($this->totalGeral, 2, ',', '.');
    }

    public function getPesoTotalFormatadoProperty(): string
    {
        return number_format($this->pesoTotal, 2, ',', '.') . ' kg';
    }

    public function getDespesasDetalhadasFiltradasProperty(): array
    {
        return array_filter($this->despesasDetalhadas, fn($valor) => $valor > 0);
    }

    public function getImpostosDetalhadosFiltradosProperty(): array
    {
        return array_filter($this->impostosDetalhados, fn($valor) => $valor > 0);
    }

    public function getTemEmolumentosProperty(): bool
    {
        return $this->processo->emolumentoTarifa && $this->processo->emolumentoTarifa->exists;
    }

    public function getTemMercadoriasProperty(): bool
    {
        return $this->processo->mercadorias->count() > 0;
    }

    // ✅ MÉTODO PARA EXPORTAR DADOS
    public function exportarParaArray(): array
    {
        return [
            'processo' => [
                'numero' => $this->processo->NrProcesso,
                'data_registo' => $this->processo->DataRegisto?->format('d/m/Y'),
                'cliente' => $this->processo->cliente?->CompanyName,
                'exportador' => $this->processo->exportador?->Nome,
            ],
            'valores' => [
                'cif' => $this->cif,
                'valor_aduaneiro' => $this->valorAduaneiro,
                'total_despesas' => $this->totalDespesas,
                'total_impostos' => $this->totalImpostos,
                'total_geral' => $this->totalGeral,
            ],
            'mercadorias' => [
                'total_itens' => $this->totalItens,
                'agrupamentos_unicos' => $this->agrupamentosUnicos,
                'peso_total' => $this->pesoTotal,
                'fob_total' => $this->fobMercadorias,
                'agrupamentos' => $this->mercadoriasAgrupadas,
            ],
            'percentagens' => [
                'despesas_sobre_valor_aduaneiro' => $this->percentDespesas,
                'impostos_sobre_valor_aduaneiro' => $this->percentImpostos,
            ],
        ];
    }

    public function render()
    {
        return view('livewire.processos.resumo-asys', [
            'processo' => $this->processo,
            'despesasFiltradas' => $this->despesasDetalhadasFiltradas,
            'impostosFiltrados' => $this->impostosDetalhadosFiltrados,
            'temDados' => $this->temEmolumentos || $this->temMercadorias,
        ]);
    }
}
