<?php

namespace App\Models\Saft\MasterFiles;

use Illuminate\Database\Eloquent\Model;
use App\Models\Customer;

class Cliente extends Model
{
    public function Clientebuild(\SimpleXMLElement $xml, Customer $customer): \SimpleXMLElement
    {
        $customerXML = $xml->addChild('Customer');

        // CAMPOS PRINCIPAIS
        $customerXML->addChild('CustomerID', $customer->CustomerID ?? '000000');
        $customerXML->addChild('AccountID', $customer->AccountID ?? 'Desconhecido');
        $customerXML->addChild('CustomerTaxID', $customer->CustomerTaxID ?? '999999999');
        $customerXML->addChild('CompanyName', htmlspecialchars($customer->CompanyName) ?? 'Desconhecido');
        // ADDRESSES
        $this->buildAddress($customerXML, 'BillingAddress', $customer->endereco);

        if ($customer->shipToAddress !== null) {
            $this->buildAddress($customerXML, 'ShipToAddress', $customer->endereco);
        }

        $customerXML->addChild('SelfBillingIndicator', $customer->SelfBillingIndicator ? 1 : 0);

        return $customerXML;
    }

    private function buildAddress(\SimpleXMLElement $xml, string $tagName, $address)
    {
        $addressNode = $xml->addChild($tagName);

        $addressNode->addChild('AddressDetail', $address->AddressDetail ?? 'Desconhecido');
        $addressNode->addChild('City', $address->City ?? 'Desconhecido');
        $addressNode->addChild('PostalCode', $address->PostalCode ?? 'Desconhecido');
        $addressNode->addChild('Province', $address->Province ?? 'Desconhecido');
        $addressNode->addChild('Country', $address->Country ?? 'AO');
    }
}
