<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Processo extends Model
{
    use HasFactory;

    protected $fillable = [
        'NrProcesso',
        'ContaDespacho',
        'customer_id',
        'RefCliente',
        'Descricao',
        'DataAbertura',
        'DataFecho',
        'TipoProcesso',
        'Situacao',
        'user_id',
        'empresa_id',
        'exportador_id',
        // Adicione outros campos fillable conforme necessário
    ];

    protected $dates = [
        'DataAbertura',
        'DataFecho',
        'created_at',
        'updated_at'
    ];

    public static function getLastInsertedId()
    {
        $ultimoProcesso = self::latest()->first();

        if ($ultimoProcesso) {
            return $ultimoProcesso->ProcessoID;
        }

        return null;
    }

    // Relacionamento com a tabela Importacao
    public function importacao()
    {
        return $this->hasOne(Importacao::class, 'processo_id');
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * Gera um novo código de processo sequencial a cada ano.
     *
     * @return string
     */
    public static function generateNewProcesso()
    {
        // Implementar a geração do novo código de processo aqui
        return DB::select('CALL ProcessoNewCod()')[0]->codProcesso;
    }

    /**
     * Gerar um novo código de ContaDespacho sequencial a cada ano. OBS: Esse numero é gerado quando a conta é fechada ou imprimida a carta.
     * 
     * @return string
     */
    public static function generateNewContaDespacho()
    {
        // Implementar a geração do novo código de processo aqui
        return DB::select('CALL DespachoNewCod()')[0]->codProcesso;
    }

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

    // public static function mediaTempoProcessamentoAnual($year)
    // {
    //     return self::whereYear('DataAbertura', $year)
    //         ->selectRaw('AVG(DATEDIFF(DataFecho, DataAbertura)) as tempo_medio')
    //         ->value('tempo_medio');
    // }

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

    // Definindo relação One-to-One com o modelo Portuaria
    public function portuaria()
    {
        return $this->hasOne(TarifaPortuaria::class, 'Fk_processo');
    }

    // Definindo relação One-to-One com o modelo Equivalencia
    public function dar()
    {
        return $this->hasOne(TarifaDAR::class, 'Fk_processo');
    }

    //Definindo relação One-to-One com o modelo DU
    public function du()
    {
        return $this->hasOne(TarifaDU::class, 'Fk_processo');
    }
}
