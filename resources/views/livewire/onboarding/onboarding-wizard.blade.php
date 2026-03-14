@if(! $isCompleted)
    <section class="rounded-[2rem] bg-gradient-to-br from-slate-900 via-sky-900 to-cyan-700 p-[1px] shadow-xl shadow-cyan-200/40">
        <div class="rounded-[calc(2rem-1px)] bg-white/95 p-6 backdrop-blur">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-cyan-600">Onboarding</p>
                    <h2 class="mt-2 text-2xl font-black text-slate-900">Complete a configuração inicial da sua operação</h2>
                    <p class="mt-2 max-w-2xl text-sm text-slate-600">Este assistente fica visível até que a empresa, utilizadores, operação inicial e armazenamento estejam prontos.</p>
                </div>
                <div class="rounded-2xl bg-slate-900 px-4 py-3 text-white">
                    <div class="text-xs uppercase tracking-[0.18em] text-white/60">Progresso</div>
                    <div class="mt-1 text-xl font-black">{{ $checklist['completed_steps'] ?? 0 }}/{{ $checklist['total_steps'] ?? 4 }}</div>
                </div>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-3xl border px-4 py-5 {{ ($checklist['company_profile_complete'] ?? false) ? 'border-emerald-200 bg-emerald-50' : 'border-amber-200 bg-amber-50' }}">
                    <div class="text-sm font-semibold text-slate-900">Perfil da empresa</div>
                    <div class="mt-2 text-xs text-slate-600">Nome, NIF, endereço, email e contacto móvel.</div>
                </div>
                <div class="rounded-3xl border px-4 py-5 {{ ($checklist['users_configured'] ?? false) ? 'border-emerald-200 bg-emerald-50' : 'border-amber-200 bg-amber-50' }}">
                    <div class="text-sm font-semibold text-slate-900">Utilizadores</div>
                    <div class="mt-2 text-xs text-slate-600">Mais de um utilizador associado à empresa.</div>
                </div>
                <div class="rounded-3xl border px-4 py-5 {{ ($checklist['operations_started'] ?? false) ? 'border-emerald-200 bg-emerald-50' : 'border-amber-200 bg-amber-50' }}">
                    <div class="text-sm font-semibold text-slate-900">Operação iniciada</div>
                    <div class="mt-2 text-xs text-slate-600">Primeiro processo ou licenciamento criado.</div>
                </div>
                <div class="rounded-3xl border px-4 py-5 {{ ($checklist['storage_configured'] ?? false) ? 'border-emerald-200 bg-emerald-50' : 'border-amber-200 bg-amber-50' }}">
                    <div class="text-sm font-semibold text-slate-900">Armazenamento</div>
                    <div class="mt-2 text-xs text-slate-600">Disco configurado para `s3`.</div>
                </div>
            </div>

            <div class="mt-6 grid gap-3 md:grid-cols-2">
                @foreach($warnings as $warning)
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-900">
                        ⚠ {{ $warning }}
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
