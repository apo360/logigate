<?php

declare(strict_types=1);

namespace App\Infrastructure\Integrations\Asycuda;

use stdClass;
use App\Domains\Declaracao\Services\AsycudaFinancialPolicy;

/** Pure mapping layer: accepts decoded data and returns arrays; no database or Eloquent access. */
final class AsycudaProcessoMapper
{
    public function mapImport(stdClass|array $input): AsycudaProcessoImportData
    {
        $source = self::arrayify($input);
        $items = is_array($source['items'] ?? null) ? $source['items'] : [];
        $itemsByExternalId = [];
        $mercadorias = [];
        foreach ($items as $index => $item) {
            $item = self::arrayify($item);
            $externalId = self::string($item['id'] ?? null);
            if ($externalId !== null) {
                $itemsByExternalId[$externalId] = $index;
            }
            $tariff = self::path($item, 'goodsDescription.tariffCode');
            $mercadorias[] = [
                'external_id' => $externalId,
                'item_number' => $item['itemNumber'] ?? null,
                'descricao' => self::path($item, 'goodsDescription.commercialDescription'),
                'codigo_aduaneiro' => $tariff,
                'ncm_hs' => $tariff,
                'ncm_hs_numero' => is_scalar($tariff) ? preg_replace('/\D+/', '', (string) $tariff) : null,
                'quantidade' => self::path($item, 'goodsPackage.number'),
                'unidade' => self::path($item, 'supplementaryUnits.0.code'),
                'peso_liquido' => self::path($item, 'goodsWeight.net'),
                'peso_bruto' => self::path($item, 'goodsWeight.gross'),
                'peso_unidade' => self::path($item, 'goodsWeight.grossWeightUnit'),
                'preco_unitario_externo' => self::path($item, 'valuationDetails.itemPrice.amount'),
                'external_item_price' => self::path($item, 'valuationDetails.itemPrice'),
                'moeda' => self::path($item, 'valuationDetails.itemPrice.currencyRate.currencyCode'),
                'origem_pais' => self::path($item, 'goodsOrigin.country.country.code'),
                'ajustes' => $item['adjustments'] ?? [],
                'valuation' => $item['valuationDetails'] ?? [],
                'supplementary_units' => $item['supplementaryUnits'] ?? [],
                'raw' => $item,
            ];
        }

        $contentores = [];
        $links = [];
        foreach (is_array($source['containers'] ?? null) ? $source['containers'] : [] as $container) {
            $container = self::arrayify($container);
            $containerId = self::string($container['id'] ?? null);
            $contentores[] = [
                'external_id' => $containerId,
                'numero' => $container['number'] ?? null,
                'tipo' => $container['type'] ?? null,
                'descricao' => $container['description'] ?? null,
                'indicador_carga' => $container['loadIndicator'] ?? null,
                'peso_tara' => $container['tareWeight'] ?? null,
                'peso_bruto' => $container['grossWeight'] ?? null,
                'volume_bruto' => $container['grossVolume'] ?? null,
                'unidade_volume_bruto' => $container['grossVolumeUnit'] ?? null,
                'numero_volumes' => $container['numberOfPackages'] ?? null,
                'descarregado' => $container['unloadedFlag'] ?? null,
                'possui_selo' => $container['sealIndicator'] ?? null,
                'resselado' => $container['resealIndicator'] ?? null,
                'declaration_item_id' => $container['declarationItemId'] ?? null,
                'raw' => $container,
            ];

            foreach (is_array($container['items'] ?? null) ? $container['items'] : [] as $containerItem) {
                $containerItem = self::arrayify($containerItem);
                $externalItemId = self::string($containerItem['itemId'] ?? null);
                $links[] = [
                    'contentor_external_id' => $containerId,
                    'mercadoria_external_id' => $externalItemId,
                    'mercadoria_index' => $externalItemId !== null ? ($itemsByExternalId[$externalItemId] ?? null) : null,
                    'asycuda_link_id' => $containerItem['id'] ?? null,
                    'asycuda_item_id' => $containerItem['itemId'] ?? null,
                    'codigo_item' => $containerItem['code'] ?? null,
                    'unresolved' => $externalItemId === null || ! array_key_exists($externalItemId, $itemsByExternalId),
                ];
            }
        }

        $consignee = self::arrayify(self::path($source, 'parties.consignee') ?? []);
        $exporter = self::arrayify(self::path($source, 'parties.exporter') ?? []);
        $declarant = self::arrayify($source['declarant'] ?? []);
        $procedure = $source['procedure'] ?? [];
        $transportMode = self::path($source, 'transportInformation.inlandTransportMode.code');
        $manifestValue = self::path($items[0] ?? [], 'transportDocumentReference.manifest');
        $manifest = self::arrayify($manifestValue);
        $originCodes = [];
        foreach ($items as $item) {
            $origin = self::path($item, 'goodsOrigin.country.country.code');
            if (is_scalar($origin) && trim((string) $origin) !== '') $originCodes[] = trim((string) $origin);
        }
        $originCodes = array_values(array_unique($originCodes));
        $process = [
            'regiao_aduaneira_reference' => [
                'abrev' => self::path($procedure, 'modelCode'),
                'codigo' => self::path($procedure, 'generalProcedureCode'),
            ],
            'tipo_transporte' => $transportMode,
            'registo_transporte' => self::path($manifest, 'registrationNumber')
                ?? (is_scalar($manifestValue) ? $manifestValue : null),
            'transport_identity_reference' => self::path($source, 'transportInformation.arrivalIdentity.identifier'),
            'nacionalidade_transporte' => self::path($source, 'transportInformation.arrivalIdentity.nationalityCode'),
            'manifest_reference' => $manifestValue === null ? null : $manifest,
            'estancia_reference' => self::path($source, 'offices.clearance.code'),
            'office_references' => [
                'clearance' => self::path($source, 'offices.clearance'),
                'presentation' => self::path($source, 'offices.presentation'),
                'border' => self::path($source, 'offices.border'),
            ],
            'customs_procedure_reference' => [
                'extended_code' => self::path($procedure, 'customsProcedure.extendedCode'),
                'additional_code' => self::path($procedure, 'customsProcedure.additionalCode'),
            ],
            'bank_reference' => self::path($source, 'bankAccount.code'),
            'paises' => [
                'origem' => count($originCodes) === 1 ? $originCodes[0] : null,
                'origens_itens' => $originCodes,
                'destino' => self::path($source, 'countries.destination.code'),
                'nacionalidade_transporte' => self::path($source, 'transportInformation.arrivalIdentity.nationalityCode'),
            ],
            'raw_procedure' => $procedure,
        ];

        return new AsycudaProcessoImportData(
            processo: $process,
            mercadorias: $mercadorias,
            contentores: $contentores,
            contentorMercadorias: $links,
            customerReference: self::partyReference($consignee, 'operatorCode'),
            exportadorReference: self::partyReference($exporter, 'operatorCode'),
            declarantReference: self::partyReference($declarant, 'operatorCode'),
            documentos: self::normalizeDocuments($source['mediaDocuments'] ?? []),
            financial: [
                'valuation' => $source['valuation'] ?? [],
                'adjustments' => $source['adjustments'] ?? [],
                'transaction_term' => $source['transactionTerm'] ?? [],
                'policy' => (new AsycudaFinancialPolicy())->describe($source, $items),
            ],
            unmapped: [
                'root' => $source,
                'item_fields' => ['goodsPackage', 'goodsWeight', 'supplementaryUnits', 'adjustments', 'valuationDetails'],
            ],
            warnings: self::mappingWarnings($links, $source),
        );
    }

