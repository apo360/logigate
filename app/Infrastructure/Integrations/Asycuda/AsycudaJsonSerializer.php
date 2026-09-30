<?php

declare(strict_types=1);

namespace App\Infrastructure\Integrations\Asycuda;

use JsonException;
use RuntimeException;

final class AsycudaJsonSerializer
{
    public function serialize(array $representation): string
    {
        try {
            return json_encode($representation, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        } catch (JsonException $exception) {
            throw new RuntimeException('Não foi possível serializar a declaração ASYCUDA em JSON UTF-8.', previous: $exception);
        }
    }
}
