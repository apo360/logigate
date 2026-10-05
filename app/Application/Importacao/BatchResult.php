<?php
namespace App\Application\Importacao;

final class BatchResult
{
    public array $rows = [];
    public function accepted(int $line, int $id): void { $this->rows[] = ['line' => $line, 'status' => 'accepted', 'id' => $id]; }
    public function rejected(int $line, array $errors): void { $this->rows[] = ['line' => $line, 'status' => 'rejected', 'errors' => $errors]; }
    public function toArray(): array
    {
        $accepted = count(array_filter($this->rows, fn ($row) => $row['status'] === 'accepted'));
        return ['accepted' => $accepted, 'rejected' => count($this->rows) - $accepted, 'rows' => $this->rows];
    }
    public function status(): string
    {
        $totals = $this->toArray();
        return $totals['rejected'] === 0 ? 'completed' : ($totals['accepted'] > 0 ? 'partial' : 'failed');
    }
}
