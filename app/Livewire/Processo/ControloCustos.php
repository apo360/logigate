<?php

namespace App\Livewire\Processo;

use App\Application\Processo\Services\ProcessoCustoService;
use App\Models\Processo;
use App\Models\ProcessoCustoAlerta;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ControloCustos extends Component
{
    use WithPagination;

    #[Url(as: 'processo')]
    public ?int $processoId = null;

    #[Url]
    public string $pesquisa = '';

    #[Url]
    public string $natureza = '';

    #[Url]
    public string $estado = '';

    #[Url]
    public string $pendencia = '';

    public string $responsavelFiltro = '';

    public string $inicio = '';

    public string $fim = '';

    public array $registo = [];

    public array $tratamento = ['estado' => 'encaminhado', 'responsavel_id' => '', 'prazo' => '', 'justificacao' => ''];

    public string $alerta = '';

    public string $motivo = '';

    public array $config = [];

    public string $mensagem = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', Processo::class);
        if ($this->processoId) {
            $this->service()->processo($this->processoId);
        }
        $this->config = $this->service()->configuracao();
        $this->limparRegisto();
    }

    private function service(): ProcessoCustoService
    {
        return app(ProcessoCustoService::class);
    }

    public function updated($field): void
    {
        if (in_array($field, ['pesquisa', 'natureza', 'estado', 'pendencia', 'responsavelFiltro', 'inicio', 'fim'], true)) {
            $this->resetPage();
        }
    }

    private function limparRegisto(): void
    {
        $this->registo = ['fase' => 'declarado', 'escopo' => 'tributos', 'valor' => '', 'data' => today()->toDateString(), 'referencia' => '', 'descricao' => '', 'documento_id' => '', 'external_invoice_id' => '', 'origem_id' => ''];
    }

    public function seleccionar(int $id): void
    {
        $this->service()->processo($id);
        $this->processoId = $id;
        $this->alerta = '';
        $this->mensagem = '';
        $this->motivo = '';
        $this->limparRegisto();
        $this->resetValidation();
        $this->dispatch('custo-seleccionado');
    }

    public function gravar(): void
    {
        $this->service()->registar($this->processoId ?? 0, array_map(fn ($v) => $v === '' ? null : $v, $this->registo));
        $this->limparRegisto();
        $this->resetValidation();
        $this->mensagem = 'Registo guardado e conferência actualizada.';
    }

    public function actualizar(): void
    {
        $this->service()->sincronizar($this->processoId ?? 0);
        $this->mensagem = 'Conferência actualizada com os dados actuais.';
    }

    public function anular(int $id): void
    {
        $this->service()->anular($this->processoId ?? 0, $id, $this->motivo);
        $this->motivo = '';
        $this->mensagem = 'Registo anulado. O original foi preservado no histórico.';
    }

    public function abrirAlerta(string $key): void
    {
        $r = $this->service()->relatorio($this->processoId ?? 0);
        abort_unless(isset($r['alerts'][$key]) || isset($r['workflow'][$key]), 404);
        $this->alerta = $key;
        $a = $r['workflow']->get($key);
        $this->tratamento = ['estado' => $a?->estado ?? 'encaminhado', 'responsavel_id' => $a?->responsavel_id ?? '', 'prazo' => $a?->prazo?->toDateString() ?? '', 'justificacao' => $a?->justificacao ?? ''];
    }

    public function tratar(): void
    {
        $this->service()->tratar($this->processoId ?? 0, $this->alerta, array_map(fn ($v) => $v === '' ? null : $v, $this->tratamento));
        $this->alerta = '';
        $this->mensagem = 'Tratamento registado no histórico.';
    }

    public function configurar(): void
    {
        $this->service()->guardarConfiguracao($this->processoId ?? 0, $this->config);
        $this->service()->sincronizar($this->processoId ?? 0);
        $this->mensagem = 'Critérios da empresa actualizados. Actualize a conferência dos restantes processos para renovar a fila.';
    }

    public function render()
    {
        Gate::authorize('viewAny', Processo::class);
        $empresa = TenantContext::empresaId();
        $query = Processo::where('empresa_id', $empresa)->with('cliente');
        if ($this->pesquisa !== '') {
            $term = '%'.mb_substr($this->pesquisa, 0, 100).'%';
            $query->where(fn ($q) => $q->where('NrProcesso', 'like', $term)->orWhere('RefCliente', 'like', $term)->orWhereHas('cliente', fn ($q) => $q->where('CompanyName', 'like', $term)));
        }
        if (in_array($this->natureza, ['geral', 'exportacao_crude'], true)) {
            $query->where('natureza_operacao', $this->natureza);
        }
        if ($this->natureza === 'legado') {
            $query->whereNull('natureza_operacao');
        }
        if ($this->estado !== '') {
            $query->where('Estado', mb_substr($this->estado, 0, 60));
        }
        foreach (['inicio' => '>=', 'fim' => '<='] as $field => $operator) {
            $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $this->{$field});
            if ($date && $date->format('Y-m-d') === $this->{$field}) {
                $query->whereDate('DataAbertura', $operator, $this->{$field});
            }
        }
        $pending = ProcessoCustoAlerta::where('empresa_id', $empresa)->where('detectado', true)->whereIn('estado', ['aberto', 'encaminhado'])
            ->whereIn('processo_id', Processo::where('empresa_id', $empresa)->select('id'));
        if ($this->pendencia !== '' || $this->responsavelFiltro !== '') {
            $filter = clone $pending;
            if (in_array($this->pendencia, ['aberto', 'encaminhado'], true)) {
                $filter->where('estado', $this->pendencia);
            }
            if ($this->pendencia === 'atrasado') {
                $filter->whereDate('prazo', '<', today());
            }
            if ($this->responsavelFiltro !== '') {
                $filter->where('responsavel_id', (int) $this->responsavelFiltro);
            }
            $query->whereIn('id', $filter->select('processo_id'));
        }
        $queue = (clone $pending)->whereIn('processo_id', (clone $query)->select('processos.id'))
            ->with('processo')->orderByRaw('prazo IS NULL')->orderBy('prazo')->orderBy('id')->limit(20)->get();
        $processos = $query->orderByDesc('id')->paginate(12);
        $counts = (clone $pending)->whereIn('processo_id', $processos->modelKeys())->selectRaw('processo_id, COUNT(*) as total')->groupBy('processo_id')->pluck('total', 'processo_id');
        $report = $this->processoId ? $this->service()->relatorio($this->processoId) : null;
        $users = User::whereIn('id', DB::table('empresa_users')->where('empresa_id', $empresa)->select('user_id'))->orderBy('name')->get(['id', 'name']);

        return view('livewire.processo.controlo-custos', [
            'processos' => $processos, 'counts' => $counts, 'report' => $report, 'users' => $users, 'queue' => $queue,
            'pendingCount' => (clone $pending)->count(), 'lateCount' => (clone $pending)->whereDate('prazo', '<', today())->count(),
            'documentos' => $report ? $this->service()->documentos($report['p']) : collect(),
            'facturas' => $report ? $this->service()->facturas($report['p']) : collect(),
            'estados' => Processo::where('empresa_id', $empresa)->whereNotNull('Estado')->distinct()->pluck('Estado'),
        ]);
    }
}
