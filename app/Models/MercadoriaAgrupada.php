<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        return $this->belongsTo(PautaAduaneira::class, 'codigo_aduaneiro', 'codigo')
                    ->whereRaw("REPLACE(codigo, '.', '') = ?", [$this->codigo_aduaneiro]);
    }

    // Relacionamento com as mercadorias
    public function mercadorias()
    {
        return $this->hasMany(Mercadoria::class, 'codigo_aduaneiro', 'codigo_aduaneiro')
                    ->where('licenciamento_id', $this->licenciamento_id)
                    ->orWhere('Fk_Importacao', $this->processo_id);
    }

    /**
     * Método para adicionar ou atualizar um agrupamento de mercadorias.
     */
    public static function StoreAndUpdateAgrupamento($mercadoria)
    {
        // Verificar se já existe um agrupamento com o mesmo código aduaneiro de um determinado licenciamento / processo
        $agrupamento = MercadoriaAgrupada::where('codigo_aduaneiro', $mercadoria->codigo_aduaneiro)
            ->where(function($query) use ($mercadoria) {
                $query->where('licenciamento_id', $mercadoria->licenciamento_id)
                    ->orWhere('processo_id', $mercadoria->processo_id);
            })->first();

        if ($agrupamento) {
            // Atualizar o agrupamento existente somando os valores de quantidade, peso e preço total
            $agrupamento->quantidade_total += $mercadoria->Quantidade;
            $agrupamento->peso_total += $mercadoria->Peso;
            $agrupamento->preco_total += $mercadoria->preco_total;

            // Atualizar os IDs das mercadorias agrupadas
            $mercadoriasIds = json_decode($agrupamento->mercadorias_ids);
            $mercadoriasIds[] = $mercadoria->id;
            $agrupamento->mercadorias_ids = json_encode($mercadoriasIds);

            $agrupamento->save();
        } else {
            // Criar um novo agrupamento
            self::create([
                'codigo_aduaneiro' => $mercadoria->codigo_aduaneiro,
                'licenciamento_id' => $mercadoria->licenciamento_id,
                'quantidade_total' => $mercadoria->Quantidade,
                'peso_total' => $mercadoria->Peso,
                'preco_total' => $mercadoria->preco_total,
                'mercadorias_ids' => json_encode([$mercadoria->id]),
            ]);

            // Adicionar mais uma adição na linha do licenciamento correspondente
            $exists = Licenciamento::where('id', $mercadoria->licenciamento_id)->first();
            if ($exists) {
                $exists->adicoes += 1;
                $exists->save();  // Mover o save para dentro do if
            }
        }
    }

    public static function RemoveAgrupamento($mercadoria)
    {
        $agrupamento = MercadoriaAgrupada::where('codigo_aduaneiro', $mercadoria->codigo_aduaneiro)
            ->where(function($query) use ($mercadoria) {
                $query->where('licenciamento_id', $mercadoria->licenciamento_id)
                    ->orWhere('processo_id', $mercadoria->processo_id);
            })->first();

        if ($agrupamento) {
            // Subtrair as quantidades, peso e preço do agrupamento
            $agrupamento->quantidade_total = max(0, $agrupamento->quantidade_total - $mercadoria->quantidade);
            $agrupamento->peso_total = max(0, $agrupamento->peso_total - $mercadoria->peso);
            $agrupamento->preco_total = max(0, $agrupamento->preco_total - $mercadoria->preco_total);


            $mercadoriasIds = json_decode($agrupamento->mercadorias_ids, true);
            //simplificar a remoção do ID usando a função array_filter:
            $mercadoriasIds = array_filter($mercadoriasIds, fn($id) => $id !== $mercadoria->id); // Remover o ID da mercadoria
            $agrupamento->mercadorias_ids = json_encode(array_values($mercadoriasIds));

            // Se não houver mais mercadorias associadas ao agrupamento, deletar o agrupamento
            if (count($mercadoriasIds) == 0) {
                $agrupamento->delete();
            } else {
                $agrupamento->save();
            }
        }
    }

    public static function recalcularAgrupamento($licenciamentoId = null, $processoId = null)
    {
        // Busca todos os agrupamentos ou filtra por licenciamento ou processo
        if ($licenciamentoId) {
            $agrupamentos = MercadoriaAgrupada::where('licenciamento_id', $licenciamentoId)->get();
        }

        if ($processoId) {
            $agrupamentos = MercadoriaAgrupada::where('processo_id', $processoId)->get();
        }

        foreach ($agrupamentos as $agrupamento) {
            // Busca todas as mercadorias associadas ao agrupamento usando os IDs armazenados
            $mercadorias = Mercadoria::whereIn('id', json_decode($agrupamento->mercadorias_ids))->get();

            // Calcula os novos valores agregados
            $quantidadeTotal = $mercadorias->sum('Quantidade');
            $pesoTotal = $mercadorias->sum('Peso');
            $precoTotal = $mercadorias->sum('preco_total');

            // Verifica se há mudanças nos valores para evitar operações desnecessárias
            if ($agrupamento->quantidade_total !== $quantidadeTotal ||
                $agrupamento->peso_total !== $pesoTotal ||
                $agrupamento->preco_total !== $precoTotal) {

                // Atualiza o agrupamento com os valores recalculados
                $agrupamento->quantidade_total = $quantidadeTotal;
                $agrupamento->peso_total = $pesoTotal;
                $agrupamento->preco_total = $precoTotal;

                $agrupamento->save();
            }
        }

        return response()->json(['message' => 'Recalculo dos agrupamentos concluído com sucesso!']);
    }


}
