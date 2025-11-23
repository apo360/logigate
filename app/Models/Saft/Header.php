<?php

namespace App\Models\Saft;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class Header
{
    protected $empresa;

    public string $auditFileVersion = '1.01_01';
    public string $taxAccountingBasis;
    public string $currencyCode = 'AOA';

    // Campos opcionais
    public string $fiscalYear;
    public string $startDate;
    public string $endDate;
    public string $dateCreated;

    public function __construct(array $data = [])
    {
        // Obtém empresa autenticada
        $this->empresa = Auth::user()->empresas->first();

        // Preenche campos do input
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {

                // Aceita Carbon nas datas
                if ($value instanceof Carbon) {
                    $this->$key = $value->format('Y-m-d');
                } else {
                    $this->$key = $value;
                }
            }
        }

        // Gera DateCreated se não fornecida
        // $this->dateCreated = $this->dateCreated ?? now()->format('Y-m-d\TH:i:s');
        $this->dateCreated = $this->dateCreated ?? now()->format('Y-m-d');
    }
    /**
     * Converte o header para XML
     * @param \SimpleXMLElement $xml
     * @return \SimpleXMLElement
     * 
     */

    public function buildHeader(\SimpleXMLElement $xml)
    {
        $empresa = $this->empresa;

        $header = $xml->addChild('Header');
        $header->addChild('AuditFileVersion', $this->auditFileVersion);
        $header->addChild('CompanyID', $empresa->NIF ?? '000000000');
        $header->addChild('TaxRegistrationNumber', $empresa->NIF ?? '000000000');
        $header->addChild('TaxAccountingBasis', $this->taxAccountingBasis);

        $header->addChild('CompanyName', $empresa->Empresa ?? 'SEM_NOME');
        $header->addChild('BusinessName', $empresa->Empresa ?? 'SEM_NOME');

        // Endereço
        $companyAddress = $header->addChild('CompanyAddress');
        $companyAddress->addChild('StreetName', $empresa->Endereco_completo ?? '');
        $companyAddress->addChild('AddressDetail', $empresa->Endereco_completo ?? '');
        $companyAddress->addChild('City', $empresa->Cidade ?? '');
        $companyAddress->addChild('Country', 'AO');

        // Datas
        $header->addChild('FiscalYear', $this->fiscalYear);
        $header->addChild('StartDate', $this->startDate);
        $header->addChild('EndDate', $this->endDate);

        $header->addChild('CurrencyCode', $this->currencyCode);
        $header->addChild('DateCreated', $this->dateCreated);

        // Dados do software (para validação AGT)
        $header->addChild('TaxEntity', 'Global');
        $header->addChild('ProductCompanyTaxID', '5417473677');
        $header->addChild('SoftwareValidationNumber', '490/AGT/2024');
        $header->addChild('ProductID', 'Logigate 1.0/HONGAYETU-PRESTACAO DE SERVICOS, LDA.');
        $header->addChild('ProductVersion', '1.0');

        // Contactos opcionais
        $header->addChild('Telephone', $empresa->Telefone ?? '');
        $header->addChild('Email', $empresa->Email ?? '');
        $header->addChild('Website', $empresa->Website ?? '');

        return $header;
    }
}
