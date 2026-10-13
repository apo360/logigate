<?php

namespace App\Exports;

use App\Services\Dashboard\OperationalReport;
use Closure;
use Generator;
use Maatwebsite\Excel\Concerns\FromGenerator;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

final class OperationalDashboardExport extends DefaultValueBinder implements FromGenerator, WithCustomValueBinder
{
    public function __construct(private readonly Closure $rows) {}

    public function generator(): Generator
    {
        foreach (($this->rows)() as $row) {
            yield array_map(OperationalReport::safeCell(...), $row);
        }
    }

    public function bindValue(Cell $cell, mixed $value): bool
    {
        if (is_string($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }
}
