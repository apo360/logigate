<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class MercadoriaAgrupada extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo_aduaneiro',
        'licenciamento_id',
        'processo_id',
        'quantidade_total', 
        'peso_total',
        'preco_total',
        'mercadorias_ids', //Para armazenar os IDs das mercadorias agrupadas
    ];

    // Relacionamento com Pauta Aduaneira
    // Relacionamento com Pauta Aduaneira, removendo os pontos para comparação
    public function pautaAduaneira()
    {
        return $this->belongsTo(PautaAduaneira::class, 'codigo_aduaneiro', 'codigo');
    }

    /**
     * Relação com mercadorias
     */

    public function mercadorias()
    {
        return $this->hasMany(Mercadoria::class, 'id', 'mercadorias_ids');
    }

    public function mercadoriasLicenciamento()
    {
        return $this->hasMany(Mercadoria::class, 'codigo_aduaneiro', 'codigo_aduaneiro')
            ->where('licenciamento_id', $this->licenciamento_id);
    }

    /**
     * Relação com mercadorias de PROCESSO
     */
    public function mercadoriasProcesso()
    {
        return $this->hasMany(Mercadoria::class, 'codigo_aduaneiro', 'codigo_aduaneiro')
            ->where('Fk_Importacao', $this->processo_id);
    }

    /**
     * Método para adicionar ou atualizar um agrupamento de mercadorias.
     */
    public static function storeAndUpdateAgrupamento(Mercadoria $mercadoria): void
    {
        try {
            // Determinar o ID do processo/licenciamento
            $processoId = $mercadoria->Fk_Importacao;
            $licenciamentoId = $mercadoria->licenciamento_id;
            
            // Validar que temos pelo menos um relacionamento
            if (!$processoId && !$licenciamentoId) {
                throw new \Exception('Mercadoria não está vinculada a um processo ou licenciamento');
            }

            // Buscar agrupamento existente
            $agrupamento = self::where('codigo_aduaneiro', $mercadoria->codigo_aduaneiro)
                ->where(function($query) use ($processoId, $licenciamentoId) {
                    $query->when($licenciamentoId, 
                        fn($q) => $q->where('licenciamento_id', $licenciamentoId)
                    )->when($processoId, 
                        fn($q) => $q->where('processo_id', $processoId)
                    );
                })->first();

            if ($agrupamento) {
                // Atualizar agrupamento existente
                self::updateExistingAgrupamento($agrupamento, $mercadoria);
            } else {
                // Criar novo agrupamento
                self::createNewAgrupamento($mercadoria, $processoId, $licenciamentoId);
            }
            
        } catch (\Exception $e) {
            // Log do erro (opcional)
            Log::error('Erro ao processar agrupamento: ' . $e->getMessage());
            throw $e; // Re-lançar se quiser tratar no controller
        }
    }

    /**
     * Atualiza um agrupamento existente
     */
    protected static function updateExistingAgrupamento(self $agrupamento, Mercadoria $mercadoria): void
    {
        // Somar valores
        $agrupamento->quantidade_total += $mercadoria->Quantidade;
        $agrupamento->peso_total += $mercadoria->Peso;
        $agrupamento->preco_total += $mercadoria->preco_total;
        
        // Atualizar IDs das mercadorias (usando array para melhor manipulação)
        $mercadoriasIds = json_decode($agrupamento->mercadorias_ids, true) ?? [];
        
        // Adicionar novo ID se não existir
        if (!in_array($mercadoria->id, $mercadoriasIds)) {
            $mercadoriasIds[] = $mercadoria->id;
            $agrupamento->mercadorias_ids = json_encode($mercadoriasIds);
        }
        
        // Atualizar data de modificação
        $agrupamento->updated_at = now();
        
        $agrupamento->save();
    }

    /**
     * Cria um novo agrupamento
     */
    protected static function createNewAgrupamento(Mercadoria $mercadoria, $processoId, $licenciamentoId): void
    {
        self::create([
            'codigo_aduaneiro' => $mercadoria->codigo_aduaneiro,
            'licenciamento_id' => $licenciamentoId,
            'processo_id' => $processoId,
            'quantidade_total' => $mercadoria->Quantidade,
            'peso_total' => $mercadoria->Peso,
            'preco_total' => $mercadoria->preco_total,
            'mercadorias_ids' => json_encode([$mercadoria->id]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Incrementar adições apenas para licenciamentos
        if ($licenciamentoId) {
            self::incrementarAdicoesLicenciamento($licenciamentoId);
        }
    }

    /**
     * Incrementa o contador de adições no licenciamento
     */
    protected static function incrementarAdicoesLicenciamento($licenciamentoId): void
    {
        $licenciamento = Licenciamento::find($licenciamentoId);
        
        if ($licenciamento) {
            // Usar increment() para evitar race conditions
            $licenciamento->increment('adicoes');
        }
    }

    /**
     * Remove uma mercadoria do agrupamento (quando deletada ou editada)
     */
    public static function removeFromAgrupamento(Mercadoria $mercadoria): void
    {
        try {
            $processoId = $mercadoria->Fk_Importacao;
            $licenciamentoId = $mercadoria->licenciamento_id;
            
            $agrupamento = self::where('codigo_aduaneiro', $mercadoria->codigo_aduaneiro)
                ->where(function($query) use ($processoId, $licenciamentoId) {
                    $query->when($licenciamentoId, 
                        fn($q) => $q->where('licenciamento_id', $licenciamentoId)
                    )->when($processoId, 
                        fn($q) => $q->where('processo_id', $processoId)
                    );
                })->first();
            
            if (!$agrupamento) {
                return;
            }
            
            // Remover ID da mercadoria da lista
            $mercadoriasIds = json_decode($agrupamento->mercadorias_ids, true) ?? [];
            
            // Encontrar e remover o ID
            $key = array_search($mercadoria->id, $mercadoriasIds);
            if ($key !== false) {
                unset($mercadoriasIds[$key]);
                
                // Reindexar array
                $mercadoriasIds = array_values($mercadoriasIds);
                
                // Se ainda há mercadorias no agrupamento, atualizar totais
                if (!empty($mercadoriasIds)) {
                    $agrupamento->quantidade_total -= $mercadoria->Quantidade;
                    $agrupamento->peso_total -= $mercadoria->Peso;
                    $agrupamento->preco_total -= $mercadoria->preco_total;
                    $agrupamento->mercadorias_ids = json_encode($mercadoriasIds);
                    $agrupamento->save();
                } else {
                    // Se não há mais mercadorias, deletar o agrupamento
                    $agrupamento->delete();
                    
                    // Se for licenciamento, decrementar adições
                    if ($licenciamentoId) {
                        $licenciamento = Licenciamento::find($licenciamentoId);
                        if ($licenciamento && $licenciamento->adicoes > 0) {
                            $licenciamento->decrement('adicoes');
                        }
                    }
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Erro ao remover do agrupamento: ' . $e->getMessage());
            throw $e;
        }
    }
}
