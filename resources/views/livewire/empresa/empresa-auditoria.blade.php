<div class="mx-auto max-w-6xl space-y-5">
    <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Controlo</p>
        <h1 class="mt-1 text-2xl font-bold text-slate-950 dark:text-white">Segurança & Auditoria</h1>
        <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600 dark:text-slate-300">
            O sistema já utiliza autenticação Jetstream, permissões Spatie e auditoria de modelos. Esta página centraliza o acesso administrativo sem expor dados sensíveis fora das permissões existentes.
        </p>
    </section>

    <section class="grid gap-4 md:grid-cols-3">
        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-700">
                <i class="fa fa-key"></i>
            </span>
            <h2 class="mt-4 font-semibold text-slate-950 dark:text-white">Acesso</h2>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
                Alteração de senha, sessões e 2FA permanecem em Minha Conta.
            </p>
            <a href="{{ route('profile.show') }}" class="mt-3 inline-flex text-sm font-semibold text-blue-700 hover:text-blue-800">
                Abrir minha conta
            </a>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50 text-indigo-700">
                <i class="fa fa-users-gear"></i>
            </span>
            <h2 class="mt-4 font-semibold text-slate-950 dark:text-white">Permissões</h2>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
                Funções e permissões são geridas no módulo de usuários da empresa.
            </p>
            <a href="{{ route('usuarios.index') }}" class="mt-3 inline-flex text-sm font-semibold text-blue-700 hover:text-blue-800">
                Gerir usuários
            </a>
        </div>

        <div class="rounded-lg border border-dashed border-slate-300 bg-white p-5 dark:border-slate-700 dark:bg-slate-900">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100 text-slate-700">
                <i class="fa fa-clipboard-list"></i>
            </span>
            <h2 class="mt-4 font-semibold text-slate-950 dark:text-white">Eventos auditáveis</h2>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
                Relatórios filtráveis de auditoria ficam reservados para uma etapa futura de UX e permissões específicas.
            </p>
        </div>
    </section>
</div>
