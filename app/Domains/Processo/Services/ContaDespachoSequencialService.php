<?php
namespace App\Domains\Processo\Services;

final readonly class ContaDespachoSequencialService
{
    public function gerarContaDespachoSequencial(?int $empresaId = null): string
    {
        $empresaId ??= \App\Support\TenantContext::empresaId();
        if (! $empresaId) { throw new \Illuminate\Auth\Access\AuthorizationException('Empresa activa obrigatória.'); }
        $year = (int) now()->format('Y');
        $next = app(OperationalSequence::class)->reserve($empresaId, 'conta_despacho', $year);
        return 'CCD-' . str_pad((string) $next, 3, '0', STR_PAD_LEFT) . '/' . $year;
    }
}
