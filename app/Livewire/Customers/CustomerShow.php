<?php

namespace App\Livewire\Customers;

use App\Application\Customer\Services\CustomerTenantAccessService;
use App\Application\Customer\Queries\CustomerDetailsQuery;
use App\Application\FacturacaoIntegracao\Actions\SincronizarClienteFacturacaoAction;
use App\Application\FacturacaoIntegracao\DTOs\SincronizarClienteFacturacaoDTO;
use App\Application\Integracoes\Services\IntegracaoResolverService;
use App\Domains\Customers\Services\CustomerAccountStatementService;
use App\Domains\Integracoes\Enums\ProvedorIntegracaoEnum;
use App\Domains\Integracoes\Enums\TipoIntegracaoEnum;
use App\Models\Customer;
use App\Models\EmpresaIntegracao;
use App\Models\ExternalCustomerMapping;
use App\Models\Provincia;
use Carbon\Carbon;
use Livewire\Component;

class CustomerShow extends Component
{
    public Customer $customer;

    public int $customerId;

    public array $labels = [];

    public array $atividadeMes = [
        'processos' => [],
        'licenciamentos' => [],
    ];

    public string $activePanel = 'overview';

    public bool $hongayetuActiva = false;

    public ?ExternalCustomerMapping $hongayetuMapping = null;

    public ?string $hongayetuSyncMessage = null;

    public ?string $hongayetuSyncError = null;

    public function mount(int|Customer $customer, CustomerDetailsQuery $query): void
    {
        $id = $customer instanceof Customer ? $customer->id : (int) $customer;

        $this->customer = $query->execute($id);
        $this->customerId = $this->customer->id;

        $this->prepareActivityChartData();
        $this->loadHongayetuState();
    }

    public function openPortalCredentialsModal(): void
    {
        $this->dispatch(
            'open-customer-portal-credentials-modal',
            customerId: $this->customer->id
        );
    }

    public function setPanel(string $panel): void
    {
        abort_unless(in_array($panel, ['overview', 'conta-corrente', 'avencas'], true), 404);

        $this->activePanel = $panel;
    }

    public function sincronizarHongayetu(SincronizarClienteFacturacaoAction $action): void
    {
        $this->hongayetuSyncMessage = null;
        $this->hongayetuSyncError = null;

        $empresaId = $this->currentEmpresaId();

        if (! $empresaId || ! $this->customerBelongsToEmpresa($empresaId)) {
            $this->hongayetuSyncError = 'Cliente não pertence à empresa actual.';
            return;
        }

        $resolver = app(IntegracaoResolverService::class);

        if (! $resolver->isFacturacaoHongayetuActiva($empresaId)) {
            $this->hongayetuActiva = false;
            $this->hongayetuSyncError = 'A integração de Facturação Hongayetu não está activa.';
            return;
        }

        $this->loadHongayetuState();

        if ($this->hongayetuMapping) {
            $this->hongayetuSyncMessage = 'Cliente já sincronizado com a Facturação Hongayetu.';
            return;
        }

        try {
            $integracao = $resolver->resolveForEmpresaId(
                $empresaId,
                TipoIntegracaoEnum::Facturacao,
                ProvedorIntegracaoEnum::HongayetuFacturacao,
            );

            $this->hongayetuMapping = $action->execute($this->syncDto($empresaId, $integracao));
            $this->hongayetuSyncMessage = 'Cliente sincronizado com a Facturação Hongayetu.';
            $this->customer = $this->customer->fresh();
        } catch (\Throwable $exception) {
            report($exception);

            $this->hongayetuSyncError = $exception->getMessage();
        }

        $this->loadHongayetuState();
    }

    // Activar e desativar User
    public function toggleStatus(){
        // 
    }

