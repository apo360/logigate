<?php

namespace App\Infrastructure\FacturacaoIntegracao\Hongayetu;

use App\Domains\FacturacaoIntegracao\Clients\HongayetuFacturacaoClientInterface;
use App\Domains\FacturacaoIntegracao\Exceptions\CredenciaisFacturacaoInvalidasException;
use App\Domains\FacturacaoIntegracao\Exceptions\FacturaExternaFalhouException;
use App\Domains\FacturacaoIntegracao\Exceptions\FacturacaoIntegracaoException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Classe responsável por interagir com a API de facturação do Hongayetu.
 * Implementa a interface `HongayetuFacturacaoClientInterface` e fornece métodos para listar, criar, atualizar clientes e facturas, bem como obter PDFs de facturas e listar bancos e estabelecimentos.
 */

class HttpHongayetuFacturacaoClient implements HongayetuFacturacaoClientInterface
{
    // Define a base URL padrão para a API do Hongayetu
    private const DEFAULT_BASE_URL = 'https://api.hongayetu.com/logigate/v1';

    // Construtor da classe, que recebe as configurações e credenciais da integração
    public function __construct(
        private readonly array $config = [],
        private readonly array $credenciais = [],
    ) {
    }

    public function verificarCredenciais(array $config, array $credenciais): array
    {
        return $this->send('post', '/auth/verificar', [], [], $config, $credenciais);
    }
    /**
     * Bloco de métodos para interagir com a API do Hongayetu, incluindo listar, criar e atualizar clientes.
     * Cada método utiliza o método privado `send` para enviar a requisição HTTP apropriada, e o método `request` para configurar a requisição com as credenciais e configurações corretas. O método `normalize` é usado para padronizar a resposta da API, tratando erros e retornando os dados de forma consistente.
     */
    public function listarClientes(array $query = [], array $config = [], array $credenciais = []): array
    {
        return $this->send('get', '/facturacao/clientes', [], $query, $config, $credenciais);
    }

    public function criarCliente(array $payload, array $config = [], array $credenciais = []): array
    {
        return $this->send('post', '/facturacao/clientes', $payload, [], $config, $credenciais);
    }

    public function actualizarCliente(int $id, array $payload, array $config = [], array $credenciais = []): array
    {
        return $this->send('put', "/facturacao/clientes/{$id}", $payload, [], $config, $credenciais);
    }
    // Fim do bloco de métodos para interagir com a API do Hongayetu

    /**
     * Bloco de métodos para interagir com facturas, incluindo listar, consultar, emitir, obter PDF e anular facturas.
     * Cada método utiliza o método privado `send` para enviar a requisição HTTP apropriada, e o método `request` para configurar a requisição com as credenciais e configurações corretas. O método `normalize` é usado para padronizar a resposta da API, tratando erros e retornando os dados de forma consistente.
     * O método `obterPdfFactura` possui um comportamento especial, permitindo retornar o PDF da factura em base64 ou como um arquivo binário, dependendo do parâmetro `$base64`. Se o PDF for retornado como arquivo binário, o método verifica o tipo de conteúdo da resposta para garantir que não seja JSON antes de retornar o corpo da resposta.
     * O método `listarBancos` e `listarEstabelecimentos` permitem listar os bancos e estabelecimentos disponíveis na API do Hongayetu, respectivamente.
     * O método privado `send` é responsável por enviar a requisição HTTP para a API do Hongayetu, utilizando o método HTTP apropriado (GET, POST, PUT) e tratando os headers, payload e query parameters conforme necessário. Ele também chama o método `normalize` para padronizar a resposta da API, tratando erros e retornando os dados de forma consistente.
     * O método privado `request` é responsável por configurar a requisição HTTP com as credenciais e configurações corretas, incluindo o token de autenticação, timeout e tentativas de retry. Ele lança uma exceção `CredenciaisFacturacaoInvalidasException` se o token de autenticação não estiver configurado corretamente.
     */
    public function listarFacturas(array $query = [], array $config = [], array $credenciais = []): array
    {
        return $this->send('get', '/facturacao/facturas', [], $query, $config, $credenciais);
    }

    public function consultarFactura(int $id, array $config = [], array $credenciais = []): array
    {
        return $this->send('get', "/facturacao/facturas/{$id}", [], [], $config, $credenciais);
    }

    public function emitirFactura(array $payload, array $config = [], array $credenciais = []): array
    {
        $headers = [];

        if (isset($payload['idempotency_key'])) {
            $headers['Idempotency-Key'] = (string) $payload['idempotency_key'];
            unset($payload['idempotency_key']);
        }

        return $this->send('post', '/facturacao/facturas/emitir', $payload, [], $config, $credenciais, $headers);
    }

    // Metódo para obter o PDF de uma factura, com opção de retorno em base64 ou como arquivo binário.
    public function obterPdfFactura(int $id, bool $base64 = true, array $config = [], array $credenciais = []): array|string
    {
        $response = $this->request($config, $credenciais)
            ->get($this->url($config, "/facturacao/facturas/{$id}/pdf"), ['base64' => $base64 ? 1 : 0]);

        if ($response->successful() && ! $base64 && ! str_contains((string) $response->header('Content-Type'), 'json')) {
            return $response->body();
        }

        return $this->normalize($response);
    }

