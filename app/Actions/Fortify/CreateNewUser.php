<?php

namespace App\Actions\Fortify;

use App\Http\Controllers\OtpController;
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
        Validator::make($input, [
            'empresa' => ['required', 'string', 'max:255'],
            'nif' => ['nullable', 'string', 'max:50', 'unique:empresas'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : ['nullable'],
        ])->validate();

        // Gerar o código da conta da empresa
        $currentYear = Carbon::now()->year;
        $companyCount = Empresa::count();
        $contaCode = 'LGi' . str_pad($companyCount + 1, 4, '0', STR_PAD_LEFT) . $currentYear;

        try {
            // Iniciar a transação
            DB::beginTransaction();

            // Cria a empresa que o administrador vai gerir
            $empresa = Empresa::create([
                'conta' => $contaCode,
                'Empresa' => $input['empresa'],
                'Designacao' => 'Despachante Oficial',
                'NIF' => $input['nif'],
                'Cedula' => $input['cedula'],
                'Endereco_completo' => $input['endereco'],
                'Provincia' => $input['provincia'],
                'Cidade' => $input['cidade'], // Corrigido para 'Cidade'
            ]);

            // Introduzir dados do Representante
            Representante::create([
                'nome' => $input['name'],
                'apelido' => $input['apelido'],
                'telefone' => $input['telefone'],
                'tipo' => $input['tipo_representante'],
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
            // Inicializar o cliente S3
            $s3Client = new S3Client([
                'version' => 'latest',
                'region'  => 'us-east-1', // A região do seu bucket S3
            ]);

            $bucket = 'logigate-docs'; // Nome do bucket

            // Criar o caminho completo da pasta, incluindo a raiz e o nome da pasta
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
