<div class="rounded-lg border bg-white p-5">
    <h2 class="mb-4 text-lg font-semibold">Sessão e Segurança</h2>
    <div class="grid gap-3 text-sm md:grid-cols-2">
        <p><strong>Última sessão:</strong> {{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Nunca' }}</p>
        <p><strong>Último IP:</strong> {{ $user->last_login_ip ?? 'N/D' }}</p>
        <p><strong>Email verificado:</strong> {{ $user->email_verified_at ? 'Sim' : 'Não' }}</p>
        <p><strong>Senha alterada:</strong> {{ $user->password_changed ? 'Sim' : 'Não' }}</p>
    </div>
</div>
