<?php

namespace App\Services;

class SAFtParser
{
    public function parse(string $filePath): array
    {
        // Verifica se o ficheiro existe
        if (!file_exists($filePath)) {
            throw new \Exception("Ficheiro não encontrado: " . $filePath);
        }

        // Lê o conteúdo do ficheiro e converte de XML para array
        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new \Exception("Falha ao ler o ficheiro: " . $filePath);
        }

        // Carrega o XML
        $xml = simplexml_load_string($content, "SimpleXMLElement", LIBXML_NOCDATA);
        if ($xml === false) {
            throw new \Exception("Formato XML inválido no ficheiro: " . $filePath);
        }

        // Converte SimpleXML para array associativo
        $json = json_decode(json_encode($xml), true);

        return [
            'header'          => $this->extractHeader($json),
            'customers'       => $this->normalizeArray($json['MasterFiles']['Customer'] ?? []),
            'suppliers'       => $this->normalizeArray($json['MasterFiles']['Supplier'] ?? []),
            'products'        => $this->normalizeArray($json['MasterFiles']['Product'] ?? []),
            'tax_table'       => $this->normalizeArray($json['MasterFiles']['TaxTable']['TaxTableEntry'] ?? []),
            'invoices'        => $this->extractInvoices($json),
            'invoice_lines'   => $this->extractInvoiceLines($json),
            'payments'        => $this->normalizeArray($json['SourceDocuments']['Payments']['Payment'] ?? []),
            'working_docs'    => $this->normalizeArray($json['SourceDocuments']['WorkingDocuments']['WorkDocument'] ?? []),
        ];
    }

    // Extrai o header do ficheiro SAF-T
    protected function extractHeader(array $json): array
    {
        return $json['Header'] ?? [];
    }

    // Extrai as faturas e normaliza os dados
    protected function extractInvoices(array $json): array
    {
        $invoices = $this->normalizeArray($json['SourceDocuments']['SalesInvoices']['Invoice'] ?? []);
        $result = [];
        foreach ($invoices as $inv) {
            $result[] = [
                'InvoiceNo'     => $inv['InvoiceNo'] ?? null,
                'InvoiceDate'   => $inv['InvoiceDate'] ?? null,
                'CustomerID'    => $inv['CustomerID'] ?? null,
                'GrossTotal'    => $inv['DocumentTotals']['GrossTotal'] ?? null,
                'NetTotal'      => $inv['DocumentTotals']['NetTotal'] ?? null,
                'TaxPayable'    => $inv['DocumentTotals']['TaxPayable'] ?? null,
            ];
        }
        return $result;
    }

    // Extrai as linhas das faturas
    protected function extractInvoiceLines(array $json): array
    {
        $invoices = $this->normalizeArray($json['SourceDocuments']['SalesInvoices']['Invoice'] ?? []);
        $lines = [];

        foreach ($invoices as $inv) {
            $invoiceNo = $inv['InvoiceNo'] ?? null;
            $invLines = $this->normalizeArray($inv['Line'] ?? []);

            foreach ($invLines as $line) {
                $lines[] = [
                    'InvoiceNo'   => $invoiceNo,
                    'LineNumber'  => $line['LineNumber'] ?? null,
                    'ProductCode' => $line['ProductCode'] ?? null,
                    'Description' => $line['ProductDescription'] ?? null,
                    'Quantity'    => $line['Quantity'] ?? null,
                    'UnitPrice'   => $line['UnitPrice'] ?? null,
                    'CreditAmount'=> $line['CreditAmount'] ?? null,
                    'DebitAmount' => $line['DebitAmount'] ?? null,
                ];
            }
        }

        return $lines;
    }

    /**
     * Garante que o resultado é sempre array de registos.
     * Ex.: Se tiver apenas 1 cliente, SimpleXML converte em assoc array → normalizamos para lista.
     */
    protected function normalizeArray($value): array
    {
        if (empty($value)) {
            return [];
        }
        return isset($value[0]) ? $value : [$value];
    }
}
