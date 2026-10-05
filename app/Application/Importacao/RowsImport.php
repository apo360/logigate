<?php
namespace App\Application\Importacao;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

abstract class RowsImport implements ToCollection, WithHeadingRow
{
    public readonly BatchResult $result;
    private int $line = 1;
    public function __construct(protected readonly Empresa $empresa, protected readonly User $actor)
    {
        $this->result = new BatchResult();
    }
    abstract protected function create(array $data): int;
    abstract protected function fields(): array;
    public function collection(Collection $rows): void
    {
        if ($rows->count() > 5000) { throw new InvalidArgumentException('O lote deve conter no máximo 5000 linhas.'); }
        foreach ($rows as $row) {
            ++$this->line;
            if (collect($row)->every(fn ($value) => $value === null || trim((string) $value) === '')) { continue; }
            $data = [];
            $known = [];
            foreach ($this->fields() as $field) {
                $slug = \Illuminate\Support\Str::slug($field, '_');
                foreach (array_unique([$slug, strtolower($field)]) as $heading) {
                    $known[$heading] = true;
                    if (isset($row[$heading])) {
                        $data[$field] = is_string($row[$heading]) ? trim($row[$heading]) : $row[$heading];
                    }
                }
            }
            $unknown = collect($row)->reject(fn ($value, $key) => isset($known[$key]) || $value === null || $value === '')->keys()->all();
            if ($unknown !== []) {
                $this->result->rejected($this->line, ['columns' => ['Colunas não suportadas: ' . implode(', ', $unknown)]]);
                continue;
            }
            try {
                $id = DB::transaction(fn () => $this->create($data));
                $this->result->accepted($this->line, $id);
            } catch (ValidationException $error) {
                $this->result->rejected($this->line, $error->errors());
            } catch (InvalidArgumentException $error) {
                $this->result->rejected($this->line, ['row' => [$error->getMessage()]]);
            }
        }
    }
}
