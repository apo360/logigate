<?php

namespace App\Models;

use App\Domains\Arquivo\Support\HasDocumentos;
use App\Application\Processo\Services\EmolumentoTarifaTotalsService;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Processo extends Model implements Auditable
{
    use HasFactory, SoftDeletes, BelongsToTenant, HasDocumentos;
    use \OwenIt\Auditing\Auditable;

    public function generateTags(): array
    {
        $correlation = request()->attributes->get('v1_operation_id');
        if (! $correlation) {
            $correlation = (string) \Illuminate\Support\Str::uuid();
            request()->attributes->set('v1_operation_id', $correlation);
        }
        return ['empresa:' . $this->empresa_id, 'operation:' . $correlation];
    }

    /**
     * A tabela associada ao modelo.
     *
     * @var string
     */
    protected $table;

    protected $fillable = [
        'id',
        'NrProcesso',
        'ContaDespacho',
        'RefCliente',
        'Descricao',
        'DataAbertura',
        'DataFecho',
        'TipoProcesso',
        'Estado',
        'customer_id',
        'user_id',
        'empresa_id',
        'exportador_id',
        'estancia_id',
        'NrDU',
        'N_Dar',
        'MarcaFiscal',
        'BLC_Porte',
        'Pais_origem',
        'Pais_destino',
        'PortoOrigem',
        'DataPartida',
        'DataChegada',
        'TipoTransporte',
        'registo_transporte',
        'nacionalidade_transporte',
        'forma_pagamento',
        'codigo_banco',
        'Moeda',
        'Cambio',
        'cambio_origem',
        'cambio_data',
        'cambio_confirmado',
        'ValorTotal',
        'ValorAduaneiro',
        'fob_total',
        'frete',
        'seguro',
        'cif',
        'peso_bruto',
        'quantidade_barris',
        'data_carregamento',
        'valor_barril_usd',
        'num_deslocacoes',
        'rsm_num',
        'certificado_origem',
        'guia_exportacao',
        'vinheta',
        'porto_desembarque_id',
        'localizacao_mercadoria_id',
        'condicao_pagamento_id',
        'observacoes',
    ];

    protected $dates = [
        'DataFecho',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function totaisMercadorias(): array
    {
        return app(\App\Application\Mercadoria\Services\MercadoriaParentTotalsService::class)->calculatedTotals($this);
    }

    /**
     * Configurar a tabela dinamicamente. 
     *
     * @param string $table
     * @return void
     */
    public function setTable($table)
    {
        $this->table = $table;
    }

    public static function getLastInsertedId()
    {
        $ultimoProcesso = self::latest()->first();

        if ($ultimoProcesso) {
            return $ultimoProcesso->ProcessoID;
        }

        return null;
    }

    public function transporte()
    {
        return $this->belongsTo(TipoTransporte::class, 'TipoTransporte');
    }

    // Relacionamento com a tabela Exportador
    public function exportador()
    {
        return $this->belongsTo(Exportador::class, 'exportador_id');
    }

    /**
     * Obtém o cliente associado a este processo.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cliente()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function estancia()
    {
        return $this->belongsTo(Estancia::class, 'estancia_id');
    }

    public function tipoDeclaracao()
    {
        return $this->belongsTo(RegiaoAduaneira::class, 'TipoProcesso');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * Relação com o modelo de país de origem.
     */
    public function paisOrigem()
    {
        return $this->belongsTo(Pais::class, 'Pais_origem');
    }

    /**
     * Relação com o modelo de país de destino.
     */
    public function paisDestino()
    {
        return $this->belongsTo(Pais::class, 'Pais_destino');
    }

    /**
     * Relação com o modelo de país de destino.
     */
    public function nacionalidadeNavio()
    {
        return $this->belongsTo(Pais::class, 'nacionalidade_transporte');
    }

    /**
     * Relacionamento com as Tarifas e Emolumentos
     */

    public function emolumentoTarifa()
    {
        return $this->belongsTo(EmolumentoTarifa::class, 'id', 'processo_id');
    }

    /**
     * Metodos para obter estatisticas relativamente aos processos.
     *
     * @return int
     */
    // Método para obter o total de processos
    public static function getTotalProcessos($empresaID)
    {
        return self::where('empresa_id', $empresaID)->count();
    }

    // Método para obter o total de processos por tipo
    public static function getTotalProcessosPorTipo($tipo)
    {
        return self::where('TipoProcesso', $tipo)->count();
    }

    /**
     * Obtém os processos mais recentes.
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getProcessosRecentes($limit = 5)
    {
        return self::orderBy('DataAbertura', 'desc')->limit($limit)->get();
    }

    public function getTempoProcessamentoAttribute()
    {
        if ($this->data_conclusao && $this->data_entrada) {
            return $this->data_conclusao->diffInDays($this->data_entrada);
        }

        return null;
    }

    public static function mediaTempoProcessamentoAnual($year)
    {
        return self::whereYear('DataAbertura', $year)
            ->selectRaw('MONTH(DataAbertura) as mes, AVG(DATEDIFF(DataFecho, DataAbertura)) as tempo_medio')
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();
    }

    public static function mediaTempoProcessamentoMensal($year, $month)
    {
        return self::whereYear('DataAbertura', $year)
            ->whereMonth('DataAbertura', $month)
            ->selectRaw('AVG(DATEDIFF(DataFecho, DataAbertura)) as tempo_medio')
            ->value('tempo_medio');
    }

    public static function mediaTempoProcessamentoDiario($date)
    {
        return self::whereDate('DataAbertura', $date)
            ->selectRaw('AVG(DATEDIFF(DataFecho, DataAbertura)) as tempo_medio')
            ->value('tempo_medio');
    }


    /**
     * Obtém as mercadorias associadas a este processo.
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */

    public function historico()
    {
        return $this->hasMany(HistoricoProcesso::class);
    }

    public function mercadorias()
    {
        return $this->hasMany(Mercadoria::class, 'Fk_Importacao');
    }

    public function contentores()
    {
        return $this->hasMany(Contentor::class, 'processo_id');
    }

    public function mercadoriasAgrupadas()
    {
        return $this->hasMany(MercadoriaAgrupada::class, 'processo_id');
    }

    public function procLicenFaturas()
    {
        return $this->hasMany(ProcLicenFactura::class, 'processo_id');
    }

    public function documentosArquivos()
    {
        return $this->hasMany(DocumentoArquivo::class, 'processo_id');
    }

    public function porto()
    {
        return $this->belongsTo(Porto::class, 'PortoOrigem', 'porto');
    }

    protected $appends = ['guia_fiscal'];

    public function getGuiaFiscalAttribute()
    {
        return (new EmolumentoTarifaTotalsService())->guiaFiscal($this->emolumentoTarifa);
    }

    public function portoDesembarque()
    {
        return $this->belongsTo(Porto::class, 'porto_desembarque_id', 'id');
    }

    /**
     * Local de Armazenamento da Mercadoria
     */
    public function localizacaoMercadoria()
    {
        return $this->belongsTo(MercadoriaLocalizacao::class, 'localizacao_mercadoria_id');
    }
}
