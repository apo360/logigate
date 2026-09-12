<?php

namespace App\Livewire\Integracoes;

use App\Application\Integracoes\Actions\ActivarIntegracaoAction;
use App\Application\Integracoes\Actions\ActualizarCredenciaisIntegracaoAction;
use App\Application\Integracoes\Actions\DesactivarIntegracaoAction;
use App\Application\Integracoes\DTOs\IntegracaoConfigDTO;
use App\Domains\FacturacaoIntegracao\Clients\HongayetuFacturacaoClientInterface;
use App\Domains\Integracoes\Enums\EstadoIntegracaoEnum;
use App\Domains\Integracoes\Enums\ProvedorIntegracaoEnum;
use App\Domains\Integracoes\Enums\TipoIntegracaoEnum;
use App\Models\Empresa;
use App\Models\EmpresaIntegracao;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class FacturacaoHongayetu extends Component
{
    public ?Empresa $empresa = null;

    public ?EmpresaIntegracao $integracao = null;

    public bool $schemaReady = true;

    public array $form = [
        'config' => [],
    ];

    public bool $internalApiUrlConfigured = false;

    public bool $internalTokenConfigured = false;

    public bool $internalApiKeyConfigured = false;

    public ?string $internalEnvironment = null;

    public ?int $internalTimeout = null;

    public ?int $internalRetry = null;

    public function mount(): void
    {
        $this->empresa = Auth::user()?->empresaAtiva();
        abort_unless($this->empresa, 403);
        Gate::forUser(Auth::user())->authorize('manageIntegrations', $this->empresa);

        $this->schemaReady = Schema::hasTable('empresa_integracoes');
        $this->loadIntegration();
        $this->loadInternalConfigurationStatus();
    }

    public function save(ActualizarCredenciaisIntegracaoAction $action): void
    {
        $this->authorizeManageIntegrations();
        $this->ensureSchemaReady();

        $validated = $this->validate($this->rules());
        $estado = $this->integracao?->estado ?? EstadoIntegracaoEnum::EmConfiguracao;

        $this->integracao = $action->execute(Auth::user(), $this->empresa, new IntegracaoConfigDTO(
            tipo: TipoIntegracaoEnum::Facturacao,
            provedor: ProvedorIntegracaoEnum::HongayetuFacturacao,
            estado: $estado,
            config: $validated['form']['config'],
            credentials: [],
        ));

        $this->loadIntegration();
        $this->loadInternalConfigurationStatus();

        $this->dispatch('toast', type: 'success', message: 'Preferências operacionais guardadas.');
    }

    public function testCredentials(HongayetuFacturacaoClientInterface $client): void
    {
        $this->authorizeManageIntegrations();
        $this->ensureSchemaReady();
        $this->loadIntegration();
        $this->loadInternalConfigurationStatus();

        if (! $this->internalApiUrlConfigured) {
            $message = 'API URL interna da Facturação Hongayetu não configurada. Contacte o suporte técnico.';
            $this->ensureIntegration()->markTestResult(false, $message);
            $this->addError('test', $message);
            $this->loadIntegration();
            return;
        }

        if (! $this->internalTokenConfigured) {
            $message = 'Token interno da Facturação Hongayetu não configurado. Contacte o suporte técnico.';
            $this->ensureIntegration()->markTestResult(false, $message);
            $this->addError('test', $message);
            $this->loadIntegration();
            return;
        }

        $this->integracao = $this->ensureIntegration();

        try {
            $result = $client->verificarCredenciais([], []);
            $message = $this->successMessage($result);
            $config = array_merge($this->integracao->config ?? [], $this->remoteConfigFromResult($result));

            $this->integracao->forceFill(['config' => $config])->save();
            $this->integracao->markTestResult(true, $message);

            $this->dispatch('toast', type: 'success', message: $message);
        } catch (\Throwable $exception) {
            $message = $this->sanitizeMessage($exception->getMessage());
            $this->integracao->markTestResult(false, $message);
            $this->dispatch('toast', type: 'error', message: $message);
        }

        $this->loadIntegration();
    }

    public function activate(ActivarIntegracaoAction $action): void
    {
        $this->authorizeManageIntegrations();
        $this->ensureSchemaReady();
        $this->loadIntegration();

        if (! $this->integracao) {
            $this->addError('activation', 'Teste a configuração interna antes de activar a integração.');
            return;
        }

        if ($this->integracao->ultimo_teste_status !== 'sucesso') {
            $this->addError('activation', 'A integração só pode ser activada depois de um teste de credenciais com sucesso.');
            return;
        }

        $localNif = $this->normalizeNif((string) ($this->empresa->NIF ?? ''));
        $remoteNif = $this->normalizeNif((string) ($this->integracao->config['remote_empresa_nif'] ?? ''));

        if ($localNif !== '' && $remoteNif !== '' && $localNif !== $remoteNif) {
            $this->addError('activation', 'O NIF fiscal remoto não corresponde ao NIF da empresa local.');
            return;
        }

        $this->integracao = $action->execute(Auth::user(), $this->empresa, $this->integracao);
        $this->loadIntegration();

        $this->dispatch('toast', type: 'success', message: 'Integração de Facturação Hongayetu activada.');
    }

    public function deactivate(DesactivarIntegracaoAction $action): void
    {
        $this->authorizeManageIntegrations();
        $this->ensureSchemaReady();
        $this->loadIntegration();

        if (! $this->integracao) {
            return;
        }

        $this->integracao = $action->execute(Auth::user(), $this->empresa, $this->integracao);
        $this->loadIntegration();

        $this->dispatch('toast', type: 'success', message: 'Integração de Facturação Hongayetu desactivada.');
    }

    public function isActive(): bool
    {
        return $this->integracao?->estado === EstadoIntegracaoEnum::Activo;
    }

    public function render()
    {
        return view('livewire.integracoes.facturacao-hongayetu', [
            'isActive' => $this->isActive(),
        ]);
    }

    private function loadIntegration(): void
    {
        if (! $this->schemaReady || ! $this->empresa) {
            return;
        }

        $this->integracao = EmpresaIntegracao::query()
            ->where('empresa_id', $this->empresa->id)
            ->where('tipo', TipoIntegracaoEnum::Facturacao->value)
            ->where('provedor', ProvedorIntegracaoEnum::HongayetuFacturacao->value)
            ->first();

        $config = $this->integracao?->config ?? [];
        $this->form = [
            'config' => array_merge($this->defaultConfig(), $config),
        ];
    }

    private function rules(): array
    {
        return [
            'form.config.empresa_fiscal_id' => ['nullable', 'string', 'max:100'],
            'form.config.nif' => ['nullable', 'string', 'max:50'],
            'form.config.estabelecimento_id_padrao' => ['nullable', 'string', 'max:100'],
            'form.config.observacoes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    private function defaultConfig(): array
    {
        return [
            'empresa_fiscal_id' => '',
            'nif' => $this->empresa?->NIF ?? '',
            'estabelecimento_id_padrao' => '',
            'observacoes' => '',
        ];
    }

    private function loadInternalConfigurationStatus(): void
    {
        $this->internalApiUrlConfigured = filled(config('hongayetu_facturacao.api_url'));
        $this->internalTokenConfigured = filled(config('hongayetu_facturacao.api_token'));
        $this->internalApiKeyConfigured = filled(config('hongayetu_facturacao.api_key'));
        $this->internalEnvironment = config('hongayetu_facturacao.environment');
        $this->internalTimeout = (int) config('hongayetu_facturacao.timeout', 30);
        $this->internalRetry = (int) config('hongayetu_facturacao.retry', 1);
    }

    private function ensureIntegration(): EmpresaIntegracao
    {
        if ($this->integracao) {
            return $this->integracao;
        }

        $this->integracao = EmpresaIntegracao::query()->create([
            'empresa_id' => $this->empresa->id,
            'tipo' => TipoIntegracaoEnum::Facturacao,
            'provedor' => ProvedorIntegracaoEnum::HongayetuFacturacao,
            'estado' => EstadoIntegracaoEnum::EmConfiguracao,
            'config' => $this->form['config'] ?? $this->defaultConfig(),
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        return $this->integracao;
    }

    private function remoteConfigFromResult(array $result): array
    {
        $data = is_array($result['data'] ?? null) ? $result['data'] : $result;
        $remoteEmpresa = $data['empresa'] ?? $data['empresa_fiscal'] ?? $data['company'] ?? [];
        $remoteEmpresa = is_array($remoteEmpresa) ? $remoteEmpresa : [];
        $permissions = $data['permissions'] ?? $data['permissoes'] ?? $data['token_permissions'] ?? null;

        return array_filter([
            'remote_empresa_id' => $remoteEmpresa['id'] ?? $remoteEmpresa['empresa_id'] ?? null,
            'remote_empresa_nome' => $remoteEmpresa['nome'] ?? $remoteEmpresa['name'] ?? $remoteEmpresa['empresa'] ?? null,
            'remote_empresa_nif' => $remoteEmpresa['nif'] ?? $remoteEmpresa['NIF'] ?? $remoteEmpresa['tax_id'] ?? null,
            'token_permissions' => is_array($permissions) ? $permissions : null,
        ], fn ($value) => $value !== null && $value !== '');
    }

    private function successMessage(array $result): string
    {
        foreach (['texto', 'message', 'mensagem'] as $key) {
            if (isset($result[$key]) && is_scalar($result[$key])) {
                return (string) $result[$key];
            }
        }

        return 'Credenciais Hongayetu verificadas com sucesso.';
    }

    private function sanitizeMessage(string $message): string
    {
        $secrets = [
            config('hongayetu_facturacao.api_token'),
            config('hongayetu_facturacao.api_key'),
            ...($this->integracao?->credentials() ?? []),
        ];

        foreach ($secrets as $secret) {
            if (is_string($secret) && $secret !== '') {
                $message = str_replace($secret, '***', $message);
            }
        }

        return mb_substr($message, 0, 1000);
    }

    private function normalizeNif(string $nif): string
    {
        return preg_replace('/\D+/', '', $nif) ?? '';
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
}
