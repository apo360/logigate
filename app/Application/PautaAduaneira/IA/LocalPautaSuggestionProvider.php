<?php

namespace App\Application\PautaAduaneira\IA;

use Illuminate\Support\Collection;

final class LocalPautaSuggestionProvider implements PautaSuggestionProviderInterface
{
    public function suggest(PautaSuggestionDTO $dto, Collection $candidates): array
    {
        $tokens = $this->tokens($dto->textForSearch());

        return $candidates
            ->map(function ($pauta) use ($tokens) {
                $description = mb_strtolower((string) $pauta->descricao);
                $matches = collect($tokens)->filter(fn (string $token) => str_contains($description, $token))->count();
                $confidence = $matches > 0 ? min(85, 45 + ($matches * 10)) : 40;

                return new PautaSuggestionResultDTO(
                    pautaAduaneiraId: (int) $pauta->id,
                    codigo: (string) $pauta->codigo,
                    descricao: (string) $pauta->descricao,
                    confidence: $confidence,
                    reason: $matches > 0 ? 'Correspondência local por descrição.' : 'Candidato local da pauta existente.',
                    source: 'local',
                );
            })
            ->sortByDesc(fn (PautaSuggestionResultDTO $result) => $result->confidence)
            ->values()
            ->all();
    }

    private function tokens(string $text): array
    {
        $text = mb_strtolower($text);
        $parts = preg_split('/[^\pL\pN]+/u', $text) ?: [];

        return array_values(array_filter($parts, fn (string $part) => mb_strlen($part) >= 3));
    }
}
