<?php

namespace App\Services;

class AsycudaParser
{
    public function parse(string $filePath): array
    {
        // Verifica se o ficheiro existe
        if (!file_exists($filePath)) {
            throw new \Exception("Ficheiro não encontrado: " . $filePath);
        }

        // Lê o conteúdo do ficheiro
        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new \Exception("Falha ao ler o ficheiro: " . $filePath);
        }

        // Converte o XML para um array associativo
        $xml = simplexml_load_string($content, "SimpleXMLElement", LIBXML_NOCDATA);
        if ($xml === false) {
            throw new \Exception("Formato XML inválido no ficheiro: " . $filePath);
        }

        // Converte SimpleXMLElement para array
        $json = json_decode(json_encode($xml), true);

        return [
            'declaration'  => $this->extractDeclaration($json),
            'traders'      => $this->extractTraders($json),
            'goods_items'  => $this->extractGoodsItems($json),
            'documents'    => $this->extractDocuments($json),
            'taxes'        => $this->extractTaxes($json),
        ];
    }

    // Extrai a declaração principal
    protected function extractDeclaration(array $json): array
    {
        return $json['Goods_Declaration'] ?? [];
    }
    // Extrai os traders
    protected function extractTraders(array $json): array
    {
        return $json['Goods_Declaration']['Traders'] ?? [];
    }
    // Extrai os itens de mercadoria
    protected function extractGoodsItems(array $json): array
    {
        return $this->normalizeArray($json['Goods_Declaration']['Goods_Item'] ?? []);
    }
    // Extrai os documentos associados
    protected function extractDocuments(array $json): array
    {
        return $this->normalizeArray($json['Goods_Declaration']['Documents'] ?? []);
    }
    // Extrai os impostos e taxas
    protected function extractTaxes(array $json): array
    {
        $items = $this->normalizeArray($json['Goods_Declaration']['Goods_Item'] ?? []);
        $taxes = [];
        foreach ($items as $item) {
            $duties = $this->normalizeArray($item['Duties_Taxes'] ?? []);
            foreach ($duties as $duty) {
                $taxes[] = [
                    'item_number' => $item['Item_Number'] ?? null,
                    'tax_type'    => $duty['TaxType'] ?? null,
                    'amount'      => $duty['Amount'] ?? null,
                ];
            }
        }
        return $taxes;
    }
    // Normaliza um valor para garantir que é sempre um array
    protected function normalizeArray($value): array
    {
        if (empty($value)) {
            return [];
        }
        return isset($value[0]) ? $value : [$value];
    }

    // Outros métodos auxiliares podem ser adicionados aqui conforme necessário
}
