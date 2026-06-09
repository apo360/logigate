<div class="mx-auto max-w-6xl space-y-5">
    <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Administração</p>
        <h1 class="mt-1 text-2xl font-bold text-slate-950 dark:text-white">Configurações</h1>
        <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600 dark:text-slate-300">
            Área reservada para regras internas da empresa. As opções abaixo usam apenas capacidades já existentes ou ficam marcadas como futuras.
        </p>
    </section>

    <section class="grid gap-4 md:grid-cols-2">
        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <div class="flex items-start gap-3">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-700">
                    <i class="fa fa-hashtag"></i>
                </span>
                <div>
                    <h2 class="font-semibold text-slate-950 dark:text-white">Numeração</h2>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
                        Prefixos e códigos actuais são geridos no perfil da empresa quando os campos existem.
                    </p>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <div class="flex items-start gap-3">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700">
                    <i class="fa fa-money-bill-transfer"></i>
                </span>
                <div>
                    <h2 class="font-semibold text-slate-950 dark:text-white">Câmbio</h2>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
                        A gestão de câmbios continua disponível no módulo existente da empresa.
                    </p>
                    <a href="{{ route('empresa.cambio') }}" class="mt-3 inline-flex text-sm font-semibold text-blue-700 hover:text-blue-800">
                        Abrir câmbios
                    </a>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-dashed border-slate-300 bg-white p-5 dark:border-slate-700 dark:bg-slate-900">
            <h2 class="font-semibold text-slate-950 dark:text-white">Preferências operacionais</h2>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
                Prazos internos, regras de finalização e notificações podem ser adicionados futuramente com aprovação de estrutura de dados.
            </p>
        </div>

        <div class="rounded-lg border border-dashed border-slate-300 bg-white p-5 dark:border-slate-700 dark:bg-slate-900">
            <h2 class="font-semibold text-slate-950 dark:text-white">Documentos obrigatórios</h2>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
                Regras por tipo de processo ficam como placeholder até existir uma fonte persistente para essas definições.
            </p>
        </div>
    </section>
</div>
