<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use OwenIt\Auditing\Models\Audit;
use App\Notifications\SuspeitoLogin;
use Illuminate\Support\Facades\Notification;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {

        // Escutar o evento de login e registrar auditoria
        Event::listen(Login::class, function ($event) {
            Audit::create([
                'user_type' => $event->user->roles->pluck('name')->first(),
                'user_id' => $event->user->id,
                'event' => 'login',
                'new_values' => ['message' => 'Usuário fez login'],
                'url' => request()->fullUrl(), // Obtém a URL completa da requisição
                'ip_address' => request()->ip(),
                'user_agent' => request()->header('User-Agent'), // Obtém o User-Agent
                'auditable_type' => get_class($event->user), // Adicionando o tipo do modelo
                'auditable_id' => $event->user->id, // Adicionando o ID do modelo
            ]);

            $event->user->update([
                'last_login_at' => now(),
                'last_login_ip' => request()->ip(),
            ]);

            // Contabiliza o número de logins nos últimos 15 minutos
            $loginCount = Audit::where('user_id', $event->user->id)
            ->where('event', 'login')
            ->where('created_at', '>=', now()->subMinutes(15))
            ->count();

            if ($loginCount > 5) {
                // Active um alerta de comportamento anômalo
                Log::alert('Possível comportamento anômalo', ['user_id' => $event->user->id, 'email' => $event->user->email]);
            }

            // Verifique se houve logins de diferentes IPs nas últimas 24 horas
            $loginsFromDifferentIps = Audit::where('user_id', $event->user->id)
            ->where('event', 'login')->where('created_at', '>=', now()->subDay())
            ->distinct('ip_address')->count();

            if ($loginsFromDifferentIps > 1) {
                Log::alert('Logins de IPs diferentes em um curto período', [
                    'user_id' => $event->user->id, 
                    'ip_address' => request()->ip(),
                    'email' => $event->user->email
                ]);
            }

            // Notificar o usuário sobre um login de um novo IP
            if ($event->user->last_login_ip !== request()->ip()) {
                Notification::route('mail', $event->user->email)
                    ->notify(new SuspeitoLogin($event->user, request()->ip()));
            }
        });

        Event::listen(Logout::class, function ($event) {
            Audit::create([
                'user_type' => $event->user->roles->pluck('name')->first(),
                'user_id' => $event->user->id,
                'event' => 'logout',
                'new_values' => ['message' => 'Usuário fez logout'],
                'ip_address' => request()->ip(),
                'auditable_type' => get_class($event->user),
                'auditable_id' => $event->user->id,
                'url' => request()->fullUrl(),
                'user_agent' => request()->header('User-Agent'),
            ]);
        });

        Event::listen(Failed::class, function ($event) {
            Log::warning('Falha no login', [
                'email' => $event->credentials['email'],
                'ip_address' => request()->ip(),
                'user_agent' => request()->header('User-Agent'),
            ]);
        });
    }
}
