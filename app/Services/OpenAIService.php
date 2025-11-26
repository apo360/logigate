<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class OpenAIService
{
    // This class can be used to interact with OpenAI's API for various functionalities.
    // Currently, it is a placeholder and can be expanded as needed.
    
    /**
     * Placeholder method for OpenAI API interaction.
     *
     * @param string $prompt
     * @return string
     */
    public function analyze($parsedData)
    {
        $prompt = "Analisa os seguintes dados SAF-T. Verifica inconsistências e sugere correções:";
        $prompt .= json_encode($parsedData['customers']);

        $response = Http::withToken(env('OPENAI_API_KEY'))->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-5.1',
            'messages' => [
                ['role' => 'system', 'content' => 'Você é um auditor fiscal especializado em XML SAF-T.'],
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        return $response->json();
    }
    /**
     * Placeholder method for OpenAI API interaction.
     *
     * @param string $prompt
     * @return string
     */
    public function generateReport($parsedData)
    {
        $prompt = "Gere um relatório detalhado com base nos seguintes dados SAF-T:";
        $prompt .= json_encode($parsedData['customers']);

        $response = Http::withToken(env('OPENAI_API_KEY'))->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4',
            'messages' => [
                ['role' => 'system', 'content' => 'Você é um auditor fiscal especializado em XML SAF-T.'],
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        return $response->json();
    }
    /**
     * Placeholder method for OpenAI API interaction.
     *
     * @param string $prompt
     * @return string
     */
    public function validateData($parsedData)
    {
        $prompt = "Valide os seguintes dados SAF-T e identifique possíveis erros ou inconsistências:";
        $prompt .= json_encode($parsedData['customers']);

        $response = Http::withToken(env('OPENAI_API_KEY'))->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4',
            'messages' => [
                ['role' => 'system', 'content' => 'Você é um auditor fiscal especializado em XML SAF-T.'],
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        return $response->json();
    }
    /**
     * Placeholder method for OpenAI API interaction.
     *
     * @param string $prompt
     * @return string
     */
    public function summarizeData($parsedData)
    {
        $prompt = "Resuma os seguintes dados SAF-T, destacando as principais informações e tendências:";
        $prompt .= json_encode($parsedData['customers']);

        $response = Http::withToken(env('OPENAI_API_KEY'))->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4',
            'messages' => [
                ['role' => 'system', 'content' => 'Você é um auditor fiscal especializado em XML SAF-T.'],
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        return $response->json();
    }
    /**
     * Placeholder method for OpenAI API interaction.
     *
     * @param string $prompt
     * @return string
     */
    public function generateSummary($parsedData)
    {
        $prompt = "Gere um resumo dos seguintes dados SAF-T, destacando as principais informações e tendências:";
        $prompt .= json_encode($parsedData['customers']);

        $response = Http::withToken(env('OPENAI_API_KEY'))->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4',
            'messages' => [
                ['role' => 'system', 'content' => 'Você é um auditor fiscal especializado em XML SAF-T.'],
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        return $response->json();
    }
    /**
     * Placeholder method for OpenAI API interaction.
     *
     * @param string $prompt
     * @return string
     */
    public function generateInsights($parsedData)
    {
        $prompt = "Gere insights detalhados com base nos seguintes dados SAF-T, destacando tendências e padrões:";
        $prompt .= json_encode($parsedData['customers']);

        $response = Http::withToken(env('OPENAI_API_KEY'))->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4',
            'messages' => [
                ['role' => 'system', 'content' => 'Você é um auditor fiscal especializado em XML SAF-T.'],
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        return $response->json();
    }

    
    /**
     * Placeholder method for OpenAI API interaction.
     *
     * @param string $prompt
     * @return string
     */
    public function generateRecommendations($parsedData)
    {
        $prompt = "Gere recomendações detalhadas com base nos seguintes dados SAF-T, destacando áreas de melhoria e conformidade:";
        $prompt .= json_encode($parsedData['customers']);

        $response = Http::withToken(env('OPENAI_API_KEY'))->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4',
            'messages' => [
                ['role' => 'system', 'content' => 'Você é um auditor fiscal especializado em XML SAF-T.'],
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        return $response->json();
    }

    public function analisarXMLAsycuda($xmlPath)
    {
        $xmlString = file_get_contents($xmlPath);

        $prompt = "Este é um ficheiro XML do sistema ASYCUDA para declarações aduaneiras. 
        Analisa o ficheiro, resume o conteúdo e identifica qualquer erro ou campo incompleto. Segue o XML:

        ```xml
        $xmlString
        ```";

        $response = Http::withToken(env('OPENAI_API_KEY'))->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        $json = $response->json();

        if (isset($json['choices'][0]['message']['content'])) {
            return $json['choices'][0]['message']['content'];
        }

        return 'Erro: resposta inesperada da API. Verifique a chave ou o modelo.';
    }
}