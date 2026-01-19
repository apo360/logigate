<?php

namespace App\Livewire\Mercadorias;

use Livewire\Component;
use App\Models\Mercadoria;
use App\Models\MercadoriaAgrupada;
use App\Models\Subcategoria;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class CreateForm extends Component
{
    public string $context;
    public int $parentId;
    public string $mode = 'create'; // create | edit
    public ?int $mercadoriaId = null;


    /** CONTROLO DO MODAL */
    public bool $open = false;
    public bool $showVeiculos = false;
    public bool $showMaquinas = false;

    public string $codigoStatus = 'idle'; 
    // idle | incomplete | invalid | valid

    public string $codigoMessage = '';

    /** FORM */
    public array $form = [
        'subcategoria_id' => null,
        'codigo_aduaneiro'=> '',
        'descricao'       => '',
        'quantidade'      => 1,
        'peso'            => 0,
        'unidade'         => 'UN',
        'ncm_hs'          => '',
        'ncm_hs_numero'   => '',
        'qualificacao'    => '',
        'preco_unitario'  => 0,
        'preco_total'     => 0,

        // veículos
        'marca'           => null,
        'modelo'          => null,
        'chassis'         => null,
        'ano_fabricacao'  => null,

        // máquina
        'potencia'        => null,
    ];


    public array $subCategorias = [];
    public array $pautas = [];


    protected $listeners = [
        'open-create-mercadoria' => 'openModal',
    ];

    protected function rules()
    {
        $rules = [
            'form.codigo_aduaneiro' => 'required|string|max:50',
            'form.descricao'        => 'nullable|string|max:255',
            'form.quantidade'       => 'required|numeric|min:0.01',
            'form.peso'             => 'nullable|numeric|min:0',
            'form.preco_unitario'   => 'required|numeric|min:0',
            'form.preco_total'      => 'required|numeric|min:0',
            'form.subcategoria_id'  => 'required|exists:sub_categoria_aduaneira,id',
            'form.unidade'          => 'required|string|max:10',
            'form.ncm_hs'           => 'nullable|string|max:100',
            'form.ncm_hs_numero'    => 'nullable|string|max:20',
            'form.qualificacao'     => 'nullable|string|max:50',
        ];

        // Validação condicional para veículos
        if ($this->showVeiculos) {
            $rules['form.marca'] = 'required|string|max:100';
            $rules['form.modelo'] = 'required|string|max:100';
            $rules['form.chassis'] = 'required|string|max:50';
            $rules['form.ano_fabricacao'] = 'nullable|integer|min:1900|max:' . date('Y');
        }

        // Validação condicional para máquinas
        if ($this->showMaquinas) {
            $rules['form.potencia'] = 'required|numeric|min:0';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'form.codigo_aduaneiro.required' => 'O código aduaneiro é obrigatório.',
            'form.subcategoria_id.required' => 'A subcategoria é obrigatória.',
            'form.marca.required' => 'A marca é obrigatória para veículos.',
            'form.modelo.required' => 'O modelo é obrigatório para veículos.',
            'form.chassis.required' => 'O chassis é obrigatório para veículos.',
            'form.potencia.required' => 'A potência é obrigatória para máquinas.',
        ];
    }

    public function mount()
    {
        $this->subCategorias = SubCategoria::all()->toArray();
    }

    public function openModal(): void
    {
        $this->resetValidation();
        $this->mode = 'create';
        $this->open = true;
    }

    #[On('open-edit-mercadoria')]
    public function openEditModal(int $id): void
    {
        $this->resetValidation();
        $m = Mercadoria::findOrFail($id);

        $this->mercadoriaId = $id;
        $this->mode = 'edit';

        $this->form = [
            'subcategoria_id' => $m->subcategoria_id,
            'codigo_aduaneiro'=> $m->codigo_aduaneiro,
            'descricao'       => $m->Descricao,
            'quantidade'      => $m->Quantidade,
            'peso'            => $m->Peso,
            'unidade'         => $m->Unidade,
            'ncm_hs'          => $m->NCM_HS,
            'ncm_hs_numero'   => $m->NCM_HS_numero,
            'qualificacao'    => $m->Qualificacao,
            'preco_unitario'  => $m->preco_unitario,
            'preco_total'     => $m->preco_total,
            'marca'           => $m->marca,
            'modelo'          => $m->modelo,
            'chassis'         => $m->chassis,
            'ano_fabricacao'  => $m->ano_fabricacao,
            'potencia'        => $m->potencia,
        ];


        $this->open = true;
    }

    public function resetForm(): void
    {
        $this->reset('form', 'mercadoriaId', 'mode');
        $this->resetValidation();
        $this->codigoStatus = 'idle';
        $this->codigoMessage = '';
        $this->showVeiculos = false;
        $this->showMaquinas = false;
        $this->pautas = [];
    }

    // Atualizar closeModal para usar resetForm
    public function closeModal(): void 
    { 
        $this->resetForm();
        $this->open = false; 
    }

    /* ===============================
       REACTIVIDADE
    =============================== */
    public function updatedFormQuantidade()
    {
        $this->calcularTotal();
    }

    public function updatedFormPrecoUnitario()
    {
        $this->calcularTotal();
    }

    protected function calcularTotal($value = null)
    {
        // Prevenir que o preço total seja manualmente alterado para valor incorreto
        $calculated = (float) $this->form['quantidade'] * (float) $this->form['preco_unitario'];
        
        if (abs($value - $calculated) > 0.01) { // Tolerância de 0.01
            $this->addError('form.preco_total', 'O preço total deve ser igual a quantidade × preço unitário.');
            
            // Corrigir automaticamente
            $this->form['preco_total'] = $calculated;
        }
    }

    public function updatedFormSubcategoriaId($value)
    {
        $this->pautas = [];
        $this->showVeiculos = false;
        $this->showMaquinas = false;

        if (!$value) {
            return;
        }

        $sub = SubCategoria::find($value);

        if (!$sub) {
            return;
        }

        // carregar pautas como fazias no AJAX
        $this->pautas = DB::table('pauta_aduaneira')
            ->where('codigo', 'like', SubCategoria::find($value)->cod_pauta . '%')
            ->get()
            ->toArray();

        // ATIVAÇÃO POR CATEGORIA
        if (in_array($sub->cod_pauta, [87, 88])) {
            $this->showVeiculos = true;
        }

        if ($sub->cod_pauta == 84) {
            $this->showMaquinas = true;
        }
        
        // limpa código anterior
        $this->form['codigo_aduaneiro'] = '';
    }

    public function updatedFormCodigoAduaneiro($value)
    {
        $this->showVeiculos = false;
        $this->codigoStatus = 'idle';
        $this->codigoMessage = '';

        if (!$value || count($this->pautas) === 0) {
            return;
        }

        // Prefixos válidos para veículos
        $prefixosVeiculos = [
            '8701','8702','8703','8704','8705','8706','8707',
            '8709','8711','8712','8713'
        ];

        foreach ($prefixosVeiculos as $prefixo) {
            if (str_starts_with($value, $prefixo)) {
                $this->showVeiculos = true;
                break;
            }
        }

        $exactMatch = false;
        $prefixMatch = false;

        foreach ($this->pautas as $pauta) {
            if ($pauta->codigo === $value) {
                $exactMatch = true;
                break;
            }

            if (str_starts_with($pauta->codigo, $value)) {
                $prefixMatch = true;
            }
        }

        if ($exactMatch) {
            $this->codigoStatus = 'valid';
            $this->codigoMessage = 'Código válido.';
            return;
        }

        if ($prefixMatch) {
            $this->codigoStatus = 'incomplete';
            $this->codigoMessage = 'Código incompleto. Continue a digitar.';
            return;
        }

        $this->codigoStatus = 'invalid';
        $this->codigoMessage = 'Código inválido. Escolha um da lista.';
    }


    public function save(): void
    {
        $this->validate();

        // Bloqueio caso o código esteja errado
        if ($this->codigoStatus !== 'valid') {
            $this->addError('form.codigo_aduaneiro', 'Código aduaneiro inválido ou incompleto.');
            return;
        }

        try {
            $mercadoria = $this->mode === 'edit' && $this->mercadoriaId
                ? Mercadoria::findOrFail($this->mercadoriaId)
                : new Mercadoria();
            
            // Atribuição dos campos - considere usar fill() para código mais limpo
            $mercadoria->fill([
                'subcategoria_id'  => $this->form['subcategoria_id'],
                'codigo_aduaneiro' => $this->form['codigo_aduaneiro'],
                'Descricao'        => $this->form['descricao'],
                'Quantidade'       => $this->form['quantidade'],
                'Unidade'          => $this->form['unidade'],
                'NCM_HS'           => $this->form['ncm_hs'],
                'NCM_HS_numero'    => $this->form['ncm_hs_numero'],
                'Qualificacao'     => $this->form['qualificacao'],
                'Peso'             => $this->form['peso'],
                'preco_unitario'   => $this->form['preco_unitario'],
                'preco_total'      => $this->form['preco_total'],
                'marca'            => $this->form['marca'],
                'modelo'           => $this->form['modelo'],
                'chassis'          => $this->form['chassis'],
                'ano_fabricacao'   => $this->form['ano_fabricacao'],
                'potencia'         => $this->form['potencia'],
            ]);

            // Apenas na criação, definir a relação pai
            if ($this->mode === 'create') {
                if ($this->context === 'processo') {
                    $mercadoria->Fk_Importacao = $this->parentId;
                } elseif ($this->context === 'licenciamento') {
                    $mercadoria->licenciamento_id = $this->parentId;
                }
            }

            // Se for edição, remover do agrupamento antigo (caso código tenha mudado)
            if ($this->mode === 'edit') {
                $oldCodigo = Mercadoria::find($this->mercadoriaId)->codigo_aduaneiro ?? null;
                if ($oldCodigo && $oldCodigo !== $this->form['codigo_aduaneiro']) {
                    $oldMercadoria = Mercadoria::find($this->mercadoriaId);
                    MercadoriaAgrupada::removeFromAgrupamento($oldMercadoria);
                }
            }

            $mercadoria->save();

            // Sincronizar agrupada (se for o caso)
            MercadoriaAgrupada::StoreAndUpdateAgrupamento($mercadoria);

            // Limpar formulário
            $this->reset('form');

            // Emitir eventos
            $this->dispatch($this->mode === 'edit' ? 'mercadoriaUpdated' : 'mercadoriaCreated');
            
            // Mensagem de sucesso
            $action = $this->mode === 'edit' ? 'atualizada' : 'criada';
            $this->dispatch('toast', 
                type: 'success', 
                message: "Mercadoria {$action} com sucesso!"
            );

            // Fechar modal
            $this->open = false;
            
            // Resetar modo se necessário
            if ($this->mode === 'edit') {
                $this->mode = 'create';
                $this->mercadoriaId = null;
            }

        } catch (\Exception $e) {
            $this->dispatch('toast', 
                type: 'error', 
                message: "Erro ao salvar mercadoria: " . $e->getMessage()
            );
        }
    }

    public function delete($id): void
    {
        try {
            $mercadoria = Mercadoria::findOrFail($id);
            
            // Verificar se pode deletar (dependendo do seu contexto de negócio)
            // Por exemplo: if ($mercadoria->hasRelatedRecords()) { ... }
            
            // Remover do agrupamento antes de deletar
            MercadoriaAgrupada::removeFromAgrupamento($mercadoria);
            
            // Deletar a mercadoria
            $mercadoria->delete();
            
            // Emitir eventos
            $this->dispatch('mercadoriaDeleted', id: $id);
            $this->dispatch('toast', 
                type: 'success', 
                message: 'Mercadoria excluída com sucesso!'
            );
            
            // Se estiver no modal de edição da mesma mercadoria, fechar
            if ($this->mode === 'edit' && $this->mercadoriaId === $id) {
                $this->open = false;
                $this->mode = 'create';
                $this->mercadoriaId = null;
            }
            
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') { // Foreign key constraint
                $this->dispatch('toast', 
                    type: 'error', 
                    message: 'Não é possível excluir esta mercadoria porque está vinculada a outros registros.'
                );
            } else {
                $this->dispatch('toast', 
                    type: 'error', 
                    message: 'Erro ao excluir mercadoria: ' . $e->getMessage()
                );
            }
        } catch (\Exception $e) {
            $this->dispatch('toast', 
                type: 'error', 
                message: 'Erro ao excluir mercadoria: ' . $e->getMessage()
            );
        }
    }

    public function render()
    {
        return view('livewire.mercadorias.create-form');
    }
}
