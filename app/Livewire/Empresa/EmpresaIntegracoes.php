<?php

namespace App\Livewire\Empresa;

use App\Application\Integracoes\Actions\ActivarIntegracaoAction;
use App\Application\Integracoes\Actions\ActualizarCredenciaisIntegracaoAction;
use App\Application\Integracoes\Actions\DesactivarIntegracaoAction;
use App\Application\Integracoes\Actions\TestarIntegracaoAction;
use App\Application\Integracoes\DTOs\IntegracaoConfigDTO;
use App\Domains\Integracoes\Enums\EstadoIntegracaoEnum;
use App\Domains\Integracoes\Enums\ProvedorIntegracaoEnum;
use App\Domains\Integracoes\Enums\TipoIntegracaoEnum;
use App\Models\Empresa;
use App\Models\EmpresaIntegracao;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class EmpresaIntegracoes extends Component
{
    public ?Empresa $empresa = null;

    public bool $schemaReady = true;

    public array $form = [];

    public ?string $selectedTipo = null;

    public ?string $selectedProvedor = null;

    public function mount(): void
    {
        $this->empresa = Auth::user()?->empresaAtiva();
        abort_unless($this->empresa, 403);
        Gate::forUser(Auth::user())->authorize('manageIntegrations', $this->empresa);

        $this->schemaReady = Schema::hasTable('empresa_integracoes');
    }

    public function openConfigure(string $tipo, string $provedor): void
    {
        $this->authorizeManageIntegrations();

        TipoIntegracaoEnum::from($tipo);
        ProvedorIntegracaoEnum::from($provedor);

        $this->selectedTipo = $tipo;
        $this->selectedProvedor = $provedor;

        $integration = $this->findIntegration($tipo, $provedor);

        $this->form = [
            'config' => $this->defaultConfig($tipo, $provedor, $integration?->config ?? []),
            'credentials' => [],
            'masked_credentials' => $integration?->maskedCredentials() ?? [],
        ];

        $this->resetValidation();
        $this->dispatch('open-modal', id: 'empresa-integracao-config');
    }

    public function save(ActualizarCredenciaisIntegracaoAction $action): void
    {
        $this->authorizeManageIntegrations();
        $this->ensureSchemaReady();

        $validated = $this->validate($this->rules());
        $tipo = TipoIntegracaoEnum::from((string) $this->selectedTipo);
        $provedor = ProvedorIntegracaoEnum::from((string) $this->selectedProvedor);
        $current = $this->findIntegration($tipo->value, $provedor->value);

        $action->execute(Auth::user(), $this->empresa, new IntegracaoConfigDTO(
            tipo: $tipo,
            provedor: $provedor,
            estado: $current?->estado ?? EstadoIntegracaoEnum::EmConfiguracao,
            config: $validated['form']['config'],
            credentials: $validated['form']['credentials'] ?? [],
        ));

        $this->form['credentials'] = [];
        $this->form['masked_credentials'] = $this->findIntegration($tipo->value, $provedor->value)?->maskedCredentials() ?? [];

        $this->dispatch('close-modal', id: 'empresa-integracao-config');
        $this->dispatch('toast', type: 'success', message: 'Integração guardada com sucesso.');
    }

    public function activate(int $integrationId, ActivarIntegracaoAction $action): void
    {
        $this->authorizeManageIntegrations();
        $this->ensureSchemaReady();
        $action->execute(Auth::user(), $this->empresa, $this->integrationForEmpresa($integrationId));
        $this->dispatch('toast', type: 'success', message: 'Integração activada.');
    }

    public function deactivate(int $integrationId, DesactivarIntegracaoAction $action): void
    {
        $this->authorizeManageIntegrations();
        $this->ensureSchemaReady();
        $action->execute(Auth::user(), $this->empresa, $this->integrationForEmpresa($integrationId));
        $this->dispatch('toast', type: 'success', message: 'Integração desactivada.');
    }

    public function test(int $integrationId, TestarIntegracaoAction $action): void
    {
        $this->authorizeManageIntegrations();
        $this->ensureSchemaReady();
        $result = $action->execute(Auth::user(), $this->empresa, $this->integrationForEmpresa($integrationId));
        $this->dispatch('toast', type: $result->success ? 'success' : 'error', message: $result->message);
    }

    public function cards(): array
    {
        return [
            ['tipo' => 'facturacao', 'provedor' => 'hongayetu_facturacao', 'title' => 'Facturação', 'icon' => 'fa-file-invoice-dollar', 'featured' => true],
            ['tipo' => 'whatsapp', 'provedor' => 'meta_whatsapp', 'title' => 'WhatsApp', 'icon' => 'fa-brands fa-whatsapp', 'featured' => false],
            ['tipo' => 'sms', 'provedor' => 'generic_sms', 'title' => 'SMS', 'icon' => 'fa-comment-sms', 'featured' => false],
            ['tipo' => 'email', 'provedor' => 'smtp_custom', 'title' => 'Email', 'icon' => 'fa-envelope', 'featured' => false],
            ['tipo' => 'pagamentos', 'provedor' => 'appy_pay', 'title' => 'Pagamentos', 'icon' => 'fa-credit-card', 'featured' => false],
            ['tipo' => 'storage', 'provedor' => 's3_custom', 'title' => 'Storage', 'icon' => 'fa-cloud', 'featured' => false],
        ];
    }

    public function integrations(): Collection
    {
        if (! $this->schemaReady) {
            return collect();
        }

        return EmpresaIntegracao::query()
            ->where('empresa_id', $this->empresa->id)
            ->get()
            ->keyBy(fn (EmpresaIntegracao $integration) => $integration->tipo->value . ':' . $integration->provedor->value);
    }

    private function rules(): array
    {
        $base = [
            'form.config.api_url' => ['nullable', 'url', 'max:255'],
            'form.config.ambiente' => ['nullable', 'in:teste,producao'],
            'form.config.timeout' => ['nullable', 'integer', 'min:1', 'max:120'],
            'form.config.retry_attempts' => ['nullable', 'integer', 'min:0', 'max:5'],
            'form.config.retry_sleep' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'form.credentials' => ['array'],
        ];

        if ($this->selectedProvedor === ProvedorIntegracaoEnum::HongayetuFacturacao->value) {
            $base['form.config.api_url'] = ['required', 'url', 'max:255'];
            $base['form.config.idempotency_strategy'] = ['nullable', 'string', 'max:80'];
            $base['form.config.empresa_fiscal_id'] = ['nullable', 'string', 'max:100'];
            $base['form.config.nif'] = ['nullable', 'string', 'max:50'];
            $base['form.credentials.api_token'] = ['nullable', 'string', 'max:2000'];
            $base['form.credentials.api_key'] = ['nullable', 'string', 'max:2000'];
        }

        if (in_array($this->selectedProvedor, [ProvedorIntegracaoEnum::MetaWhatsApp->value, ProvedorIntegracaoEnum::GenericWhatsApp->value], true)) {
            $base['form.config.default_language'] = ['nullable', 'string', 'max:20'];
            $base['form.config.templates'] = ['nullable', 'string', 'max:2000'];
            $base['form.credentials.access_token'] = ['nullable', 'string', 'max:2000'];
            $base['form.credentials.phone_number_id'] = ['nullable', 'string', 'max:255'];
            $base['form.credentials.business_account_id'] = ['nullable', 'string', 'max:255'];
            $base['form.credentials.webhook_verify_token'] = ['nullable', 'string', 'max:255'];
        }

        return $base;
    }

    private function defaultConfig(string $tipo, string $provedor, array $existing): array
    {
        return array_merge([
            'api_url' => '',
            'ambiente' => 'teste',
            'timeout' => 15,
            'retry_attempts' => 1,
            'retry_sleep' => 250,
            'empresa_fiscal_id' => '',
            'nif' => $this->empresa?->NIF ?? '',
            'idempotency_strategy' => 'empresa_tipo_origem_id',
            'default_language' => 'pt_AO',
            'templates' => '',
        ], $existing);
    }

    private function findIntegration(string $tipo, string $provedor): ?EmpresaIntegracao
    {
        if (! $this->schemaReady) {
            return null;
        }

        return EmpresaIntegracao::query()
            ->where('empresa_id', $this->empresa->id)
            ->where('tipo', $tipo)
            ->where('provedor', $provedor)
            ->first();
    }

    private function integrationForEmpresa(int $integrationId): EmpresaIntegracao
    {
        return EmpresaIntegracao::query()
            ->where('empresa_id', $this->empresa->id)
            ->findOrFail($integrationId);
    }

    private function ensureSchemaReady(): void
    {
        abort_unless($this->schemaReady, 503, 'Execute a migration empresa_integracoes antes de gerir integrações.');
    }

    private function authorizeManageIntegrations(): void
    {
        $activeEmpresa = Auth::user()?->empresaAtiva();

        abort_unless($activeEmpresa && $this->empresa && (int) $activeEmpresa->id === (int) $this->empresa->id, 403);
        Gate::forUser(Auth::user())->authorize('manageIntegrations', $activeEmpresa);

        $this->empresa = $activeEmpresa->refresh();
    }

    public function render()
    {
        return view('livewire.empresa.empresa-integracoes', [
            'cards' => $this->cards(),
            'integrations' => $this->integrations(),
        ]);
    }
}