    /** Build the confirmed external mapping from normalized arrays, never from database IDs. */
    public function mapExport(array $processo, array $empresa, array $customer, array $exportador, array $mercadorias, array $contentores): array
    {
        $externalIds = [];
        foreach ($mercadorias as $item) {
            $key = (string) ($item['id'] ?? count($externalIds));
            $externalIds[$key] = self::uuid($item['asycuda_id'] ?? null);
        }

        $items = [];
        foreach ($mercadorias as $item) {
            $key = (string) ($item['id'] ?? count($items));
            $items[] = [
                'id' => $externalIds[$key],
                'itemNumber' => $item['itemNumber'] ?? count($items) + 1,
                'goodsDescription' => [
                    'commercialDescription' => $item['Descricao'] ?? $item['descricao'] ?? null,
                    'tariffCode' => $item['codigo_aduaneiro'] ?? $item['NCM_HS'] ?? $item['NCM_HS_Numero'] ?? null,
                ],
                'transportDocumentReference' => [
                    'manifest' => $processo['manifest_reference']
                        ?? ['registrationNumber' => $processo['registo_transporte'] ?? null],
                ],
                'valuationDetails' => $item['valuationDetails'] ?? [],
                'adjustments' => $item['adjustments'] ?? [],
            ];
        }

        $containers = [];
        foreach ($contentores as $container) {
            $containerItems = [];
            foreach ($container['mercadorias'] ?? [] as $link) {
                $localItemId = (string) (is_array($link) ? ($link['id'] ?? $link['mercadoria_id'] ?? '') : $link);
                if (! isset($externalIds[$localItemId])) {
                    continue;
                }
                $containerItems[] = [
                    'id' => self::uuid(is_array($link) ? ($link['asycuda_link_id'] ?? null) : null),
                    'itemId' => $externalIds[$localItemId],
                    'code' => is_array($link) ? ($link['codigo_item'] ?? null) : null,
                ];
            }
            $containers[] = [
                'id' => self::uuid($container['asycuda_id'] ?? null),
                'number' => $container['numero'] ?? null,
                'type' => $container['tipo'] ?? null,
                'description' => $container['descricao'] ?? null,
                'loadIndicator' => $container['indicador_carga'] ?? null,
                'tareWeight' => $container['peso_tara'] ?? null,
                'grossWeight' => $container['peso_bruto'] ?? null,
                'grossVolume' => $container['volume_bruto'] ?? null,
                'grossVolumeUnit' => $container['unidade_volume_bruto'] ?? null,
                'numberOfPackages' => $container['numero_volumes'] ?? null,
                'unloadedFlag' => (bool) ($container['descarregado'] ?? false),
                'sealIndicator' => (bool) ($container['possui_selo'] ?? false),
                'resealIndicator' => (bool) ($container['resselado'] ?? false),
                'declarationItemId' => $container['asycuda_declaration_item_id'] ?? null,
                'items' => $containerItems,
            ];
        }

        return [
            'procedure' => [
                'modelCode' => $processo['tipo_declaracao']['abrev'] ?? null,
                'generalProcedureCode' => $processo['tipo_declaracao']['codigo'] ?? null,
            ],
            'declarant' => self::externalParty($empresa, ['operatorCode' => $empresa['NIF'] ?? null]),
            'parties' => [
                'consignee' => self::externalParty($customer, ['operatorCode' => $customer['CustomerTaxID'] ?? null]),
                'exporter' => self::externalParty($exportador, ['operatorCode' => $exportador['ExportadorTaxID'] ?? null]),
            ],
            'transportInformation' => [
                'inlandTransportMode' => ['code' => $processo['TipoTransporte'] ?? null],
                'arrivalIdentity' => [
                    'identifier' => $processo['transport_identifier'] ?? null,
                    'nationalityCode' => $processo['nacionalidade_transporte'] ?? null,
                ],
            ],
            'offices' => ['clearance' => ['code' => $processo['estancia']['code'] ?? null]],
            'items' => $items,
            'containers' => $containers,
            'valuation' => $processo['valuation'] ?? [],
            'adjustments' => $processo['adjustments'] ?? [],
            'transactionTerm' => $processo['transactionTerm'] ?? [
                'paymentTermCode' => $processo['forma_pagamento'] ?? null,
            ],
            'mediaDocuments' => $processo['mediaDocuments'] ?? [],
        ];
    }

