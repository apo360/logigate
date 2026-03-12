<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\ContactFormMail;
use App\Models\ContactMessage;
use Illuminate\Support\Facades\Validator;


class ContactMessageController extends Controller
{
    /**
     * Enviar mensagem de contacto
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function send(Request $request)
    {
        // Validação dos dados
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'telefone' => 'required|string|max:20',
            'empresa' => 'nullable|string|max:255',
            'assunto' => 'required|string|max:100',
            'mensagem' => 'required|string|max:5000',
        ], [
            'nome.required' => 'O nome é obrigatório',
            'email.required' => 'O email é obrigatório',
            'email.email' => 'Por favor, insira um email válido',
            'telefone.required' => 'O telefone é obrigatório',
            'assunto.required' => 'O assunto é obrigatório',
            'mensagem.required' => 'A mensagem é obrigatória',
            'mensagem.max' => 'A mensagem não pode exceder 5000 caracteres',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Guardar a mensagem na base de dados
            $contactMessage = ContactMessage::create([
                'nome' => $request->nome,
                'email' => $request->email,
                'telefone' => $request->telefone,
                'empresa' => $request->empresa,
                'assunto' => $request->assunto,
                'mensagem' => $request->mensagem,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status' => 'pending'
            ]);

            // Enviar email para a equipa
            try {
                Mail::to(config('mail.contact_email', 'aduaneiro@hongayetu.com'))
                    ->send(new ContactFormMail($contactMessage));
            } catch (\Exception $e) {
                // Log do erro mas não falha a resposta - a mensagem já foi guardada
                Log::error('Erro ao enviar email de contacto: ' . $e->getMessage());
            }

            // Resposta de sucesso
            return response()->json([
                'success' => true,
                'message' => 'Mensagem enviada com sucesso! Entraremos em contacto em breve.',
                'data' => [
                    'message_id' => $contactMessage->id
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erro ao processar formulário de contacto: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Ocorreu um erro ao processar a sua mensagem. Por favor, tente novamente mais tarde.'
            ], 500);
        }
    }
}
