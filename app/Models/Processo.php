<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use OwenIt\Auditing\Contracts\Auditable;

class Processo extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    /**
     * A tabela associada ao modelo.
     *
     * @var string
     */
    protected $table;

    protected $fillable = [
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
        'DataChegada',
        'TipoTransporte',
        'registo_transporte',
        'nacionalidade_transporte',
        'forma_pagamento',
        'codigo_banco',
        'Moeda',
        'Cambio',
        'ValorTotal',
        'ValorAduaneiro',
        'fob_total',
        'frete',
        'seguro',
        'cif',
    ];

    protected $dates = [
        'DataAbertura',
        'DataFecho',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

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

    protected static function boot()
    {
        parent::boot();

        // Evento executado antes de criar um novo registro
        static::creating(function ($processo) {

            if (Auth::check()) {
                $processo->user_id = Auth::user()->id;
            }

            // Definir automaticamente o empresa_id se ainda não estiver definido
            if (!$processo->empresa_id) {
                $processo->empresa_id = Auth::user()->empresas->first()->id/* Defina aqui o ID da empresa que deseja associar */;
            }

            // Gerar automaticamente o NrProcesso apenas se a tabela for 'processos'
            if ($processo->getTable() === 'processos') {
                $processo->NrProcesso = self::generateNewProcesso($processo->empresa_id);
            }
        });

        static::updating(function ($processo) {
            if ($processo->isDirty(['Estado'])) {
                $log = [
                    'processo_id' => $processo->id,
                    'user_id' => Auth::id(),
                    'alteracao' => 'Estado alterado de ' . $processo->getOriginal('Estado') . ' para ' . $processo->Estado,
                    'data' => now(),
                ];
                Log::info('Alteração no processo:', $log); // Ou salve em uma tabela de auditoria.
            }

            if ($processo->Estado === 'Concluído' && $processo->isDirty('Estado')) {
                throw new \Exception('Não é permitido alterar o estado de um processo concluído.');
            }

        });
        

        // Evento executado antes de excluir um registro
        static::deleting(function ($processo) {
            // Exemplo: impedir exclusão se o processo estiver em um estado específico
            if ($processo->Estado === ['Retido','Finalizado']) {
                throw new \Exception('Processos concluídos não podem ser excluídos.');
            }

            Log::info('Processo excluído', [
                'processo_id' => $processo->id,
                'user_id' => Auth::id(),
                'motivo' => request('motivo') ?? 'Não especificado',
                'data' => now(),
            ]);

            DB::table('processos_historico')->insert($processo->toArray());

            // Mail::to('admin@empresa.com')->send(new ProcessoExcluido($processo));
        });
    }

    public static function getLastInsertedId()
    {
        $ultimoProcesso = self::latest()->first();

        if ($ultimoProcesso) {
            return $ultimoProcesso->ProcessoID;
        }

        return null;
    }

    public function tipoTransporte()
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

    public function tipoProcesso()
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
     * Relacionamento com a Mercadorias.
     */
    public function procLicenMercadorias()
    {
        return $this->hasMany(ProcessoLicenciamentoMercadoria::class, 'processo_id');
    }

    /**
     * Gera um novo código de processo sequencial a cada ano.
     *
     * @return string
     */

    // Função para gerar o código único e sequencial
    public static function generateNewProcesso($empresaId)
    {
        // Obtenha o último licenciamento dessa empresa
        $ultimoProcesso = Processo::where('empresa_id', $empresaId)->orderBy('id', 'desc')->first();

        // Se houver um licenciamento anterior, incremente o número
        if ($ultimoProcesso) {
            $ultimoCodigo = (int) substr($ultimoProcesso->NrProcesso, -7, 4); // Exemplo: pega os 4 dígitos específicos
            $novoCodigo = $ultimoCodigo + 1;
        } else {
            // Caso seja o primeiro licenciamento da empresa
            $novoCodigo = 1;
        }

        // Obtenha o nome da empresa e gere as iniciais
        $empresa = Empresa::findOrFail($empresaId);

        if($empresa->CodProcesso == ''){
            // Função para obter as iniciais do nome da empresa
            $iniciais = implode('', array_map(function($word) { return strtoupper($word[0]); }, explode(' ', $empresa->nome)));
        }else{
            $iniciais = $empresa->CodProcesso;
        }
        // Gera o código com as iniciais, ID da empresa e o código do processo
        $codigoProcesso = $iniciais . '-' . str_pad($empresaId, 3, '0', STR_PAD_LEFT) . '-' . str_pad($novoCodigo, 4, '0', STR_PAD_LEFT) . '/' . Carbon::now()->format('y');

        return $codigoProcesso;
    }


    /**
     * Gerar um novo código de ContaDespacho sequencial a cada ano. OBS: Esse numero é gerado quando a conta é fechada ou imprimida a carta.
     * 
     * @return string
     */

     /**
     * Obtém a data de abertura formatada.
     *
     * @param date $dataAbertura
     * @return string|null
     */
    protected function getDataAberturaAttribute()
    {
        $dataAbertura = $this->attributes['DataAbertura'];

        if ($dataAbertura) {
            return date('d/m/Y', strtotime($dataAbertura));
        }

        return null;
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

}
