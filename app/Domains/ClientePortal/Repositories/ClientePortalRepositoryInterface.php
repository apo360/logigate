<?php

declare(strict_types=1);

namespace App\Domains\ClientePortal\Repositories;

use App\Domains\ClientePortal\DTOs\CredenciaisClienteDTO;

interface ClientePortalRepositoryInterface
{
    public function clientePossuiCredenciais(int|string $clienteId): bool;

    /**
     * Cria o usuário/credenciais do cliente.
     *
     * Implementação deve guardar hash da password.
     */
    public function criarCredenciaisCliente(int|string $clienteId, CredenciaisClienteDTO $dto): void;

    public function verificarClienteVinculado(int|string $clienteId): bool;

    /**
     * Atualiza a password do cliente (deve ser hash).
     */
    public function redefinirPasswordCliente(int|string $clienteId, string $novaPassword): void;

    /**
     * Consulta processos associados ao cliente.
     * Retorna uma estrutura serializável (array).
     */
    public function listarProcessosCliente(int|string $clienteId, array $filtros = []): array;

    /**
     * Retorna o identificador/email do usuário vinculado, se existir.
     */
    public function getUsuarioVinculado(int|string $clienteId): ?array;
}

