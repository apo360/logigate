<?php

namespace App\Models\Saft\SourceDocuments;

use Illuminate\Database\Eloquent\Model;
use App\Models\SalesInvoice;
use App\Models\SalesLine;

class SalesInvoices extends Model
{
    public function SalesInvoicesbuild(\SimpleXMLElement $sourceDocuments, $salesInvoice): \SimpleXMLElement
    {
        // Criar nó SalesInvoices
        $salesInvoicesXML = $sourceDocuments->addChild('SalesInvoices');
        // Adicionar SalesInvoice
        $salesInvoicesXML->addChild('NumberOfEntries', count($salesInvoice));

        $totalDebit = SalesInvoice::sumDebit($salesInvoice);
        $totalCredit = SalesInvoice::sumCredit($salesInvoice);

        $salesInvoicesXML->addChild('TotalDebit', number_format($totalDebit, 2, '.', ''));
        $salesInvoicesXML->addChild('TotalCredit', number_format($totalCredit, 2, '.', ''));

        // Agora adicionar cada <Invoice>
        foreach ($salesInvoice as $invoice) {
            $this->buildInvoice($salesInvoicesXML, $invoice);
        }

        $WithholdingTaxs = $sourceDocuments->addChild('WithholdingTax');
        $WithholdingTaxs->addChild('WithholdingTaxAmount', '0.00');

        return $salesInvoicesXML;
    }

    /**
     * Construit o nó Invoice
     */
    private function buildInvoice(\SimpleXMLElement $salesInvoicesXML, SalesInvoice $invoice)
    {
        $invoiceXML = $salesInvoicesXML->addChild('Invoice');

        $invoiceXML->addChild('InvoiceNo', $invoice->invoice_no);
        $DocumentStatus = $invoiceXML->addChild('DocumentStatus');
        $DocumentStatus->addChild('InvoiceStatus', $invoice->salesstatus->invoice_status ?? 'N');
        $DocumentStatus->addChild('InvoiceStatusDate', SalesInvoice::formatDateTime($invoice->salesstatus->invoice_status_date ?? date('Y-m-d H:i:s')));
        $DocumentStatus->addChild('SourceID', $invoice->source_id); // Verificar se é este campo
        $DocumentStatus->addChild('SourceBilling', 'P');
        $invoiceXML->addChild('Hash', $invoice->hash);
        $invoiceXML->addChild('HashControl', $invoice->hash_control);
        $invoiceXML->addChild('InvoiceDate', SalesInvoice::formatDateTime($invoice->invoice_date ?? date('Y-m-d H:i:s')));
        $invoiceXML->addChild('InvoiceType', $invoice->invoiceType->Code);
        $SpecialRegimes = $invoiceXML->addChild('SpecialRegimes');
        $SpecialRegimes->addChild('SelfBillingIndicator', 0);
        $SpecialRegimes->addChild('CashVATIndicator', 0);
        $SpecialRegimes->addChild('ThirdPartiesBillingIndicator', 0);
        $invoiceXML->addChild('SourceID', $invoice->source_id);
        $invoiceXML->addChild('SystemEntryDate', SalesInvoice::formatDateTime($invoice->system_entry_date));
        $invoiceXML->addChild('CustomerID', $invoice->customer->CustomerID);

        $this->buildAddress($invoiceXML, $invoice);

        $invoiceXML->addChild('MovementStartTime', SalesInvoice::formatDateTime($invoice->movement_start_time));

        // Buscar SalesLines e Adicionar Linhas de Venda
        $salesLines = $invoice->salesitem;
        $this->buildSalesLines($invoiceXML, $salesLines);
        
        // Totais
        $totals = $invoice->salesdoctotal;

        $DocumentTotals = $invoiceXML->addChild('DocumentTotals');
        $DocumentTotals->addChild('TaxPayable', number_format($totals->tax_payable ?? 0, 2, '.', ''));
        $DocumentTotals->addChild('NetTotal', number_format($totals->net_total ?? 0, 2, '.', ''));
        $DocumentTotals->addChild('GrossTotal', number_format($totals->gross_total ?? 0, 2, '.', ''));

        // Se o tipo de factura for FT07 adicionar o payments
        if ($invoice->invoiceType->Code === 'FR') {
            $Payment = $DocumentTotals->addChild('Payment');
            $Payment->addChild('PaymentMechanism', $invoice->salesdoctotal->paymentMechanism->code);
            $Payment->addChild('PaymentAmount', number_format($invoice->salesdoctotal->gross_total, 2, '.', ''));
            $Payment->addChild('PaymentDate', SalesInvoice::formatDateTime($invoice->salesdoctotal->data_pagamento));
        }
        
        $WithholdingTaxs = $invoiceXML->addChild('WithholdingTax');
        $WithholdingTaxs->addChild('WithholdingTaxAmount', '0.00');

        return $invoiceXML;
    }

    // Linhas de Venda
    private function buildSalesLines(\SimpleXMLElement $invoiceXML, $salesLines)
    {
        foreach ($salesLines as $line) {
            $lineXML = $invoiceXML->addChild('Line');

            $lineXML->addChild('LineNumber', $line->line_number);
            $lineXML->addChild('ProductCode', htmlspecialchars($line->produto->ProductCode));
            $lineXML->addChild('ProductDescription', htmlspecialchars($line->produto->ProductDescription));
            $lineXML->addChild('Quantity', number_format($line->quantity, 2, '.', ''));
            $lineXML->addChild('UnitOfMeasure', $line->unit_of_measure);
            $lineXML->addChild('UnitPrice', number_format($line->unit_price, 2, '.', ''));
            $lineXML->addChild('TaxPointDate', $line->tax_point_date);
            $lineXML->addChild('CreditAmount', number_format($line->credit_amount, 2, '.', ''));
            $lineXML->addChild('DebitAmount', number_format($line->debit_amount, 2, '.', ''));
        }
    }

    // Endereços
    private function buildAddress(\SimpleXMLElement $invoiceXML, $invoice)
    {
        $address = $invoice->customer->endereco;
        // ShipTo
        $shipToXML = $invoiceXML->addChild('ShipTo'); // ShipTo ou ShipFrom
        $shipToXML->addChild('DeliveryDate', date('Y-m-d'));
        $addressNodeShipTo = $shipToXML->addChild('Address');
        $addressNodeShipTo->addChild('AddressDetail', htmlspecialchars($address->AddressDetail ?? 'Desconhecido'));
        $addressNodeShipTo->addChild('City', htmlspecialchars($address->City ?? 'Desconhecido'));
        $addressNodeShipTo->addChild('PostalCode', htmlspecialchars($address->PostalCode ?? 'Desconhecido'));
        $addressNodeShipTo->addChild('Country', htmlspecialchars($address->Country ?? 'AO'));

        // ShipFrom
        $shipFromXML = $invoiceXML->addChild('ShipFrom');
        $shipFromXML->addChild('DeliveryDate', date('Y-m-d'));
        $addressNodeShipFrom = $shipFromXML->addChild('Address');
        $addressNodeShipFrom->addChild('AddressDetail', 'Rua Amilcar Cabral, n.º 66, 1.º Dto Ingombotas');
        $addressNodeShipFrom->addChild('City', 'Luanda');
        $addressNodeShipFrom->addChild('PostalCode', 'Desconhecido');
        $addressNodeShipFrom->addChild('Country', 'AO');

        return $invoiceXML;
        // Adicionar outros campos de endereço conforme necessário
    }
}
