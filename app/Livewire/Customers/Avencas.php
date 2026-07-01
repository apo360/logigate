<?php

namespace App\Livewire\Customers;

use App\Application\Customer\Avencas\Actions\ChangeCustomerAvencaStatusAction;
use App\Application\Customer\Avencas\Actions\CreateCustomerAvencaAction;
use App\Application\Customer\Avencas\Actions\GerarMovimentoContaCorrenteDaAvencaAction;
use App\Application\Customer\Avencas\Actions\ListCustomerAvencasAction;
use App\Application\Customer\Avencas\Actions\UpdateCustomerAvencaAction;
use App\Application\Customer\Avencas\DTOs\CustomerAvencaData;
use App\Application\Customer\Services\CustomerTenantAccessService;
use App\Models\Customer;
use App\Models\CustomerAvenca;
use App\Models\ContaCorrente;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class Avencas extends Component
{
    use AuthorizesRequests;

    public int $customerId;

    public Customer $customer;

    public int $empresaId;

    public bool $showStructuredNotice = true;

    public bool $schemaReady = false;

    public bool $canManage = false;

    public bool $canGenerateMovimento = false;

    public bool $showForm = false;

    public ?int $editingId = null;

    public array $form = [
        'titulo' => '',
        'descricao' => '',
        'valor' => '',
        'periodicidade' => 'mensal',
        'data_inicio' => '',
        'data_fim' => '',
        'dia_cobranca' => '',
        'status' => 'rascunho',
        'observacoes' => '',
    ];

    protected function rules(): array
    {
        return [
            'form.titulo' => ['required', 'string', 'max:255'],
            'form.descricao' => ['nullable', 'string', 'max:2000'],
            'form.valor' => ['required', 'numeric', 'min:0'],
            'form.periodicidade' => ['required', 'in:mensal,trimestral,semestral,anual'],
            'form.data_inicio' => ['required', 'date'],
            'form.data_fim' => ['nullable', 'date', 'after_or_equal:form.data_inicio'],
            'form.dia_cobranca' => ['nullable', 'integer', 'min:1', 'max:31'],
            'form.status' => ['required', 'in:rascunho,ativa,suspensa,cancelada,encerrada,expirada'],
            'form.observacoes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function mount(int|Customer|null $customer = null, ?int $customerId = null): void
    {
        $this->customerId = $customer instanceof Customer ? (int) $customer->id : (int) ($customerId ?? $customer);
        $this->empresaId = $this->currentEmpresaId();

        $this->customer = $this->tenantCustomerQuery()
            ->with('avencas')
            ->findOrFail($this->customerId);

        $this->authorize('view', $this->customer);

        $this->schemaReady = Schema::hasColumn('customer_avencas', 'empresa_id')
            && Schema::hasColumn('customer_avencas', 'status')
            && Schema::hasColumn('customer_avencas', 'titulo');

        $this->canManage = $this->schemaReady
            && Auth::user()?->can('create', [CustomerAvenca::class, $this->customer]);

        $this->canGenerateMovimento = $this->schemaReady
            && Schema::hasColumn('conta_correntes', 'empresa_id')
            && Schema::hasColumn('conta_correntes', 'customer_avenca_id')
            && Schema::hasColumn('conta_correntes', 'origem_tipo')
            && Schema::hasColumn('conta_correntes', 'origem_id')
            && Schema::hasColumn('conta_correntes', 'metadata')
            && Auth::user()?->can('create', [ContaCorrente::class, $this->customer]);
    }

    public function openCreateForm(): void
    {
        abort_unless($this->canManage, 409, 'A tabela de avenças ainda não está pronta para escrita tenant-safe.');

        $this->resetValidation();
        $this->editingId = null;
        $this->form = [
            'titulo' => '',
            'descricao' => '',
            'valor' => '',
            'periodicidade' => 'mensal',
            'data_inicio' => now()->toDateString(),
            'data_fim' => '',
            'dia_cobranca' => '',
            'status' => 'rascunho',
            'observacoes' => '',
        ];
        $this->showForm = true;
    }

    public function openEditForm(int $avencaId): void
    {
        abort_unless($this->canManage, 409, 'A tabela de avenças ainda não está pronta para edição tenant-safe.');

        $avenca = $this->findTenantAvenca($avencaId);
        $this->authorize('update', $avenca);

        $this->resetValidation();
        $this->editingId = $avenca->id;
        $this->form = [
            'titulo' => $avenca->titulo_exibicao,
            'descricao' => (string) ($avenca->descricao ?? ''),
            'valor' => (string) $avenca->valor,
            'periodicidade' => (string) $avenca->periodicidade,
            'data_inicio' => $avenca->data_inicio?->toDateString() ?? '',
            'data_fim' => $avenca->data_fim?->toDateString() ?? '',
            'dia_cobranca' => (string) ($avenca->dia_cobranca ?? ''),
            'status' => $avenca->estado,
            'observacoes' => (string) ($avenca->observacoes ?? ''),
        ];
        $this->showForm = true;
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->editingId = null;
        $this->resetValidation();
    }

    public function save(CreateCustomerAvencaAction $createAction, UpdateCustomerAvencaAction $updateAction): void
    {
        abort_unless($this->canManage, 409, 'A tabela de avenças ainda não está pronta para escrita tenant-safe.');

        $validated = $this->validate()['form'];
        $payload = [
            ...$validated,
            'empresa_id' => $this->empresaId,
            'customer_id' => $this->customerId,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ];

        if ($this->editingId) {
            $avenca = $this->findTenantAvenca($this->editingId);
            $this->authorize('update', $avenca);
            $updateAction->execute($avenca, CustomerAvencaData::fromArray($payload));
            session()->flash('success', 'Avença actualizada com sucesso.');
        } else {
            $this->authorize('create', [CustomerAvenca::class, $this->customer]);
            $createAction->execute(CustomerAvencaData::fromArray($payload));
            session()->flash('success', 'Avença criada com sucesso.');
        }

        $this->closeForm();
    }

    public function changeStatus(int $avencaId, string $status, ChangeCustomerAvencaStatusAction $action): void
    {
        abort_unless($this->canManage, 409, 'A tabela de avenças ainda não está pronta para alteração de estado.');

        $avenca = $this->findTenantAvenca($avencaId);
        $this->authorize('changeStatus', $avenca);

        $action->execute($avenca, $this->empresaId, $status, Auth::id());

        session()->flash('success', 'Estado da avença actualizado com sucesso.');
    }

    public function gerarMovimento(int $avencaId, GerarMovimentoContaCorrenteDaAvencaAction $action): void
    {
        abort_unless($this->canGenerateMovimento, 409, 'A Conta Corrente ainda não está pronta para receber débitos de avença.');

        $avenca = $this->findTenantAvenca($avencaId);
        $this->authorize('changeStatus', $avenca);
        $this->authorize('create', [ContaCorrente::class, $this->customer]);

        $action->execute($avenca, Auth::user(), $this->empresaId);

        $this->dispatch('conta-corrente-atualizada');
        session()->flash('success', 'Débito da avença gerado na Conta Corrente.');
    }

    public function render()
    {
        $avencas = app(ListCustomerAvencasAction::class)->execute($this->empresaId, $this->customerId);

        return view('livewire.customers.avencas', [
            'avencas' => $avencas,
            'totalAvencas' => $avencas->count(),
            'avencasAtivas' => $avencas->where('estado', 'ativa')->count(),
            'valorMensalEstimado' => $this->valorMensalEstimado($avencas),
            'proximaCobranca' => $avencas
                ->filter(fn (CustomerAvenca $avenca) => $avenca->proxima_cobranca_em !== null)
                ->sortBy('proxima_cobranca_em')
                ->first()?->proxima_cobranca_em,
        ]);
    }

    private function tenantCustomerQuery()
    {
        $query = Customer::query();

        if (!Schema::hasColumn('customers', 'deleted_at')) {
            $query->withoutGlobalScope(SoftDeletingScope::class);
        }

        return $query->where(function ($tenantQuery): void {
            $tenantQuery->where('empresa_id', $this->empresaId);

            if (Schema::hasTable('customers_empresas')) {
                $tenantQuery->orWhereHas('empresas', fn ($empresaQuery) => $empresaQuery->where('empresas.id', $this->empresaId));
            }
        });
    }

    private function findTenantAvenca(int $avencaId): CustomerAvenca
    {
        return CustomerAvenca::query()
            ->whereKey($avencaId)
            ->where('customer_id', $this->customerId)
            ->forEmpresa($this->empresaId)
            ->firstOrFail();
    }

    private function currentEmpresaId(): int
    {
        $empresaId = app(CustomerTenantAccessService::class)->empresaId(Auth::user());
        abort_if(!$empresaId, 403);

        return (int) $empresaId;
    }

    private function valorMensalEstimado($avencas): float
    {
        return (float) $avencas
            ->where('estado', 'ativa')
            ->sum(function (CustomerAvenca $avenca): float {
                $valor = (float) $avenca->valor;

                return match ($avenca->periodicidade) {
                    'trimestral' => $valor / 3,
                    'semestral' => $valor / 6,
                    'anual' => $valor / 12,
                    default => $valor,
                };
            });
    }
}
