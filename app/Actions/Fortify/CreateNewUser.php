<?php

namespace App\Actions\Fortify;

use App\Helpers\DatabaseErrorHandler;
use App\Http\Controllers\ArquivoController;
use App\Mail\ConfirmationMail;
use App\Models\Empresa;
use App\Models\User;
use App\Models\ActivatedModule;
use App\Models\EmpresaUser;
use App\Models\Plano;
use App\Models\Representante;
use App\Models\Subscricao;
use App\Services\ModuloAtivacaoService;
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
use Illuminate\Http\Request;

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
            'logotipo' => ['nullable', 'image', 'max:5120', 'mimes:jpeg,png,jpg,gif,svg'], // Exemplo de validação para imagem
            'cidade' => ['nullable', 'string', 'max:255'],
            'apelido' => ['nullable', 'string', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'tipo_representante' => ['nullable', 'string', 'max:255'],
            'plano_id' => ['required', 'exists:planos,id'],
            'modalidade_pagamento' => ['required', 'string', 'max:255'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : ['nullable'],
        ]);

        // dd($input);

        // Validação adicional para cédula se a designação for "Despachante Oficial"
        if ($input['Designacao'] === 'Despachante Oficial') {
            $validator->sometimes('cedula', ['required', 'string', 'max:50'], function ($input) {
                return $input['Designacao'] === 'Despachante Oficial';
            });
        }

        // Lançar exceção se a validação falhar
        $validator->validate();

        try {
            // Iniciar a transação
            DB::beginTransaction();

            // Criar dados do Usuário
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
            ]);

            // Criar empresa diretamente
            $empresa = Empresa::create([
                'Empresa' => $input['empresa'],
                'Designacao' => $input['Designacao'],
                'NIF' => $input['nif'],
                'Cedula' => $input['cedula'],
                'Endereco_completo' => $input['endereco'],
                'Provincia' => $input['provincia'],
                'Cidade' => $input['cidade'],
            ]);

            // Criar representante
            Representante::create([
                'nome' => $input['name'],
                'apelido' => $input['apelido'],
                'telefone' => $input['telefone'],
                'tipo' => $input['tipo_representante'],
                'empresa_id' => $empresa->id,
            ]);

            // Dados do Relacionamento Empresa, Usuario e Conta
            EmpresaUser::create(['empresa_id' => $empresa->id, 'user_id' => $user->id, 'conta' => $empresa->conta]);

            // 4️⃣ Armazenar logotipo no S3 se fornecido
            if (isset($input['logotipo']) && $input['logotipo'] instanceof \Illuminate\Http\UploadedFile) {
                try {
                    // Inicializa o cliente S3
                    $s3 = new \Aws\S3\S3Client([
                        'version' => 'latest',
                        'region' => env('AWS_DEFAULT_REGION'),
                        'credentials' => [
                            'key'    => env('AWS_ACCESS_KEY_ID'),
                            'secret' => env('AWS_SECRET_ACCESS_KEY'),
                        ],
                    ]);

                    // Nome único e caminho do arquivo
                    $fileName = 'Logotipos/' . uniqid() . '_' . preg_replace('/\s+/', '_', $input['logotipo']->getClientOriginalName());

                    // Upload para o bucket configurado
                    $result = $s3->putObject([
                        'Bucket' => env('AWS_BUCKET'),
                        'Key'    => $fileName,
                        'SourceFile' => $input['logotipo']->getPathname(),
                        'ACL'    => 'public-read', // opcional — se quiser acesso público
                    ]);

                    // Guardar o URL do logotipo
                    $logotipoUrl = $result['ObjectURL'];

                    // Atualizar no banco
                    $empresa->update(['logotipo' => $logotipoUrl]);

                } catch (\Aws\Exception\AwsException $e) {
                    // Lidar com falhas no upload
                    Log::error('Erro ao enviar logotipo para o S3', [
                        'empresa_id' => $empresa->id,
                        'error' => $e->getMessage(),
                    ]);

                    // Opcionalmente, podes continuar o processo sem travar o registo
                    // ou lançar exceção se quiser abortar:
                    throw ValidationException::withMessages(['logotipo' => 'Falha ao enviar logotipo.']);
                }
            }


            // Cria a subscrição e automáticamente activa os modulos e menus associados (Um serviço dentro do controller de subscrição)
            if (!isset($input['plano_id'])) {
                Log::error('Plano de subscrição não fornecido.', [
                    'empresa_id' => $empresa->id,
                ]);
                throw ValidationException::withMessages(['plano_id' => 'Plano de subscrição é obrigatório.']);
            }else{
                try
                {
                    $duracao = match ($input['modalidade_pagamento']) {
                        'Mensal' => 1,
                        'Trimestral' => 3,
                        'Semestral' => 6,
                        'Anual' => 12,
                        default => 1
                    };

                    $plano = Plano::find($input['plano_id']);

                    // Calcular preço do plano (exemplo simples)
                    $valorPago = $plano->preco_mensal * $duracao;

                    // Registar a subscrição com o plano subscrito
                    Subscricao::create([
                        'empresa_id' => $empresa->id,
                        'modulo_id' => 0,
                        'plano_id' => $input['plano_id'],
                        'data_subscricao' => Carbon::now(),
                        'data_expiracao' => Carbon::now()->addMonths($duracao),
                        'tipo_plano' => $plano->nome,
                        'modalidade_pagamento' => $input['modalidade_pagamento'],
                        'valor_pago' => $valorPago,
                        'status' => 'ATIVA',
                    ]);
                    // Chama o Serviço de Activação de Módulos
                    app(ModuloAtivacaoService::class)->ativarModulos($empresa->id, $plano->id);

                } catch (\Exception $e) {
                    // Lidar com falhas na subscrição e Activação de Módulos
                    Log::error('Erro ao criar subscrição para a empresa.', [
                        'empresa_id' => $empresa->id,
                        'error' => $e->getMessage(),
                    ]);
                    Log::error('Erro ao activar módulos para a empresa.', [
                        'empresa_id' => $empresa->id,
                        'error' => $e->getMessage(),
                    ]);
                    throw ValidationException::withMessages(['plano_id' => 'Falha ao criar subscrição.']);
                }
            }

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
            // Mail::to($user->email)->send(new ConfirmationMail($otp));

            // Pasta Raiz no S3
            $pasta = new ArquivoController();
            $pasta->createMasterFolder($empresa->id);

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