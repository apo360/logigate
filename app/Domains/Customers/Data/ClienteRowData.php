<?php

namespace App\Domains\Customers\Data;

class ClienteRowData
{
    public int $id;

    public string $name;

    public ?string $email;

    public ?string $taxId;

    public ?string $phone;

    public int $processosTotal;

    public int $processosAtivos;

    public int $processosFinalizados;

    public int $licenciamentoTotal;

    public float $saldoContaCorrente;

    public string $status;

    public string $tipoCliente;

    public bool $isActive;

    public function __construct($customer)
    {
        $this->id = (int) $customer->id;
        $this->name = (string) ($customer->CompanyName ?? 'Cliente sem nome');
        $this->email = $customer->Email;
        $this->taxId = $customer->CustomerTaxID;
        $this->phone = $customer->Telephone;

        $this->processosTotal = (int) (
            $customer->processos_total_count
            ?? $customer->processos_count
            ?? 0
        );

        $this->processosAtivos = (int) (
            $customer->processos_ativos_count
            ?? 0
        );

        $this->processosFinalizados = (int) (
            $customer->processos_finalizados_count
            ?? 0
        );

        $this->licenciamentoTotal = (int) (
            $customer->licenciamentos_total_count
            ?? $customer->licenciamento_count
            ?? 0
        );

        $this->saldoContaCorrente = (float) ($customer->saldo_conta_corrente ?? 0);

        $this->isActive = (bool) $customer->is_active;
        $this->status = $this->isActive ? 'Activo' : 'Inactivo';

        $this->tipoCliente = $customer->tipo_cliente
            ? ucfirst((string) $customer->tipo_cliente)
            : ($customer->CustomerType ?? '—');
    }

    public function initials(): string
    {
        $name = trim($this->name);

        if ($name === '') {
            return 'CL';
        }

        return strtoupper(mb_substr($name, 0, 2));
    }

    public function saldoFormatado(): string
    {
        return number_format($this->saldoContaCorrente, 2, ',', '.') . ' Kz';
    }

    public function saldoColor(): string
    {
        return $this->saldoContaCorrente >= 0
            ? 'text-green-600'
            : 'text-red-600';
    }
}