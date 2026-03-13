<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\NewsletterSubscriber;
use App\Mail\NewsletterConfirmationMail;
use App\Mail\NewsletterWelcomeMail;

class NewsletterSubscriberController extends BaseController
{
    /**
     * Subscrever na newsletter
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function subscribe(Request $request)
    {
        // Validação
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255|unique:newsletter_subscribers,email',
        ], [
            'email.required' => 'O email é obrigatório',
            'email.email' => 'Por favor, insira um email válido',
            'email.unique' => 'Este email já está registado na nossa newsletter',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Criar token de confirmação
            $token = Str::random(64);
            
            // Guardar subscritor
            $subscriber = NewsletterSubscriber::create([
                'email' => $request->email,
                'token' => $token,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status' => 'pending' // pending, active, unsubscribed
            ]);

            // Enviar email de confirmação
            try {
                Mail::to($subscriber->email)
                    ->send(new NewsletterConfirmationMail($subscriber));
            } catch (\Exception $e) {
                Log::error('Erro ao enviar email de confirmação: ' . $e->getMessage());
                // Não falhamos o processo - o subscritor fica pendente
            }

            return response()->json([
                'success' => true,
                'message' => 'Confirmação enviada! Por favor, verifique o seu email para confirmar a subscrição.',
                'data' => [
                    'email' => $subscriber->email
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erro ao subscrever newsletter: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Ocorreu um erro ao processar a sua subscrição. Por favor, tente novamente.'
            ], 500);
        }
    }

    /**
     * Confirmar subscrição
     *
     * @param string $token
     * @return \Illuminate\Http\JsonResponse
     */
    public function confirm($token)
    {
        $subscriber = NewsletterSubscriber::where('token', $token)
            ->where('status', 'pending')
            ->first();

        if (!$subscriber) {
            return response()->json([
                'success' => false,
                'message' => 'Token inválido ou subscrição já confirmada.'
            ], 404);
        }

        try {
            // Atualizar status
            $subscriber->update([
                'status' => 'active',
                'confirmed_at' => now(),
                'token' => null // token usado apenas uma vez
            ]);

            // Enviar email de boas-vindas
            try {
                Mail::to($subscriber->email)
                    ->send(new NewsletterWelcomeMail($subscriber));
            } catch (\Exception $e) {
                Log::error('Erro ao enviar email de boas-vindas: ' . $e->getMessage());
            }

            // Pode retornar uma view de confirmação ou JSON
            return response()->json([
                'success' => true,
                'message' => 'Subscrição confirmada com sucesso! Bem-vindo à nossa newsletter.'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erro ao confirmar subscrição: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao confirmar subscrição.'
            ], 500);
        }
    }

    /**
     * Cancelar subscrição
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function unsubscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:newsletter_subscribers,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $subscriber = NewsletterSubscriber::where('email', $request->email)
                ->where('status', 'active')
                ->first();

            if (!$subscriber) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email não encontrado ou já cancelado.'
                ], 404);
            }

            $subscriber->update([
                'status' => 'unsubscribed',
                'unsubscribed_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Subscrição cancelada com sucesso. Sentiremos a sua falta!'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erro ao cancelar subscrição: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao cancelar subscrição.'
            ], 500);
        }
    }

    /**
     * Listar subscritores (admin only)
     */
    public function index(Request $request)
    {
        // TODO: Adicionar middleware de autenticação
        $subscribers = NewsletterSubscriber::orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $subscribers
        ]);
    }

    /**
     * Exportar subscritores ativos (admin only)
     */
    public function export()
    {
        // TODO: Adicionar middleware de autenticação
        $subscribers = NewsletterSubscriber::where('status', 'active')
            ->select('email', 'created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $subscribers
        ]);
    }
}
