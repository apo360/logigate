<?php

namespace App\Http\Requests;

use App\Application\Licenciamento\Services\LicenciamentoTenantAccessService;
use App\Application\Licenciamento\Support\LicenciamentoFormSupport;
use App\Models\Licenciamento;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class LicenciamentoRequest extends FormRequest
{
   public function authorize(): bool
    {
        $licenciamento = $this->licenciamentoFromRoute();

        if ($licenciamento instanceof Licenciamento) {
            return $this->user()?->can('update', $licenciamento) ?? false;
        }

        return $this->user()?->can('create', Licenciamento::class) ?? false;
    }

    public function rules(): array
    {
        return app(LicenciamentoFormSupport::class)->rules(
            empresaId: $this->empresaId(),
            licenciamentoId: $this->licenciamentoId()
        );
    }

    public function messages(): array
    {
        return app(LicenciamentoFormSupport::class)->messages();
    }

    public function attributes(): array
    {
        return app(LicenciamentoFormSupport::class)->attributes();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'empresa_id' => $this->empresaId(),
            'peso_bruto' => $this->normalizeDecimal($this->input('peso_bruto')),
            'fob_total' => $this->normalizeDecimal($this->input('fob_total')),
            'frete' => $this->normalizeDecimal($this->input('frete')),
            'seguro' => $this->normalizeDecimal($this->input('seguro')),
            'cif' => $this->normalizeDecimal($this->input('cif')),
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $this->validateCifConsistency($validator);
        });
    }

    private function validateCifConsistency($validator): void
    {
        $fob = (float) ($this->input('fob_total') ?? 0);
        $frete = (float) ($this->input('frete') ?? 0);
        $seguro = (float) ($this->input('seguro') ?? 0);
        $cif = (float) ($this->input('cif') ?? 0);

        if ($fob <= 0 && $frete <= 0 && $seguro <= 0 && $cif <= 0) {
            return;
        }

        $expectedCif = app(LicenciamentoFormSupport::class)
            ->calculatedValues($fob, $frete, $seguro)['cif'];

        if (round($cif, 2) !== round($expectedCif, 2)) {
            $validator->errors()->add(
                'cif',
                'O CIF deve ser igual à soma do FOB, Frete e Seguro.'
            );
        }
    }

    private function empresaId(): int
    {
        $empresaId = app(LicenciamentoTenantAccessService::class)
            ->empresaIdFor($this->user());

        abort_if(! $empresaId, 403, 'Empresa activa não encontrada.');

        return (int) $empresaId;
    }

    private function licenciamentoId(): ?int
    {
        return $this->licenciamentoFromRoute()?->id;
    }

    private function licenciamentoFromRoute(): ?Licenciamento
    {
        $licenciamento = $this->route('licenciamento');

        if ($licenciamento instanceof Licenciamento) {
            return $licenciamento;
        }

        if (is_numeric($licenciamento)) {
            return Licenciamento::query()->find((int) $licenciamento);
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