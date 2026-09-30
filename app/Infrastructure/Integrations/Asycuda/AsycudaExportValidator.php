<?php

declare(strict_types=1);

namespace App\Infrastructure\Integrations\Asycuda;

final class AsycudaExportValidator
{
    /** Validate only the structural profile observed in the supplied ASYCUDA export. */
    public function validate(array $data): array
    {
        $errors = [];
        $warnings = [];
        foreach (['procedure', 'declarant', 'parties', 'transportInformation', 'items', 'containers'] as $field) {
            if (! array_key_exists($field, $data)) $errors[] = "Campo obrigatório ausente: {$field}.";
        }
        foreach (['modelCode', 'generalProcedureCode'] as $field) {
            if (blank(data_get($data, 'procedure.' . $field))) $errors[] = "Procedimento sem {$field}; completar o tipo de declaração antes de exportar.";
        }
        if (! is_array($data['items'] ?? null) || $data['items'] === []) $errors[] = 'O processo precisa de pelo menos uma mercadoria.';
        foreach ($data['items'] ?? [] as $i => $item) {
            foreach (['id', 'itemNumber', 'goodsDescription.commercialDescription', 'goodsDescription.tariffCode'] as $path) {
                if (blank(data_get($item, $path))) $errors[] = sprintf('Mercadoria %d sem %s.', $i + 1, $path);
            }
            if (! \Illuminate\Support\Str::isUuid((string) ($item['id'] ?? ''))) $errors[] = sprintf('UUID inválido na mercadoria %d.', $i + 1);
            if (blank(data_get($item, 'goodsWeight.net')) || blank(data_get($item, 'goodsWeight.gross'))) $warnings[] = sprintf('Pesos líquidos/brutos da mercadoria %d não estão disponíveis no mapeamento confirmado.', $i + 1);
            if (blank(data_get($item, 'goodsPackage.number'))) $warnings[] = sprintf('Quantidade/embalagem da mercadoria %d não está disponível no mapeamento confirmado.', $i + 1);
            $warnings[] = sprintf('Preço, moeda e avaliação externa da mercadoria %d não foram inferidos a partir dos valores financeiros internos.', $i + 1);
        }
        foreach ($data['containers'] ?? [] as $i => $container) {
            if (blank($container['number'] ?? null)) $errors[] = sprintf('Contentor %d sem número.', $i + 1);
            if (($container['items'] ?? []) === []) $warnings[] = sprintf('Contentor %d não possui mercadorias associadas.', $i + 1);
            foreach ($container['items'] ?? [] as $link) {
                if (! in_array($link['itemId'] ?? null, array_column($data['items'] ?? [], 'id'), true)) $errors[] = sprintf('Contentor %d referencia uma mercadoria ausente.', $i + 1);
            }
        }
        foreach ($data['items'] ?? [] as $i => $item) {
            if (! in_array($item['id'] ?? null, array_merge(...array_map(fn ($container) => array_column($container['items'] ?? [], 'itemId'), $data['containers'] ?? [])), true)) {
                $warnings[] = sprintf('Mercadoria %d não está associada a contentor; confirme a modalidade da declaração.', $i + 1);
            }
        }
        if (blank(data_get($data, 'offices.clearance.code'))) $errors[] = 'Estância de despacho em falta.';
        if (blank(data_get($data, 'transportInformation.arrivalIdentity.identifier'))) $errors[] = 'Identificação do navio/transporte não resolvida; é necessário confirmar esse dado para exportar.';
        if (blank(data_get($data, 'transactionTerm.incoterms.code'))) $warnings[] = 'Incoterm não resolvido; nenhuma condição foi inferida.';
        if (blank(data_get($data, 'procedure.customsProcedure.extendedCode')) || blank(data_get($data, 'procedure.customsProcedure.additionalCode'))) $warnings[] = 'Códigos de procedimento aduaneiro adicional/estendido não disponíveis no modelo LogiGate; confirmar no ASYCUDA.';
        if (($data['adjustments'] ?? []) === []) $warnings[] = 'Ajustes de frete/seguro não foram exportados: valores e modos externos continuam por resolver.';
        if (($data['valuation'] ?? []) === []) $warnings[] = 'Valuation e totalInvoice não exportados: não foi estabelecida equivalência financeira segura.';
        $warnings[] = 'Países de origem por mercadoria e país de destino não foram exportados: o perfil observado não confirmou um alvo de declaração inequívoco para os dados disponíveis.';
        return ['errors' => array_values(array_unique($errors)), 'warnings' => array_values(array_unique($warnings))];
    }
}
