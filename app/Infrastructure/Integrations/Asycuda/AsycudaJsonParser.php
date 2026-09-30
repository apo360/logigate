<?php

declare(strict_types=1);

namespace App\Infrastructure\Integrations\Asycuda;

use JsonException;
use stdClass;

final class AsycudaJsonParser
{
    public function parse(string $json): AsycudaJsonParseResult
    {
        try {
            $data = json_decode($json, false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            return new AsycudaJsonParseResult(
                success: false,
                data: null,
                errors: ['Invalid JSON: ' . $exception->getMessage()],
            );
        }

        if (! $data instanceof stdClass) {
            return new AsycudaJsonParseResult(
                success: false,
                data: null,
                errors: ['The JSON root must be an object.'],
            );
        }

        if (property_exists($data, 'items') && ! is_array($data->items)) {
            return new AsycudaJsonParseResult(
                success: false,
                data: null,
                errors: ['The items field must be an array when present.'],
            );
        }

        if (isset($data->items)) {
            foreach ($data->items as $index => $item) {
                if (! $item instanceof stdClass) {
                    return new AsycudaJsonParseResult(
                        success: false,
                        data: null,
                        errors: [sprintf('The items[%d] entry must be an object.', $index)],
                    );
                }
            }
        }

        $warnings = property_exists($data, 'items')
            ? []
            : ['The observed items field is absent; no item structure was validated.'];

        return new AsycudaJsonParseResult(
            success: true,
            data: $data,
            warnings: $warnings,
        );
    }
}
