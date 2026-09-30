<?php

declare(strict_types=1);

namespace App\Livewire\Processo;

use App\Application\Processo\Actions\ImportarDeclaracaoAsycudaAction;
use App\Application\Processo\Actions\PrepararImportacaoAsycudaAction;
use App\Application\Processo\DTOs\AsycudaImportResolvedData;
use App\Infrastructure\Integrations\Asycuda\AsycudaJsonParser;
use App\Infrastructure\Integrations\Asycuda\AsycudaProcessoMapper;
use App\Domains\Processo\Enums\FormaPagamentoEnum;
use App\Enums\MoedaEnum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

final class ImportarDeclaracaoAsycuda extends Component
{
    use WithFileUploads;

    public $upload;
    public array $preview = [];
    public array $selections = [];
    public array $parseErrors = [];
    public ?string $errorMessage = null;
    public ?int $createdProcessId = null;
    public array $paymentOptions = [];
    public array $currencyOptions = [];

    public function mount(): void
    {
        $this->paymentOptions = array_map(fn (FormaPagamentoEnum $case) => $case->label(), FormaPagamentoEnum::cases());
        $this->currencyOptions = array_map(fn (MoedaEnum $case) => $case->label(), MoedaEnum::cases());
    }

    public function updatedSelections(): void
    {
        $this->refreshResolution();
    }

    public function preparePreview(): void
    {
        $this->reset(['preview', 'selections', 'parseErrors', 'errorMessage', 'createdProcessId']);
        $this->validate(['upload' => 'required|file|max:10240']);
        if (strtolower($this->upload->getClientOriginalExtension()) !== 'json') {
            $this->parseErrors = ['Seleccione um ficheiro .json válido.'];
            return;
        }

        try {
            $parsed = app(AsycudaJsonParser::class)->parse($this->upload->get());
            if (! $parsed->success) {
                $this->parseErrors = $parsed->errors;
                return;
            }

            $mapped = app(AsycudaProcessoMapper::class)->mapImport($parsed->data);
            $mappedArray = json_decode(json_encode(get_object_vars($mapped), JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
            $token = Str::random(48);
            Cache::put($this->cacheKey($token), $mappedArray, now()->addMinutes(30));
            $this->selections['_token'] = $token;
            $this->refreshResolution();
        } catch (Throwable $exception) {
            Log::warning('Não foi possível preparar a declaração ASYCUDA.', ['exception' => $exception]);
            $this->parseErrors = ['Não foi possível preparar este ficheiro para preview. Verifique se o conteúdo é JSON válido.'];
        }
    }

    public function import(): void
    {
        $token = $this->selections['_token'] ?? null;
        $mapped = $token ? Cache::get($this->cacheKey($token)) : null;
        if (! is_array($mapped)) {
            $this->errorMessage = 'O preview expirou. Carregue novamente o ficheiro.';
            $this->preview = [];
            return;
        }

        $this->validate([
            'selections.financial.fob_total' => 'nullable|numeric|min:0|max:999999999999.99',
            'selections.financial.frete' => 'nullable|numeric|min:0|max:99999999.99',
            'selections.financial.seguro' => 'nullable|numeric|min:0|max:99999999.99',
            'selections.financial.cif' => 'nullable|numeric|min:0|max:99999999.99',
            'selections.financial.ValorTotal' => 'nullable|numeric|min:0|max:999999999999.99',
            'selections.financial.ValorAduaneiro' => 'nullable|numeric|min:0|max:999999999999.99',
            'selections.financial.Cambio' => 'nullable|numeric|min:0|max:999999999999.99',
            'selections.item_prices.*' => 'required|numeric|min:0|max:999999999999.99',
        ]);

        try {
            $resolved = app(PrepararImportacaoAsycudaAction::class)->execute($mapped, $this->selections);
            $this->preview = $this->previewArray($resolved);
            if (! $resolved->canImport()) {
                $this->errorMessage = 'Resolva os campos obrigatórios antes de importar.';
                return;
            }

            $processo = app(ImportarDeclaracaoAsycudaAction::class)->execute(new AsycudaImportResolvedData($resolved->mapped, $resolved->resolved));
            Cache::forget($this->cacheKey($token));
            $this->createdProcessId = (int) $processo->id;
            $this->dispatch('toast', type: 'success', message: 'Declaração importada com sucesso.');
        } catch (Throwable $exception) {
            Log::error('Falha transaccional na importação da declaração ASYCUDA.', ['exception' => $exception]);
            $this->errorMessage = 'Não foi possível importar a declaração. Reveja as referências e tente novamente.';
        }
    }

    public function openProcesso(): mixed
    {
        abort_unless($this->createdProcessId, 404);
        return redirect()->route('processos.edit', ['processo' => $this->createdProcessId]);
    }

    private function refreshResolution(): void
    {
        $token = $this->selections['_token'] ?? null;
        $mapped = $token ? Cache::get($this->cacheKey($token)) : null;
        if (! is_array($mapped)) return;

        try {
            $resolvedPreview = app(PrepararImportacaoAsycudaAction::class)->execute($mapped, $this->selections);
            $this->preview = $this->previewArray($resolvedPreview);
            foreach (['customer_id', 'exportador_id', 'tipo_processo_id', 'estancia_id', 'tipo_transporte_id', 'codigo_banco', 'forma_pagamento', 'moeda'] as $key) {
                if (! isset($this->selections[$key]) && isset($resolvedPreview->resolved[$key])) $this->selections[$key] = $resolvedPreview->resolved[$key];
            }
            foreach (['origem' => 'Pais_origem', 'destino' => 'Pais_destino', 'nacionalidade_transporte' => 'nacionalidade_transporte'] as $kind => $target) {
                $choiceKey = 'country_' . $kind;
                if (! isset($this->selections[$choiceKey]) && isset($resolvedPreview->resolved[$target])) $this->selections[$choiceKey] = $resolvedPreview->resolved[$target];
            }
            if (! isset($this->selections['pauta_ids']) && isset($resolvedPreview->resolved['pautas'])) $this->selections['pauta_ids'] = $resolvedPreview->resolved['pautas'];
            $this->errorMessage = null;
        } catch (Throwable $exception) {
            Log::warning('Falha na resolução tenant-aware da declaração.', ['exception' => $exception]);
            $this->errorMessage = 'Não foi possível resolver as referências para a empresa activa.';
        }
    }

    private function previewArray($preview): array
    {
        return [
            'mapped' => $preview->mapped,
            'resolved' => $preview->resolved,
            'requiresResolution' => $preview->requiresResolution,
            'warnings' => $preview->warnings,
            'references' => $preview->references,
            'unmapped' => $preview->unmapped,
            'blockingErrors' => $preview->blockingErrors,
            'canImport' => $preview->canImport(),
        ];
    }

    private function cacheKey(string $token): string
    {
        $userId = Auth::id() ?? 0;
        return 'asycuda-import-preview:' . $userId . ':' . hash('sha256', $token);
    }

    public function render()
    {
        return view('livewire.processo.importar-declaracao-asycuda');
    }
}
