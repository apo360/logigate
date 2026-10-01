<?php

namespace App\Actions\Fortify;

use App\Domains\Empresa\Actions\CriarEmpresaAction;
use App\Domains\Empresa\Data\EmpresaData;
use App\Models\User;
use App\Models\Subscricao;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    public function create(array $input)
    {
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

        $validator->validate();

        try {
            DB::beginTransaction();

            // 1) Utilizador
            $user = User::create([
                'name'     => $input['name'],
                'email'    => $input['email'],
                'password' => Hash::make($input['password']),
            ]);

            // 2) Empresa
            $empresa = app(CriarEmpresaAction::class)->executeForNewUser(
                EmpresaData::from([
                    'Empresa'    => $input['empresa'],
                    'Designacao' => $input['designacao'] ?? 'Despachante Oficial',
                    'NIF'        => $input['nif'],
                ]), $user
            );

            // Membership and company-scoped Administrador are created atomically by the Action.
            // Subscription business behavior remains unchanged.
            Subscricao::create([
                'empresa_id'           => $empresa->id,
                'plano_id'             => $input['plano_id'],
                'modalidade_pagamento' => $input['modalidade_pagamento'],
                'data_subscricao'      => Carbon::now(),
                'status'               => 'pendente',
            ]);

            DB::commit();

            return $user;

        } catch (\Throwable $th) {
            DB::rollBack();

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
