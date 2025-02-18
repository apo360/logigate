<?php

namespace App\Actions\Fortify;

use App\Mail\ConfirmationMail;
use App\Models\Empresa;
use App\Models\User;
use App\Models\ActivatedModule;
use App\Models\EmpresaUser;
use App\Models\Representante;
use App\Models\Subscricao;
use Aws\S3\S3Client;
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
            'empresa' => ['required', 'string', 'max:255'],
            'nif' => ['nullable', 'string', 'max:50', 'unique:empresas'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'Designacao' => ['required', 'string', 'in:Despachante Oficial,Transitário,Agente de Carga'],
            'cedula' => ['nullable', 'string', 'max:50'], // Adicionado validação para cédula
            'endereco' => ['nullable', 'string', 'max:255'],
            'provincia' => ['nullable', 'string', 'max:255'],
            'cidade' => ['nullable', 'string', 'max:255'],
            'apelido' => ['nullable', 'string', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'tipo_representante' => ['nullable', 'string', 'max:255'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : ['nullable'],
        ]);

        // Validação adicional para cédula se a designação for "Despachante Oficial"
        if ($input['Designacao'] === 'Despachante Oficial') {
            $validator->sometimes('cedula', ['required', 'string', 'max:50'], function ($input) {
                return $input['Designacao'] === 'Despachante Oficial';
            });
        }

        // Lançar exceção se a validação falhar
        $validator->validate();

        // Gerar o código da conta da empresa
        $currentYear = Carbon::now()->year;
        $companyCount = Empresa::count();
        $contaCode = 'LGi' . str_pad($companyCount + 1, 4, '0', STR_PAD_LEFT) . $currentYear;

        try {
            // Iniciar a transação
            DB::beginTransaction();

            // Cria a empresa que o administrador vai gerir
            $empresa = Empresa::create([
                'Empresa' => $input['empresa'],
                'Designacao' => $input['Designacao'],
                'NIF' => $input['nif'],
                'Cedula' => $input['cedula'] ?? null, // Usar null se cédula não for fornecida
                'Endereco_completo' => $input['endereco'] ?? null,
                'Provincia' => $input['provincia'] ?? null,
                'Cidade' => $input['cidade'] ?? null,
            ]);

            // Introduzir dados do Representante
            Representante::create([
                'nome' => $input['name'],
                'apelido' => $input['apelido'] ?? null,
                'telefone' => $input['telefone'] ?? null,
                'tipo' => $input['tipo_representante'] ?? null,
                'empresa_id' => $empresa->id,
            ]);

            // Criar dados do Usuário
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
            ]);

            // Dados do Relacionamento
            EmpresaUser::create([
                'conta' => $contaCode,
                'empresa_id' => $empresa->id,
                'user_id' => $user->id,
            ]);

            // Criar a subscrição
            Subscricao::create([
                'empresa_id' => $empresa->id,
                'modulo_id' => 1,
                'data_expiracao' => now()->addMonth(),
                'status' => 'ATIVA',
            ]);

            Subscricao::create([
                'empresa_id' => $empresa->id,
                'modulo_id' => 17,
                'data_expiracao' => now()->addMonth(),
                'status' => 'ATIVA',
            ]);

            // Ativar o módulo
            ActivatedModule::create([
                'module_id' => 1,
                'empresa_id' => $empresa->id,
                'activation_date' => now(),
            ]);

            ActivatedModule::create([
                'module_id' => 17,
                'empresa_id' => $empresa->id,
                'activation_date' => now(),
            ]);

            // Atribuir permissões de Administrador
            $role = Role::findOrCreate('Administrador');
            $user->assignRole($role);

            // Gerar OTP
            $otp = rand(100000, 999999); // Gera um OTP de 6 dígitos
            User::where('id', $user->id)->update([
                'otp' => $otp,
                'otp_expires_at' => Carbon::now()->addMinutes(30), // Expira em 30 minutos
            ]);

            // Enviar o e-mail de confirmação
            Mail::to($user->email)->send(new ConfirmationMail($otp));

            // Pasta Raiz no S3
            $s3Client = new S3Client([
                'version' => 'latest',
                'region'  => 'us-east-1', // A região do seu bucket S3
            ]);

            $bucket = 'logigate-docs'; // Nome do bucket
            $caminhoCompleto = 'Despachantes/' . $contaCode;

            $s3Client->putObject([
                'Bucket' => $bucket,
                'Key'    => $caminhoCompleto . '/',
                'Body'   => "", // Corpo vazio para simular uma pasta
            ]);

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