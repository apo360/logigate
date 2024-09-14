<?php

namespace App\Actions\Fortify;

use App\Http\Controllers\OtpController;
use App\Mail\ConfirmationMail;
use App\Models\Empresa;
use App\Models\User;
use App\Models\ActivatedModule;
use App\Models\Subscricao;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
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
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);

        // Cria a empresa que administrador vai gerir
        $empresa = Empresa::create([
            'Empresa' => $input['empresa'],
            'Designacao' => $input['Designacao'],
            'NIF' => $input['nif'],
            'Cedula' => $input['cedula'],
            'Endereco_completo' => $input['endereco'],
        ]);

        // Para gerar o código de conta da empresa
        $currentYear = Carbon::now()->year;
        $companyCount = Empresa::count();
        $contaCode = 'LGi' . str_pad($companyCount + 1, 4, '0', STR_PAD_LEFT) . $currentYear; // 'LGi' can be replaced with any desired suffix

        $user->empresas()->attach($empresa->id, ['conta' => $contaCode]);

        Subscricao::create([
            'empresa_id' => $empresa->id,
            'modulo_id' => 1,
            'data_expiracao' => now()->addYear(),
            'status' => 'ATIVA'
        ]);

        ActivatedModule::create([
            'module_id' => 1,
            'empresa_id' => $empresa->id,
            'activation_date' => now(),
        ]);

        // Atribuir permissões de Administrador...
        $role = Role::findOrCreate('admin');

        // Atribua o papel ao usuário
        $user->assignRole($role);


        $otp = rand(100000, 999999); // Gera um OTP de 6 dígitos

        User::where('id', $user->id)->update([
            'otp' => $otp,
            'otp_expires_at' => Carbon::now()->addMinutes(30), // Expira em 30 minutos
        ]);
        // Enviar o e-mail de confirmação
        Mail::to($user->email)->send(new ConfirmationMail($otp));

        return $user;
    }
}
