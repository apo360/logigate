<?php

namespace App\Domains\ClientePortal\Services;

use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Contracts\Auth\StatefulGuard;

/**
 * Serviço responsável por gerenciar a autenticação dos clientes no portal, utilizando o guard específico para clientes.
 */
final class ClienteAuthService
{
    public function __construct(
        private readonly StatefulGuard $guard,
    ) {
    }

    public function tentarLogin(string $email, string $password): bool
    {
        return $this->guard->attempt([
            'email' => $email,
            'password' => $password,
        ]);
    }

    // Gerar credenciais para um cliente que ainda não possui acesso ao portal
    public function gerarCredenciais(Customer $cliente, int $empresaId): string
    {
        if ($cliente->password) {
            throw new \Exception('Cliente já possui credenciais.');
        }
        $password = Str::random(10);
        $cliente->password = Hash::make($password);
        $cliente->is_client = true;
        $cliente->save();

        // Vincular a empresa se ainda não estiver
        if (!$cliente->empresas()->where('empresa_id', $empresaId)->exists()) {
            $cliente->empresas()->attach($empresaId);
        }

        return $password; // para enviar por email
    }

    // Redefinir a password de um cliente existente
    public function redefinirPassword(Customer $cliente): string
    {
        $password = Str::random(10);
        $cliente->password = Hash::make($password);
        $cliente->save();
        return $password;
    }

    // Verificar se o cliente tem acesso ao portal (ex: tem password definida)
    public function login(string $email, string $password): bool
    {
        return Auth::guard('cliente')->attempt(['email' => $email, 'password' => $password]);
    }

    // Verificar se o cliente está autenticado
    public function check(): bool
    {   return Auth::guard('cliente')->check();
    }

    // Logout do cliente
    public function logout(): void
    {
        $this->guard->logout();
    }

    public function user()
    {
        return $this->guard->user();
    }
}


