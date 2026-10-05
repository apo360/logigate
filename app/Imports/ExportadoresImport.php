<?php

namespace App\Imports;

use App\Domains\Exportadores\Actions\CreateOrAssociateExportadorAction;
use App\Domains\Exportadores\Data\ExportadorFormData;
use App\Domains\Exportadores\Services\ExportadorValidation;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

final class ExportadoresImport implements ToCollection, WithHeadingRow
{
    public function __construct(private readonly Empresa $empresa, private readonly User $actor) {}

    public function collection(Collection $rows): void
    {
        $action = app(CreateOrAssociateExportadorAction::class);
        foreach ($rows as $index => $row) {
            if (collect($row)->every(fn ($value) => $value === null || trim((string) $value) === '')) {
                continue;
            }
            $data = [];
            foreach ([
                'exportador' => 'Exportador', 'exportador_tax_id' => 'ExportadorTaxID', 'exportadortaxid' => 'ExportadorTaxID',
                'account_id' => 'AccountID', 'accountid' => 'AccountID', 'endereco' => 'Endereco',
                'telefone' => 'Telefone', 'email' => 'Email', 'pais' => 'Pais', 'website' => 'Website',
                'cidade' => 'Cidade', 'codigo_exportador' => 'codigo_exportador',
                'additional_info' => 'additional_info', 'status' => 'status',
            ] as $heading => $field) {
                if (isset($row[$heading])) {
                    $data[$field] = is_string($row[$heading]) ? trim($row[$heading]) : $row[$heading];
                }
            }
            // Excel can expose identifiers as numbers; preserve textual identifiers.
            foreach (['ExportadorTaxID', 'AccountID', 'Telefone', 'codigo_exportador'] as $field) {
                if (isset($data[$field])) { $data[$field] = (string) $data[$field]; }
            }
            try {
                $data = ExportadorFormData::fromArray(ExportadorValidation::validate($data));
                $action->execute($data, $this->empresa, $this->actor);
            } catch (ValidationException $error) {
                throw ValidationException::withMessages([
                    'file' => 'Linha ' . ($index + 2) . ': ' . implode(' ', $error->validator->errors()->all()),
                ]);
            }
        }
    }
}