    private function prepareActivityChartData(): void
    {
        $months = collect(range(5, 0))
            ->map(fn (int $monthsAgo) => now()->subMonths($monthsAgo)->startOfMonth());

        $this->labels = $months
            ->map(fn (Carbon $date) => $date->format('M/Y'))
            ->values()
            ->toArray();

        $this->atividadeMes = [
            'processos' => $months
                ->map(function (Carbon $date) {
                    return $this->customer->processos
                        ->filter(fn ($processo) => $processo->created_at?->isSameMonth($date))
                        ->count();
                })
                ->values()
                ->toArray(),

            'licenciamentos' => $months
                ->map(function (Carbon $date) {
                    return $this->customer->licenciamento
                        ->filter(fn ($licenciamento) => $licenciamento->created_at?->isSameMonth($date))
                        ->count();
                })
                ->values()
                ->toArray(),
        ];
    }

    private function loadHongayetuState(): void
    {
        $empresaId = $this->currentEmpresaId();

        $this->hongayetuActiva = app(IntegracaoResolverService::class)
            ->isFacturacaoHongayetuActiva($empresaId);

        $this->hongayetuMapping = $empresaId
            ? $this->mappingFor($empresaId)
            : null;
    }

    private function mappingFor(int $empresaId): ?ExternalCustomerMapping
    {
        return ExternalCustomerMapping::query()
            ->where('empresa_id', $empresaId)
            ->where('customer_id', $this->customer->id)
            ->where('provider', ExternalCustomerMapping::PROVIDER_HONGAYETU_FACTURACAO)
            ->latest('synced_at')
            ->latest('id')
            ->first();
    }

    private function currentEmpresaId(): ?int
    {
        return app(CustomerTenantAccessService::class)->currentEmpresaId();
    }

    private function customerBelongsToEmpresa(int $empresaId): bool
    {
        return Customer::query()
            ->forEmpresa($empresaId)
            ->whereKey($this->customer->id)
            ->exists();
    }

    private function syncDto(int $empresaId, EmpresaIntegracao $integracao): SincronizarClienteFacturacaoDTO
    {
        $this->customer->loadMissing('endereco');

        return new SincronizarClienteFacturacaoDTO(
            empresaId: $empresaId,
            empresaIntegracaoId: $integracao->id,
            customerId: $this->customer->id,
            nome: $this->customer->CompanyName,
            tipo: $this->hongayetuCustomerType($this->customer->CustomerType),
            nif: $this->customer->CustomerTaxID,
            telefone: $this->customer->Telephone,
            email: $this->customer->Email,
            endereco: $this->customer->endereco?->AddressDetail,
            provinciaId: $this->hongayetuProvinceId($this->customer->endereco?->Province),
            grupoId: $this->configuredDefaultGroupId(),
        );
    }

    private function hongayetuCustomerType(?string $type): int
    {
        $normalized = str($type ?? '')->lower()->trim()->toString();

        return in_array($normalized, ['individual', 'singular', 'pessoa singular', '0'], true) ? 0 : 1;
    }

    private function hongayetuProvinceId(?string $province): ?int
    {
        $province = trim((string) $province);

        if ($province === '') {
            return null;
        }

        if (is_numeric($province)) {
            return (int) $province;
        }

        return Provincia::query()
            ->where('Nome', $province)
            ->value('id');
    }

    private function configuredDefaultGroupId(): ?int
    {
        $groupId = config('hongayetu_facturacao.default_customer_group_id')
            ?? config('hongayetu_facturacao.grupo_id_padrao');

        return is_numeric($groupId) ? (int) $groupId : null;
    }

    public function render()
    {
        $empresaId = auth()->user()?->empresa_id
            ?? auth()->user()?->empresas()->value('empresas.id');

        $statementService = app(CustomerAccountStatementService::class);
        $ultimoMovimentoContaCorrente = $statementService
            ->movimentosRecentes($this->customer->id, $empresaId ? (int) $empresaId : null, 1)
            ->first();

        return view('livewire.customers.customer-show', [
            'labels' => $this->labels,
            'atividadeMes' => $this->atividadeMes,
            'saldoContaCorrente' => $statementService->saldo($this->customer->id, $empresaId ? (int) $empresaId : null),
            'ultimoMovimentoContaCorrente' => $ultimoMovimentoContaCorrente,
        ]);
    }
}
