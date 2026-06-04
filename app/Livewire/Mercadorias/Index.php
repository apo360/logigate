<?php

namespace App\Livewire\Mercadorias;

use App\Application\Mercadoria\Actions\ExcluirMercadoriaAction;
use App\Application\Mercadoria\Queries\ListarMercadoriasQuery;
use Livewire\Component;

class Index extends Component
{
    public string $context; // processo | licenciamento | outros
    public int $parentId;

    public array $agrupadas = [];
    public array $mercadorias = [];
    public array $expanded = [];
    public array $totais = [
        'quantidade' => 0,
        'peso' => 0,
        'fob' => 0,
        'fob_aplicado' => 0,
    ];

    public float $fob_percent = 100;

    public ?int $editingId = null;
    public array $editingFields = [];


    protected $listeners = [
        'mercadoriaCreated' => 'reload',
        'mercadoriaUpdated' => 'reload',
        'mercadoriaDeleted' => 'reload',
        'open-edit-mercadoria' => 'handleEdit',
        'close-modal' => 'closeModal',
    ];

    public function handleEdit(int $id): void
    {
        // Apenas reencaminha para o formulário
        $this->dispatch('open-edit-mercadoria', id: $id)
             ->to('mercadorias.create-form');
    }

    public function closeModal(): void
    {
        $this->dispatchBrowserEvent('close-modal', ['modal' => 'edit-mercadoria']);
        $this->editingId = null;
        $this->editingFields = [];
    }

    public function mount(string $context, int $parentId)
    {
        $this->context = $context;
        $this->parentId = $parentId;
        $this->reload();
    }


    public function reload(): void
    {
        $result = app(ListarMercadoriasQuery::class)->execute($this->context, $this->parentId);

        $this->mercadorias = $result['mercadorias'];
        $this->agrupadas = $result['agrupadas'];
        $this->totais = $this->withAppliedFob($result['totais']);

        foreach ($this->agrupadas as $g) {
            $this->expanded[$g['codigo_aduaneiro']] ??= false;
        }
    }


    public function toggle(string $codigo)
    {
        $this->expanded[$codigo] = ! $this->expanded[$codigo];
    }

    public function updatedFobPercent(): void
    {
        $this->totais = $this->withAppliedFob($this->totais);
    }

    public function deleteItem(int $id): void
    {
        try {
            app(ExcluirMercadoriaAction::class)->execute($id, $this->context, $this->parentId);
            $this->reload();
            $this->dispatch('mercadoriaDeleted', id: $id);
            $this->dispatch('toast', type: 'success', message: 'Mercadoria excluída com sucesso!');
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'Erro ao excluir mercadoria: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $groups = collect($this->agrupadas)->map(function ($g) {
            return array_merge($g, [
                'children' => collect($this->mercadorias)
                    ->where('codigo_aduaneiro', $g['codigo_aduaneiro'])
                    ->values()->toArray()
            ]);
        });

        return view('livewire.mercadorias.index', compact('groups'));
    }

    private function withAppliedFob(array $totais): array
    {
        $fob = (float) ($totais['fob'] ?? 0);
        $percent = max(0, min((float) $this->fob_percent, 100));
        $this->fob_percent = $percent;

        $totais['fob_aplicado'] = round($fob * ($percent / 100), 2);

        return $totais;
    }
}
