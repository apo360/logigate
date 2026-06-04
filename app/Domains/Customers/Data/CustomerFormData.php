<?php

namespace App\Domains\Customers\Data;

final class CustomerFormData
{
    public function __construct(
        public readonly string $customerTaxId,
        public readonly string $customerType,
        public readonly string $companyName,
        public readonly ?string $email,
        public readonly ?string $telephone,
        public readonly ?string $fax,
        public readonly ?string $website,
        public readonly int $selfBillingIndicator,
        public readonly ?string $metodoPagamento,
        public readonly string $tipoCliente,
        public readonly bool $isActive,
        public readonly ?string $observacoes,
        public readonly ?string $moedaOperacao,
        public readonly ?string $frequencia,
        public readonly ?string $addressDetail,
        public readonly string $addressType,
        public readonly ?string $city,
        public readonly ?string $country,
        public readonly ?string $postalCode,
        public readonly ?string $province,
        public readonly ?string $buildingNumber = null,
        public readonly ?string $streetName = null,
        public readonly ?string $accountId = null,
        public readonly ?string $codigoCliente = null,
        public readonly ?string $status = null,
        public readonly ?int $nacionality = null,
        public readonly ?string $docType = null,
        public readonly ?string $docNum = null,
        public readonly ?string $validadeDateDoc = null,
    ) {
    }

    public static function fromArray(array $form): self
    {
        $status = (string) ($form['Status'] ?? ($form['status'] ?? 'Ativo'));
        $isActive = array_key_exists('is_active', $form)
            ? (bool) $form['is_active']
            : in_array(strtolower($status), ['ativo', 'activa', 'active'], true);

        return new self(
            customerTaxId: preg_replace('/[^0-9]/', '', (string) ($form['CustomerTaxID'] ?? '')),
            customerType: (string) ($form['CustomerType'] ?? ''),
            companyName: trim((string) ($form['CompanyName'] ?? '')),
            email: self::nullableString($form['Email'] ?? null),
            telephone: self::nullableString($form['Telephone'] ?? null),
            fax: self::nullableString($form['Fax'] ?? null),
            website: self::nullableString($form['Website'] ?? null),
            selfBillingIndicator: (int) ($form['SelfBillingIndicator'] ?? 0),
            metodoPagamento: self::nullableString($form['metodo_pagamento'] ?? null),
            tipoCliente: strtolower((string) ($form['TipoCliente'] ?? ($form['tipo_cliente'] ?? 'importador'))),
            isActive: $isActive,
            observacoes: self::nullableString($form['Notes'] ?? ($form['observacoes'] ?? null)),
            moedaOperacao: self::nullableString($form['moeda_operacao'] ?? 'AOA'),
            frequencia: self::nullableString($form['frequencia'] ?? null),
            addressDetail: self::nullableString($form['Address'] ?? ($form['AddressDetail'] ?? null)),
            addressType: (string) ($form['AddressType'] ?? 'Facturamento'),
            city: self::nullableString($form['City'] ?? null),
            country: self::nullableString($form['Country'] ?? 'Angola'),
            postalCode: self::nullableString($form['PostalCode'] ?? null),
            province: self::nullableString($form['Province'] ?? null),
            buildingNumber: self::nullableString($form['BuildingNumber'] ?? null),
            streetName: self::nullableString($form['StreetName'] ?? null),
            accountId: self::nullableString($form['AccountID'] ?? null),
            codigoCliente: self::nullableString($form['codigo_cliente'] ?? null),
            status: self::nullableString($form['status'] ?? ($form['Status'] ?? null)),
            nacionality: isset($form['nacionality']) && $form['nacionality'] !== '' ? (int) $form['nacionality'] : null,
            docType: self::nullableString($form['doc_type'] ?? null),
            docNum: self::nullableString($form['doc_num'] ?? null),
            validadeDateDoc: self::nullableString($form['validade_date_doc'] ?? null),
        );
    }

    public function toArray(): array
    {
        return [
            'CustomerTaxID' => $this->customerTaxId,
            'CustomerType' => $this->customerType,
            'CompanyName' => $this->companyName,
            'Email' => $this->email,
            'Telephone' => $this->telephone,
            'Fax' => $this->fax,
            'Website' => $this->website,
            'SelfBillingIndicator' => $this->selfBillingIndicator,
            'metodo_pagamento' => $this->metodoPagamento,
            'tipo_cliente' => $this->tipoCliente,
            'is_active' => $this->isActive,
            'observacoes' => $this->observacoes,
            'moeda_operacao' => $this->moedaOperacao,
            'frequencia' => $this->frequencia,
            'AddressDetail' => $this->addressDetail,
            'AddressType' => $this->addressType,
            'City' => $this->city,
            'Country' => $this->country,
            'PostalCode' => $this->postalCode,
            'Province' => $this->province,
            'BuildingNumber' => $this->buildingNumber,
            'StreetName' => $this->streetName,
            'AccountID' => $this->accountId,
            'codigo_cliente' => $this->codigoCliente,
            'status' => $this->status,
            'nacionality' => $this->nacionality,
            'doc_type' => $this->docType,
            'doc_num' => $this->docNum,
            'validade_date_doc' => $this->validadeDateDoc,
        ];
    }

    private static function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