    public function anularFactura(int $id, array $payload, array $config = [], array $credenciais = []): array
    {
        return $this->send('post', "/facturacao/facturas/{$id}/anular", $payload, [], $config, $credenciais);
    }

    public function listarBancos(array $query = [], array $config = [], array $credenciais = []): array
    {
        return $this->send('get', '/geral/bancos', [], $query, $config, $credenciais);
    }

    public function listarEstabelecimentos(array $query = [], array $config = [], array $credenciais = []): array
    {
        return $this->send('get', '/geral/estabelecimentos', [], $query, $config, $credenciais);
    }
    // Fim do bloco de métodos para interagir com facturas

    private function send(
        string $method,
        string $path,
        array $payload = [],
        array $query = [],
        array $config = [],
        array $credenciais = [],
        array $headers = [],
    ): array {
        $request = $this->request($config, $credenciais);

        if ($headers !== []) {
            $request = $request->withHeaders($headers);
        }

        $url = $this->url($config, $path);
        $response = match ($method) {
            'get' => $request->get($url, $query),
            'put' => $request->put($url, $payload),
            default => $request->post($url, $payload),
        };

        return $this->normalize($response);
    }

    private function request(array $config = [], array $credenciais = []): PendingRequest
    {
        $config = $this->resolveConfig($config);
        $credenciais = $this->resolveCredenciais($credenciais);
        $token = $credenciais['api_token'] ?? $credenciais['api_key'] ?? null;

        if (! is_string($token) || trim($token) === '') {
            throw new CredenciaisFacturacaoInvalidasException('Token/API key da integração de facturação não configurado.');
        }

        return Http::acceptJson()
            ->asJson()
            ->withToken($token)
            ->timeout(max(1, (int) ($config['timeout'] ?? 15)))
            ->retry(
                max(0, (int) ($config['retry_attempts'] ?? $config['retry'] ?? 1)),
                max(0, (int) ($config['retry_sleep'] ?? 250)),
                throw: false,
            );
    }

    private function url(array $config, string $path): string
    {
        $config = $this->resolveConfig($config);
        $baseUrl = rtrim((string) ($config['api_url'] ?? ''), '/');

        if ($baseUrl === '') {
            throw new CredenciaisFacturacaoInvalidasException('API URL da integração de facturação não configurada.');
        }

        return $baseUrl . '/' . ltrim($path, '/');
    }

    private function normalize(Response $response): array
    {
        $json = $response->json();
        $data = is_array($json) ? $json : $response->body();

        if ($response->successful()) {
            if (is_array($data) && array_key_exists('estado', $data)) {
                return $this->sanitize($data);
            }

            return [
                'estado' => 'ok',
                'data' => $this->sanitize($data),
            ];
        }

        $error = [
            'estado' => 'erro',
            'texto' => $this->errorMessage($data),
            'codigo_erro' => $response->status(),
            'data' => $this->sanitize($data),
        ];

        if (in_array($response->status(), [401, 403], true)) {
            throw new CredenciaisFacturacaoInvalidasException($error['texto'], $error, $response->status());
        }

        if ($response->status() === 422) {
            throw new FacturacaoIntegracaoException($error['texto'], $error, $response->status());
        }

        throw new FacturaExternaFalhouException($error['texto'], $error, $response->status());
    }

    private function errorMessage(mixed $data): string
    {
        if (is_array($data)) {
            foreach (['texto', 'message', 'error', 'erro'] as $key) {
                if (isset($data[$key]) && is_scalar($data[$key])) {
                    return (string) $data[$key];
                }
            }
        }

        return 'Falha na comunicação com a API de Facturação Hongayetu.';
    }

    private function resolveConfig(array $config): array
    {
        $fallback = $config !== [] ? $config : $this->config;
        $internal = array_filter([
            'api_url' => config('hongayetu_facturacao.api_url', self::DEFAULT_BASE_URL),
            'timeout' => config('hongayetu_facturacao.timeout'),
            'retry_attempts' => config('hongayetu_facturacao.retry'),
            'environment' => config('hongayetu_facturacao.environment'),
        ], fn ($value) => $value !== null && $value !== '');

        return array_merge($fallback, $internal);
    }

    private function resolveCredenciais(array $credenciais): array
    {
        $fallback = $credenciais !== [] ? $credenciais : $this->credenciais;
        $internal = array_filter([
            'api_token' => config('hongayetu_facturacao.api_token'),
            'api_key' => config('hongayetu_facturacao.api_key'),
        ], fn ($value) => $value !== null && $value !== '');

        return array_merge($fallback, $internal);
    }

    private function sanitize(mixed $data): mixed
    {
        if (! is_array($data)) {
            return $data;
        }

        foreach ($data as $key => $value) {
            if (in_array((string) $key, ['token', 'api_token', 'api_key', 'secret', 'password'], true)) {
                $data[$key] = '***';
                continue;
            }

            $data[$key] = $this->sanitize($value);
        }

        return $data;
    }
}
