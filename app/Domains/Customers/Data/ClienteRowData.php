<?php

namespace App\Domains\Customers\Data;
use App\Models\Customer;

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

    public float $saldoContaCorrente;

    public string $status;
    public string $tipoCliente;

    public function __construct($customer)
    {
        $this->id = $customer->id;
        $this->name = $customer->CompanyName;
        $this->email = $customer->Email;
        $this->taxId = $customer->CustomerTaxID;
        $this->phone = $customer->Telephone;

        $this->processosTotal = $customer->processos_count ?? 0;
        $this->processosAtivos = $customer->processos_ativos_count ?? 0;
        $this->processosFinalizados = $customer->processos_finalizados_count ?? 0;

        $this->saldoContaCorrente = $customer->saldo_conta_corrente ?? 0;

        $this->status = $customer->Status ?? 'Inativo';
        $this->tipoCliente = $customer->CustomerType ?? '—';
    }

    public function initials(): string
    {
        return strtoupper(substr($this->name, 0, 2));
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