<?php

namespace App\Application\PautaAduaneira\IA;

final class SugerirCodigoPautalAction
{
    public function __construct(
        private readonly PautaSuggestionService $service,
    ) {
    }

    public function execute(PautaSuggestionDTO $dto): array
    {
        return collect($this->service->suggest($dto))
            ->map(fn (PautaSuggestionResultDTO $result) => $result->toArray())
            ->values()
            ->all();
    }
}
