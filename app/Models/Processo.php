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
use OwenIt\Auditing\Models\Audit;

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

            // Evento executado após criar um novo registro
            // Você pode usar este espaço para adicionar lógica adicional, como enviar notificações, etc.
            // Exemplo: Notificar o administrador sobre a criação de um novo processo, Inserir na tabela de histórico, Autitar, etc.
            
            // Inserir na tabela de auditoria
            Audit::create([
                'user_type'      => Auth::user()->roles->pluck('name')->first() ?? 'sem-perfil',
                'user_id'        => Auth::id(),
                'event'          => 'novo_processo',
                'new_values'     => ['message' => 'Usuário registrou um novo processo '. $processo->NrProcesso],
                'url'            => request()->fullUrl(),
                'ip_address'     => request()->ip(),
                'user_agent'     => request()->header('User-Agent'),
                'auditable_type' => get_class(Auth::user()),
                'auditable_id'   => Auth::id(),
            ]);

            // Log do Laravel
            Log::info('Novo processo criado', [
                'NrProcesso' => $processo->NrProcesso,
                'user_id'    => Auth::id(),
                'empresa_id' => $processo->empresa_id,
                'data'       => now(),
            ]);

            // (Opcional) Enviar email de notificação
            // Mail::to(config('app.admin_email'))->send(new NovoProcessoCriadoMail($processo));
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

            if ($processo->Estado === 'concluido' && $processo->isDirty('Estado')) {
                throw new \Exception('Não é permitido alterar o estado de um processo concluído.');
            }

            // Capturar as alterações feitas no modelo
            $alteracoes = $processo->getDirty(); // só os campos alterados
            $originais  = $processo->getOriginal(); // valores antigos

            // Montar os valores para guardar no audit
            $detalhes = [];
            foreach ($alteracoes as $campo => $valorNovo) {
                $detalhes[$campo] = [
                    'antes' => $originais[$campo] ?? null,
                    'depois' => $valorNovo
                ];
            }

            Audit::create([
                'user_type'      => Auth::user()->roles->pluck('name')->first() ?? 'sem-perfil',
                'user_id'        => Auth::id(),
                'event'          => 'Actualização do Processo ' . $processo->NrProcesso,
                'old_values'     => $originais, // opcional, podes guardar tudo
                'new_values'     => $detalhes,  // só campos alterados
                'url'            => request()->fullUrl(),
                'ip_address'     => request()->ip(),
                'user_agent'     => request()->header('User-Agent'),
                'auditable_type' => get_class($processo),
                'auditable_id'   => $processo->id,
            ]);

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
     * Relação com o modelo de país de destino.
     */
    public function nacionalidadeNavio()
    {
        return $this->belongsTo(Pais::class, 'nacionalidade_transporte');
    }

    /**
     * Relacionamento com a Mercadorias.
     */
    public function procLicenMercadorias()
    {
        return $this->hasMany(ProcessoLicenciamentoMercadoria::class, 'processo_id');
    }

    /**
     * Relacionamento com as Tarifas e Emolumentos
     */

     public function emolumentoTarifa()
     {
        return $this->belongsTo(EmolumentoTarifa::class, 'id', 'processo_id');
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

    public function mercadoriasAgrupadas()
    {
        return $this->hasMany(MercadoriaAgrupada::class, 'processo_id')->onDelete('cascade');
    }
    
    public function procLicenFaturas()
    {
        return $this->hasMany(ProcLicenFactura::class, 'processo_id');
    }

    public function porto()
    {
        return $this->belongsTo(Porto::class, 'PortoOrigem', 'porto');
    }

    protected $appends = ['guia_fiscal'];

    public function getGuiaFiscalAttribute()
    {
        return (float) array_sum([
            $this->direitos ?? 0.00,
            $this->emolumentos ?? 0.00,
            $this->porto ?? 0.00,
            $this->terminal ?? 0.00,
            $this->lmc ?? 0.00,
            $this->navegacao ?? 0.00,
            $this->inerentes ?? 0.00,
            $this->frete ?? 0.00,
            $this->carga_descarga ?? 0.00,
            $this->deslocacao ?? 0.00,
            $this->selos ?? 0.00,
            $this->iva_aduaneiro ?? 0.00,
            $this->iec ?? 0.00,
            $this->impostoEstatistico ?? 0.00,
            $this->juros_mora ?? 0.00,
            $this->caucao ?? 0.00,
            $this->honorario ?? 0.00,
            $this->honorario_iva ?? 0.00,
            $this->orgaos_ofiais ?? 0.00,
        ]);
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

    /**
     * Lista apenas os campos não preenchidos
     *
     * @param array $camposImportantes
     * @return array
     */
    public function getCamposNaoPreenchidos(array $camposImportantes)
    {
        $naoPreenchidos = [];

        foreach ($camposImportantes as $campo => $label) {
            if (empty($this->$campo)) {
                $naoPreenchidos[$campo] = $label;
            }
        }

        return $naoPreenchidos;
    }
}
