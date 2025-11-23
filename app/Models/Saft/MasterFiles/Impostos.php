<?php

namespace App\Models\Saft\MasterFiles;

use Illuminate\Database\Eloquent\Model;
use App\Models\TaxTable;

class Impostos extends Model
{
    public function Impostosbuild(\SimpleXMLElement $xml, TaxTable $tax): \SimpleXMLElement
    {
        $taxXML = $xml->addChild('TaxTableEntry');

        // CAMPOS PRINCIPAIS
        $taxXML->addChild('TaxType', $tax->TaxType ?? 'IVA');
        $taxXML->addChild('TaxCountryRegion', $tax->TaxCountryRegion ?? 'AO');
        $taxXML->addChild('TaxCode', $tax->TaxCode ?? 'NOR');
        $taxXML->addChild('Description', htmlspecialchars($tax->Description) ?? 'Desconhecido');
        $taxXML->addChild('TaxPercentage', number_format($tax->TaxPercentage ?? 0, 2, '.', ''));

        return $taxXML;
    }
}
