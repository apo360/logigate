<?php

namespace App\Models\Saft\MasterFiles;

use Illuminate\Database\Eloquent\Model;
use App\Models\Produto;

class Produtos_Servicos extends Model
{
    public function ProdutosServicosbuild(\SimpleXMLElement $xml, Produto $product): \SimpleXMLElement
    {
        $productXML = $xml->addChild('Product');

        // CAMPOS PRINCIPAIS
        $productXML->addChild('ProductType', $product->ProductType ?? 'S');
        $productXML->addChild('ProductCode', $product->ProductCode ?? '000000');
        $productXML->addChild('ProductGroup', $product->ProductGroup ?? 'Geral');
        $productXML->addChild('ProductDescription', htmlspecialchars($product->ProductDescription) ?? 'Desconhecido');
        $productXML->addChild('ProductNumberCode', $product->ProductNumberCode ?? '9999999999999');
        /*$productXML->addChild('UnitOfMeasure', $product->UnitOfMeasure ?? 'Unidade');
        $productXML->addChild('UnitPrice', number_format($product->UnitPrice ?? 0, 2, '.', ''));
        $productXML->addChild('TaxType', $product->TaxType ?? 'IVA');
        $productXML->addChild('TaxCountryRegion', $product->TaxCountryRegion ?? 'AO');
        $productXML->addChild('TaxCode', $product->TaxCode ?? 'NOR');
        $productXML->addChild('TaxPercentage', number_format($product->TaxPercentage ?? 0, 2, '.', ''));*/

        return $productXML;
    }
}
