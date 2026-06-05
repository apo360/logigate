<?php

namespace App\Application\PautaAduaneira\IA;

use Illuminate\Support\Collection;

interface PautaSuggestionProviderInterface
{
    /**
     * @param Collection<int, \App\Models\PautaAduaneira> $candidates
     * @return array<int, PautaSuggestionResultDTO>
     */
    public function suggest(PautaSuggestionDTO $dto, Collection $candidates): array;
}