    private static function normalizeDocuments(mixed $documents): array
    {
        $result = [];
        foreach (is_array($documents) ? $documents : [] as $document) {
            $document = self::arrayify($document);
            $result[] = [
                'code' => $document['code'] ?? $document['type'] ?? null,
                'reference' => $document['reference'] ?? $document['number'] ?? null,
                'documentDate' => $document['documentDate'] ?? $document['date'] ?? null,
                'name' => $document['name'] ?? null,
                'hash' => $document['hash'] ?? null,
                'size' => $document['size'] ?? null,
                'status' => $document['status'] ?? null,
                'itemNumber' => $document['itemNumber'] ?? null,
                'raw' => $document,
            ];
        }
        return $result;
    }

    private static function partyReference(array $party, string $identifier): array
    {
        return [
            'identifier' => self::string($party[$identifier] ?? null),
            'name' => $party['name'] ?? null,
            'address' => $party['address'] ?? null,
            'country' => $party['country'] ?? null,
            'raw' => $party,
        ];
    }

    private static function externalParty(array $data, array $extra): array
    {
        return array_filter([
            'operatorCode' => $extra['operatorCode'] ?? null,
            'authorizationCode' => $data['Cedula'] ?? null,
            'name' => $data['CompanyName'] ?? $data['Exportador'] ?? $data['Empresa'] ?? null,
            'address' => $data['Endereco'] ?? $data['address'] ?? $data['Endereco_completo'] ?? null,
            'country' => $data['Pais'] ?? $data['country'] ?? null,
        ], static fn ($value) => $value !== null);
    }

