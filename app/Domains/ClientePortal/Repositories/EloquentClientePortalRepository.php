<?php

namespace App\Domains\ClientePortal\Repositories;

use App\Domains\ClientePortal\DTOs\CredenciaisClienteDTO;

/**
 * Skeleton Eloquent repository.
 *
 * NOTA: Este ficheiro foi criado apenas para estruturar a arquitetura.
 * A implementação real depende dos Models/tabelas existentes no teu projeto.
 */
final class EloquentClientePortalRepository implements ClientePortalRepositoryInterface
{
    public function clientePossuiCredenciais(int|string $clienteId): bool
    {
        // TODO: verificar no model/table real.
        return false;
    }

    public function criarCredenciaisCliente(int|string $clienteId, CredenciaisClienteDTO $dto): void
    {
        // TODO: criar user/model do cliente e persistir hash.
        // Exemplos típicos: User::create([...]);
        // onde: password deve ser hash.
    }

    public function verificarClienteVinculado(int|string $clienteId): bool
    {
        // TODO: verificar se há vínculo com conta de acesso.
        return true;
    }

    public function redefinirPasswordCliente(int|string $clienteId, string $novaPassword): void
    {
        // TODO: atualizar password (hash).
    }

    public function listarProcessosCliente(int|string $clienteId, array $filtros = []): array
    {
        // TODO: consultar processos do cliente (ex.: Process::where(...)->get()->toArray()).
        return [];
    }

    public function getUsuarioVinculado(int|string $clienteId): ?array
    {
        // TODO: retornar email/id do user vinculado.
        return null;
    }
}

