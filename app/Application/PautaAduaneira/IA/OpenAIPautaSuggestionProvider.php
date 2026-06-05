<?php

namespace App\Application\PautaAduaneira\IA;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

final class OpenAIPautaSuggestionProvider implements PautaSuggestionProviderInterface
{
    public function __construct(
        private readonly LocalPautaSuggestionProvider $fallback,
    ) {
    }

    public function suggest(PautaSuggestionDTO $dto, Collection $candidates): array
    {
        $key = config('services.openai.key');

        if (! $key || $candidates->isEmpty()) {
            return $this->fallback->suggest($dto, $candidates);
        }

        try {
            $response = Http::withToken($key)
                ->timeout((int) config('services.openai.timeout', 20))
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => config('services.openai.model', 'gpt-4o-mini'),
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Você é especialista em classificação pautal aduaneira em Angola. Ranqueie apenas os candidatos recebidos. Não invente códigos.',
                        ],
                        [
                            'role' => 'user',
                            'content' => json_encode([
                                'mercadoria' => [
                                    'descricao' => $dto->descricao,
                                    'marca' => $dto->marca,
                                    'modelo' => $dto->modelo,
                                    'chassis' => $dto->chassis,
                                    'codigo_atual' => $dto->codigoAtual,
                                ],
                                'candidatos' => $candidates->map(fn ($pauta) => [
                                    'id' => $pauta->id,
                                    'codigo' => $pauta->codigo,
                                    'descricao' => $pauta->descricao,
                                ])->values()->all(),
                                'responda' => [
                                    'suggestions' => [
                                        [
                                            'id' => 'id do candidato',
                                            'confidence' => '0-100',
                                            'reason' => 'motivo curto',
                                        ],
                                    ],
                                ],
                            ], JSON_UNESCAPED_UNICODE),
                        ],
                    ],
                ]);

            $payload = $response->json('choices.0.message.content');
            $decoded = is_string($payload) ? json_decode($payload, true) : null;
            $rows = is_array($decoded['suggestions'] ?? null) ? $decoded['suggestions'] : [];
            $byId = $candidates->keyBy('id');

            $suggestions = collect($rows)
                ->map(function (array $row) use ($byId) {
                    $id = (int) ($row['id'] ?? 0);
                    $pauta = $byId->get($id);

                    if (! $pauta) {
                        return null;
                    }

                    return new PautaSuggestionResultDTO(
                        pautaAduaneiraId: (int) $pauta->id,
                        codigo: (string) $pauta->codigo,
                        descricao: (string) $pauta->descricao,
                        confidence: (int) ($row['confidence'] ?? 70),
                        reason: trim((string) ($row['reason'] ?? 'Sugestão ranqueada por IA.')),
                        source: 'hybrid',
                    );
                })
                ->filter()
                ->take($dto->limit)
                ->values()
                ->all();

            return $suggestions ?: $this->fallback->suggest($dto, $candidates);
        } catch (\Throwable) {
            return $this->fallback->suggest($dto, $candidates);
        }
    }
}
