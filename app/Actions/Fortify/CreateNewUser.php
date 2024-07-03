<?php

namespace App\Actions\Fortify;

use App\Models\Empresa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
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
        Validator::make($input, [
            'empresa' => ['required', 'string', 'max:255'],
            'nif' => ['nullable', 'string', 'max:50', 'unique:empresas'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : ['nullable'],
            // 'module' => ['required', 'string', 'max:255'],
            // 'plan' => ['required', 'string', 'max:255'],
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);

        // Cria a empresa que administrador vai gerir
        $empresa = Empresa::create([
            'Empresa' => $input['empresa'],
            'Designacao' => 'Despachante Oficial',
            'NIF' => $input['nif'],
            'Cedula' => $input['cedula'],
            'Endereco_completo' => $input['endereco'],
            'Dominio' => $input['subdominio'],
        ]);

        // Generate the conta code
        $currentYear = Carbon::now()->year;
        $companyCount = Empresa::count();
        $contaCode = 'LGi' . str_pad($companyCount + 1, 4, '0', STR_PAD_LEFT) . $currentYear . 'XYZ'; // 'XYZ' can be replaced with any desired suffix

        $user->empresas()->attach($empresa->id, ['conta' => $contaCode]);

        // Atribuir permissões de Administrador...
        $role = Role::findOrCreate('admin');

        // Atribua o papel ao usuário
        $user->assignRole($role);

        return $user;
    }
}
