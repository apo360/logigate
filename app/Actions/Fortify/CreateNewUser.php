<?php

namespace App\Actions\Fortify;

use App\Domains\Empresa\Actions\CriarEmpresaAction;
use App\Domains\Empresa\Data\EmpresaData;
use App\Models\User;
use App\Models\EmpresaUser;
use App\Models\Subscricao;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;
use Spatie\Permission\Models\Role;

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
        // 1) Validação FORA do try — para que erros de validação
        //    cheguem ao utilizador com a mensagem real.
        $validator = Validator::make($input, [
            'name'                 => ['required', 'string', 'max:255'],
            'empresa'              => ['required', 'string', 'max:255', 'unique:empresas,Empresa'],
            'nif'                  => ['required', 'string', 'max:255', 'unique:empresas,NIF'],
            'email'                => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'plano_id'             => ['required', 'exists:planos,id'],
            'modalidade_pagamento' => ['required', 'string', 'in:monthly,yearly'],
            'password'             => $this->passwordRules(),
            'terms'                => Jetstream::hasTermsAndPrivacyPolicyFeature()
                ? ['accepted', 'required']
                : ['nullable'],
        ]);

        // Lança ValidationException automaticamente se falhar
        $validator->validate();

        try {
            DB::beginTransaction();

            // 2) Criar utilizador
            $user = User::create([
                'name'     => $input['name'],
                'email'    => $input['email'],
                'password' => Hash::make($input['password']),
            ]);

            // 3) Criar empresa via Action (DTO com ::from agora existente)
            $empresa = app(CriarEmpresaAction::class)->execute(
                EmpresaData::from([
                    'Empresa'    => $input['empresa'],
                    'Designacao' => $input['designacao'] ?? 'Despachante Oficial',
                    'NIF'        => $input['nif'],
                ])
            );

            // 4) Relacionamento empresa <-> utilizador
            EmpresaUser::create([
                'empresa_id' => $empresa->id,
                'user_id'    => $user->id,
                'conta'      => $empresa->conta,
            ]);

            // 5) Subscrição pendente
            Subscricao::create([
                'empresa_id'            => $empresa->id,
                'plano_id'              => $input['plano_id'],
                'modalidade_pagamento'  => $input['modalidade_pagamento'],
                'data_subscricao'       => Carbon::now(),
                'status'                => 'pendente',
            ]);

            // 6) Atribuir role de Administrador (guard explícito)
            $role = Role::findOrCreate('Administrador', 'web');
            $user->assignRole($role);

            DB::commit();

            return $user;

        } catch (\Throwable $th) {
            DB::rollBack();

            // Não logues a password em claro
            $safeInput = $input;
            unset($safeInput['password'], $safeInput['password_confirmation']);

            Log::error('Erro ao criar empresa e usuário.', [
                'error' => $th->getMessage(),
                'input' => $safeInput,
                'trace' => $th->getTraceAsString(),
            ]);

            throw ValidationException::withMessages([
                'error' => 'Ocorreu um erro ao criar a empresa. Tente novamente mais tarde.',
            ]);
        }
    }
}