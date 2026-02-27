<?php

// Criar representante
            /*Representante::create([
                'nome' => $input['name'],
                'apelido' => $input['apelido'],
                'telefone' => $input['telefone'],
                'tipo' => $input['tipo_representante'],
                'empresa_id' => $empresa->id,
            ]);*/

// 4️⃣ Armazenar logotipo no S3 se fornecido
            /*if (isset($input['logotipo']) && $input['logotipo'] instanceof \Illuminate\Http\UploadedFile) {
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
            }*/

// Gerar OTP
            $otp = rand(100000, 999999); // Gera um OTP de 6 dígitos
            User::where('id', $user->id)->update([
                'otp' => $otp,
                'otp_expires_at' => Carbon::now()->addMinutes(30), // Expira em 30 minutos
            ]);