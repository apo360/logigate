<?php

namespace App\Http\Requests;

use App\Application\Processo\Services\ProcessoTenantAccessService;
use App\Application\Processo\Support\ProcessoFormSupport;
use App\Models\Processo;
use Illuminate\Foundation\Http\FormRequest;

class ProcessoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $processo = $this->processoFromRoute();

        if ($processo instanceof Processo) {
            return $this->user()?->can('update', $processo) ?? false;
        }

        return $this->user()?->can('create', Processo::class) ?? false;
    }

    /**
     * Regras de validação para a solicitação.
     */
    public function rules(): array
    {
        return app(ProcessoFormSupport::class)->rules(
            empresaId: $this->empresaId(),
            processoId: $this->processoId()
        );
    }

    /**
     * Mensagens de erro personalizadas para validação.
     */
    public function messages(): array
    {
        return app(ProcessoFormSupport::class)->messages();
    }

    public function attributes(): array
    {
        return app(ProcessoFormSupport::class)->attributes();
    }

    /**
     * Filtra e sanitiza os dados antes da validação.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'Estado' => $this->Estado ?? 'Aberto',
            'DataAbertura' => $this->DataAbertura ?? now()->toDateString(),
            'Cambio' => $this->normalizeDecimal($this->input('Cambio')),
            'ValorAduaneiro' => $this->normalizeDecimal($this->input('ValorAduaneiro')),
            'fob_total' => $this->normalizeDecimal($this->input('fob_total')),
            'frete' => $this->normalizeDecimal($this->input('frete')),
            'seguro' => $this->normalizeDecimal($this->input('seguro')),
            'cif' => $this->normalizeDecimal($this->input('cif')),
            'peso_bruto' => $this->normalizeDecimal($this->input('peso_bruto')),
        ]);
    }

    private function empresaId(): int
    {
        $user = $this->user();
        abort_if(! $user, 403, 'Utilizador autenticado não encontrado.');

        $empresaId = app(ProcessoTenantAccessService::class)->empresaIdFor($user);

        abort_if(! $empresaId, 403, 'Empresa activa não encontrada.');

        return (int) $empresaId;
    }

    private function processoId(): ?int
    {
        return $this->processoFromRoute()?->id;
    }

    private function processoFromRoute(): ?Processo
    {
        $processo = $this->route('processo');

        if ($processo instanceof Processo) {
            return $processo;
        }

        if (is_numeric($processo)) {
            return Processo::query()->find((int) $processo);
        }

        return null;
    }

    private function normalizeDecimal(mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return $value;
        }

        $value = str_replace(' ', '', (string) $value);
        $value = str_replace('.', '', $value);
        $value = str_replace(',', '.', $value);

        return is_numeric($value) ? $value : null;
    }
}
