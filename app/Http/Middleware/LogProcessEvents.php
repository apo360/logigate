<?php

namespace App\Http\Middleware;

use App\Models\HistoricoProcesso;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LogProcessEvents
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        if ($request->isMethod('post') || $request->isMethod('put') || $request->isMethod('delete')) {
            $this->logEvent($request);
        }

        return $next($request);
    }

    protected function logEvent(Request $request)
    {
        $user = Auth::user();
        $descricao = $this->getEventDescription($request);

        HistoricoProcesso::create([
            'processo_id' => $request->route('processo'),
            'user_id' => $user->id,
            'descricao' => $descricao,
        ]);
    }

    protected function getEventDescription(Request $request)
    {
        switch ($request->method()) {
            case 'POST':
                return 'Processo criado';
            case 'PUT':
                return 'Processo atualizado';
            case 'DELETE':
                return 'Processo deletado';
            default:
                return 'Operação desconhecida';
        }
    }
}
