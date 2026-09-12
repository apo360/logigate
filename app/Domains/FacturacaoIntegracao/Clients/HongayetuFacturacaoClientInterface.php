<?php

namespace App\Domains\FacturacaoIntegracao\Clients;

interface HongayetuFacturacaoClientInterface
{
    public function verificarCredenciais(array $config, array $credenciais): array;

    public function listarClientes(array $query = [], array $config = [], array $credenciais = []): array;

    public function criarCliente(array $payload, array $config = [], array $credenciais = []): array;

    public function actualizarCliente(int $id, array $payload, array $config = [], array $credenciais = []): array;

    public function listarFacturas(array $query = [], array $config = [], array $credenciais = []): array;

    public function consultarFactura(int $id, array $config = [], array $credenciais = []): array;

    public function emitirFactura(array $payload, array $config = [], array $credenciais = []): array;

    public function obterPdfFactura(int $id, bool $base64 = true, array $config = [], array $credenciais = []): array|string;

    public function anularFactura(int $id, array $payload, array $config = [], array $credenciais = []): array;

    public function listarBancos(array $query = [], array $config = [], array $credenciais = []): array;

    public function listarEstabelecimentos(array $query = [], array $config = [], array $credenciais = []): array;
}
