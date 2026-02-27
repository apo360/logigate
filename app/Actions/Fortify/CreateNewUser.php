<?php

namespace App\Actions\Fortify;

use App\Mail\ConfirmationMail;
use App\Models\Empresa;
use App\Models\User;
use App\Models\EmpresaUser;
use App\Models\Plano;
use App\Models\Subscricao;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\ValidationException;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input)
    {
        // Validação dos dados de entrada
        $validator = Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'plano_id' => ['required', 'exists:planos,id'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : ['nullable'],
        ]);

        try {
            // Lançar exceção se a validação falhar
            $validator->validate();

            // Iniciar a transação
            DB::beginTransaction();

            // Criar dados do Usuário
            $user = User::create(['name' => $input['name'], 'email' => $input['email'], 'password' => Hash::make($input['password']),]);

            // Criar empresa diretamente
            $empresa = Empresa::create(['Empresa' => 'ContaDemo'.Empresa::lastId()+1, 'Designacao' => 'Despachante Oficial',]);

            // Dados do Relacionamento Empresa, Usuario e Conta
            EmpresaUser::create(['empresa_id' => $empresa->id, 'user_id' => $user->id, 'conta' => $empresa->conta]);

            // Cria a subscrição Pendente
            Subscricao::create([
                'empresa_id' => $empresa->id,
                'plano_id' => $input['plano_id'],
                'modalidade_pagamento' => $input['modalidade_pagamento'],
                'data_subscricao' => Carbon::now(),
                'data_inicio' => Carbon::now(),
                'status' => 'pendente',
            ]);

            // Atribuir permissões de Administrador
            $role = Role::findOrCreate('Administrador');
            
            $user->assignRole($role);

            // Confirmar a transação
            DB::commit();

            return $user;
            
        } catch (\Throwable $th) {
            // Reverter a transação em caso de erro
            DB::rollBack();

            // Registra o erro no log
            Log::error('Erro ao criar empresa e usuário.', [
                'error' => $th->getMessage(),
                'input' => $input,
                'trace' => $th->getTraceAsString(),
            ]);

            // Lançar exceção para o usuário com uma mensagem genérica
            throw ValidationException::withMessages([
                'error' => 'Ocorreu um erro ao criar a empresa. Tente novamente mais tarde.',
            ]);
        }
    }
}