    private static function mappingWarnings(array $links, array $source): array
    {
        $warnings = [];
        $originCodes = [];
        foreach ($source['items'] ?? [] as $item) {
            $origin = self::path($item, 'goodsOrigin.country.country.code');
            if (is_scalar($origin) && trim((string) $origin) !== '') $originCodes[] = trim((string) $origin);
        }
        if (count(array_unique($originCodes)) > 1) $warnings[] = 'Os itens indicam países de origem diferentes; não foi escolhido um único país para o Processo.';
        foreach ($links as $link) {
            if ($link['unresolved']) {
                $warnings[] = 'Container item references an item UUID absent from items[].';
                break;
            }
        }
        if (self::path($source, 'procedure.modelCode') === null || self::path($source, 'procedure.generalProcedureCode') === null) {
            $warnings[] = 'Procedure lookup reference is incomplete.';
        }
        return $warnings;
    }

    private static function arrayify(mixed $value): array
    {
        if (is_array($value)) return $value;
        if ($value instanceof stdClass) return get_object_vars($value);
        return [];
    }

    private static function path(mixed $value, string $path): mixed
    {
        foreach (explode('.', $path) as $segment) {
            $value = self::arrayify($value);
            if (ctype_digit($segment)) {
                $value = $value[(int) $segment] ?? null;
            } else {
                $value = $value[$segment] ?? null;
            }
            if ($value === null) return null;
        }
        return $value;
    }

    private static function string(mixed $value): ?string
    {
        return is_scalar($value) && trim((string) $value) !== '' ? trim((string) $value) : null;
    }

    private static function uuid(mixed $value): string
    {
        if (is_string($value) && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value)) {
            return $value;
        }
        return sprintf('%04x%04x-%04x-4%03x-%04x-%04x%04x%04x', random_int(0, 65535), random_int(0, 65535), random_int(0, 65535), random_int(0, 4095), random_int(0, 16383) | 0x8000, random_int(0, 65535), random_int(0, 65535), random_int(0, 65535));
    }
}
