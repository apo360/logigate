<?php
namespace App\Imports;

use App\Application\Importacao\RowsImport;
use App\Application\Licenciamento\Actions\CriarLicenciamentoAction;
use App\Application\Licenciamento\DTOs\CriarLicenciamentoDTO;
use App\Application\Licenciamento\Support\LicenciamentoFormSupport;
use Illuminate\Support\Facades\Validator;

class LicenciamentosImport extends RowsImport
{
    protected function fields(): array
    {
        return array_values(array_diff(array_keys(app(LicenciamentoFormSupport::class)->rules($this->empresa->id)), ['Nr_factura', 'status_fatura']));
    }
    protected function create(array $data): int
    {
        $data['adicoes'] = 0; // The spreadsheet creates a header, without merchandise rows.
        $support = app(LicenciamentoFormSupport::class);
        $rules = array_intersect_key($support->rules($this->empresa->id), array_flip($this->fields()));
        foreach (\Illuminate\Support\Facades\Schema::getColumns('licenciamentos') as $column) {
            if ($column['name'] !== 'codigo_licenciamento' && isset($rules[$column['name']]) && ! $column['nullable']) {
                $rules[$column['name']] = array_values(array_filter($rules[$column['name']], fn ($rule) => $rule !== 'nullable'));
                $rules[$column['name']][] = 'required';
            }
        }
        $validated = Validator::make($data, $rules)->validate();
        app(\App\Application\Importacao\ImportReferences::class)->validate($this->empresa, $data['cliente_id'] ?? null, $data['exportador_id'] ?? null);
        return app(CriarLicenciamentoAction::class)->execute(new CriarLicenciamentoDTO($validated + ['empresa_id' => $this->empresa->id, 'user_id' => $this->actor->id]))->id;
    }
}
