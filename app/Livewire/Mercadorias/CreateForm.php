<?php

namespace App\Livewire\Mercadorias;

use App\Application\Mercadoria\Actions\AtualizarMercadoriaAction;
use App\Application\Mercadoria\Actions\CriarMercadoriaAction;
use App\Application\Mercadoria\Actions\ExcluirMercadoriaAction;
use App\Application\Mercadoria\DTOs\MercadoriaData;
use App\Application\Mercadoria\Repositories\MercadoriaRepositoryInterface;
use App\Application\Mercadoria\Services\MercadoriaRules;
use App\Application\Mercadoria\Services\PautaAduaneiraLookupService;
use App\Application\PautaAduaneira\IA\PautaSuggestionDTO;
use App\Application\PautaAduaneira\IA\SugerirCodigoPautalAction;
use Livewire\Component;
use App\Models\Subcategoria;
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

    public ?string $originalCodigoAduaneiro = null;

    public array $pautaSuggestions = [];

    public bool $suggestingPauta = false;

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
        'pauta_change_reason' => null,
        'pauta_change_source' => 'manual',
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

        if ($this->mode === 'edit' && $this->codigoPautalChanged()) {
            $rules['form.pauta_change_reason'] = 'required|string|min:5|max:500';
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
            'form.pauta_change_reason.required' => 'Informe a justificativa da alteração do código pautal.',
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
        $this->mercadoriaId = null;
        $this->originalCodigoAduaneiro = null;
        $this->pautaSuggestions = [];
        $this->open = true;
    }

    #[On('open-edit-mercadoria')]
    public function openEditModal(int $id): void
    {
        $this->resetValidation();
        $m = app(MercadoriaRepositoryInterface::class)->findInContext($id, $this->context, $this->parentId);

        $this->mercadoriaId = $id;
        $this->mode = 'edit';
        $this->originalCodigoAduaneiro = $m->codigo_aduaneiro;

        $this->form = [
            'subcategoria_id' => $m->subcategoria_id,
            'codigo_aduaneiro'=> $m->codigo_aduaneiro,
            'descricao'       => $m->Descricao,
            'quantidade'      => $m->Quantidade,
            'peso'            => $m->Peso,
            'unidade'         => $m->Unidade,
            'ncm_hs'          => $m->NCM_HS,
            'ncm_hs_numero'   => $m->NCM_HS_Numero,
            'qualificacao'    => $m->Qualificacao,
            'preco_unitario'  => $m->preco_unitario,
            'preco_total'     => $m->preco_total,
            'marca'           => $m->marca,
            'modelo'          => $m->modelo,
            'chassis'         => $m->chassis,
            'ano_fabricacao'  => $m->ano_fabricacao,
            'potencia'        => $m->potencia,
            'pauta_change_reason' => null,
            'pauta_change_source' => 'manual',
        ];

        $this->loadPautasForSubcategoria($m->subcategoria_id);
        $this->updatedFormCodigoAduaneiro($m->codigo_aduaneiro);

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
        $this->originalCodigoAduaneiro = null;
        $this->pautaSuggestions = [];
        $this->suggestingPauta = false;
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
        $this->form['preco_total'] = round(
            (float) $this->form['quantidade'] * (float) $this->form['preco_unitario'],
            2
        );
    }

    public function updatedFormSubcategoriaId($value)
    {
        $this->pautas = [];
        $this->showVeiculos = false;
        $this->showMaquinas = false;

        if (!$value) {
            return;
        }

        $this->loadPautasForSubcategoria((int) $value);
        
        // limpa código anterior
        $this->form['codigo_aduaneiro'] = '';
    }

    public function updatedFormCodigoAduaneiro($value)
    {
        $this->showVeiculos = false;
        $this->showMaquinas = false;
        $this->codigoStatus = 'idle';
        $this->codigoMessage = '';
        $this->pautaSuggestions = [];

        if ($this->mode === 'edit' && $this->codigoPautalChanged() && ($this->form['pauta_change_source'] ?? 'manual') !== 'ai_suggestion') {
            $this->form['pauta_change_source'] = 'manual';
        }

        if (!$value || count($this->pautas) === 0) {
            return;
        }

        // Prefixos válidos para veículos
        $rules = app(MercadoriaRules::class);
        $this->showVeiculos = $rules->isVehicleCode((string) $value);
        $this->showMaquinas = $rules->isMachineCode((string) $value);

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

    public function suggestCodigoPautal(): void
    {
        $this->suggestingPauta = true;
        $this->pautaSuggestions = [];

        try {
            $this->pautaSuggestions = app(SugerirCodigoPautalAction::class)->execute(PautaSuggestionDTO::fromArray([
                'descricao' => $this->form['descricao'] ?? null,
                'subcategoria_id' => $this->form['subcategoria_id'] ?? null,
                'marca' => $this->form['marca'] ?? null,
                'modelo' => $this->form['modelo'] ?? null,
                'chassis' => $this->form['chassis'] ?? null,
                'codigo_aduaneiro' => $this->form['codigo_aduaneiro'] ?? null,
                'limit' => 5,
            ]));

            if ($this->pautaSuggestions === []) {
                $this->dispatch('toast', type: 'warning', message: 'Nenhuma sugestão encontrada para esta mercadoria.');
            }
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'Erro ao sugerir código pautal: ' . $e->getMessage());
        } finally {
            $this->suggestingPauta = false;
        }
    }

    public function applyPautaSuggestion(int $pautaId): void
    {
        $suggestion = collect($this->pautaSuggestions)->firstWhere('pauta_aduaneira_id', $pautaId);

        if (! $suggestion) {
            return;
        }

        $this->form['codigo_aduaneiro'] = $suggestion['codigo'];
        $this->form['pauta_change_source'] = 'ai_suggestion';

        if (! collect($this->pautas)->contains(fn ($pauta) => $pauta->codigo === $suggestion['codigo'])) {
            $this->pautas[] = (object) [
                'codigo' => $suggestion['codigo'],
                'descricao' => $suggestion['descricao'],
            ];
        }

        if ($this->mode === 'edit' && $this->codigoPautalChanged() && empty($this->form['pauta_change_reason'])) {
            $this->form['pauta_change_reason'] = 'Sugestão de IA aplicada: ' . ($suggestion['reason'] ?? 'código pautal sugerido.');
        }

        $this->updatedFormCodigoAduaneiro($suggestion['codigo']);
        $this->dispatch('pautaSuggestionSelected', pauta_aduaneira_id: $pautaId, codigo: $suggestion['codigo']);
    }


    public function save(): void
    {
        $this->validate();
        $this->calcularTotal();

        // Bloqueio caso o código esteja errado
        if ($this->codigoStatus !== 'valid') {
            $this->addError('form.codigo_aduaneiro', 'Código aduaneiro inválido ou incompleto.');
            return;
        }

        try {
            $data = MercadoriaData::fromLivewire(
                $this->form,
                $this->context,
                $this->parentId,
                $this->mode === 'edit' ? $this->mercadoriaId : null
            );

            if ($this->mode === 'edit') {
                app(AtualizarMercadoriaAction::class)->execute($data);
            } else {
                app(CriarMercadoriaAction::class)->execute($data);
            }

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
            app(ExcluirMercadoriaAction::class)->execute((int) $id, $this->context, $this->parentId);
            
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

    private function loadPautasForSubcategoria(?int $subcategoriaId): void
    {
        $subcategoria = $subcategoriaId ? Subcategoria::find($subcategoriaId) : null;

        if (! $subcategoria) {
            return;
        }

        $this->pautas = app(PautaAduaneiraLookupService::class)
            ->bySubcategoriaId($subcategoria->id)
            ->map(fn ($pauta) => (object) [
                'codigo' => $pauta->codigo,
                'descricao' => $pauta->descricao,
            ])
            ->values()
            ->all();

        $this->showVeiculos = in_array((int) $subcategoria->cod_pauta, [87, 88], true);
        $this->showMaquinas = (int) $subcategoria->cod_pauta === 84;
    }

    public function codigoPautalChanged(): bool
    {
        if ($this->mode !== 'edit') {
            return false;
        }

        return trim((string) $this->originalCodigoAduaneiro) !== trim((string) ($this->form['codigo_aduaneiro'] ?? ''));
    }
}
