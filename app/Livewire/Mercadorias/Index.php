<?php

namespace App\Livewire\Mercadorias;

use Livewire\Component;
use App\Models\Mercadoria;
use App\Models\MercadoriaAgrupada;


class Index extends Component
{
    public string $context; // processo | licenciamento | outros
    public int $parentId;

    public array $agrupadas = [];
    public array $mercadorias = [];
    public array $expanded = [];

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
        $this->mercadorias = Mercadoria::query()
            ->when($this->context === 'processo', fn($q) => $q->where('Fk_Importacao', $this->parentId))
            ->when($this->context === 'licenciamento', fn($q) => $q->where('licenciamento_id', $this->parentId))
            ->orderBy('codigo_aduaneiro')
            ->get()
            ->toArray();

        $this->agrupadas = MercadoriaAgrupada::query()
            ->when($this->context === 'processo', fn($q) => $q->where('processo_id', $this->parentId))
            ->when($this->context === 'licenciamento', fn($q) => $q->where('licenciamento_id', $this->parentId))
            ->orderBy('codigo_aduaneiro')
            ->get()
            ->toArray();

        foreach ($this->agrupadas as $g) {
            $this->expanded[$g['codigo_aduaneiro']] ??= false;
        }
    }


    public function toggle(string $codigo)
    {
        $this->expanded[$codigo] = ! $this->expanded[$codigo];
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
}
