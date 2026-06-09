<x-app-layout>
    <x-slot name="header">
        <span class="text-sm font-semibold text-slate-900 dark:text-slate-100">Subscrição e Pagamentos</span>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-5">
        <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Plano</p>
            <div class="mt-1 flex flex-col justify-between gap-4 md:flex-row md:items-start">
                <div>
                    <h1 class="text-2xl font-bold text-slate-950 dark:text-white">Subscrição da empresa</h1>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600 dark:text-slate-300">
                        {{ $empresa->Empresa }} pode consultar o plano actual, continuar pagamentos pendentes ou escolher um novo plano.
                    </p>
                </div>
                <span class="inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                    Conta {{ $empresa->conta }}
                </span>
            </div>
        </section>

        @if($activeSubscription)
            <section class="rounded-lg border border-green-200 bg-green-50 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-green-700">Subscrição activa</p>
                <div class="mt-3 grid gap-4 md:grid-cols-4">
                    <div>
                        <p class="text-xs text-green-700">Plano</p>
                        <p class="font-semibold text-green-950">{{ $activeSubscription->plano->nome ?? $activeSubscription->tipo_plano ?? 'Plano activo' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-green-700">Modalidade</p>
                        <p class="font-semibold text-green-950">{{ ucfirst((string) $activeSubscription->modalidade_pagamento) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-green-700">Renovação / expiração</p>
                        <p class="font-semibold text-green-950">
                            {{ $activeSubscription->data_expiracao?->format('d/m/Y') ?? 'Sem data definida' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-green-700">Valor</p>
                        <p class="font-semibold text-green-950">{{ number_format((float) $activeSubscription->valor_pago, 2, ',', '.') }} Kz</p>
                    </div>
                </div>
            </section>
        @elseif($pendingSubscription)
            <section class="rounded-lg border border-amber-200 bg-amber-50 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">Pagamento pendente</p>
                <div class="mt-2 flex flex-col justify-between gap-4 md:flex-row md:items-center">
                    <div class="text-sm text-amber-900">
                        Existe uma subscrição pendente para
                        <strong>{{ $pendingSubscription->plano->nome ?? 'o plano seleccionado' }}</strong>.
                        Continue para finalizar o checkout.
                    </div>
                    <a href="{{ route('checkout', ['conta' => $empresa->conta]) }}"
                       class="inline-flex items-center justify-center rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-amber-700">
                        <i class="fa fa-credit-card mr-2"></i>
                        Continuar pagamento
                    </a>
                </div>
            </section>
        @else
            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @forelse($planos as $plano)
                    <form method="POST" action="{{ route('billing.start') }}" class="flex min-h-full flex-col rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                        @csrf
                        <input type="hidden" name="plano_id" value="{{ $plano->id }}">

                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h2 class="text-lg font-bold text-slate-950 dark:text-white">{{ $plano->nome }}</h2>
                                <p class="mt-1 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ $plano->descricao }}</p>
                            </div>
                            @if($plano->destaque)
                                <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">Popular</span>
                            @endif
                        </div>

                        <div class="mt-4 grid gap-2 text-sm text-slate-600 dark:text-slate-300">
                            <div>Utilizadores: <strong class="text-slate-900 dark:text-white">{{ $plano->limite_utilizadores }}</strong></div>
                            <div>Processos: <strong class="text-slate-900 dark:text-white">{{ $plano->limite_processos }}</strong></div>
                            <div>Armazenamento: <strong class="text-slate-900 dark:text-white">{{ $plano->limite_armazenamento_gb }} GB</strong></div>
                        </div>

                        <label for="modalidade-{{ $plano->id }}" class="mt-5 text-sm font-semibold text-slate-700 dark:text-slate-200">Modalidade</label>
                        <select id="modalidade-{{ $plano->id }}" name="modalidade_pagamento" class="mt-2 rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                            <option value="monthly">Mensal - {{ number_format((float) $plano->preco_mensal, 2, ',', '.') }} Kz</option>
                            <option value="trimestral">Trimestral - {{ number_format((float) $plano->preco_trimestral, 2, ',', '.') }} Kz</option>
                            <option value="semestral">Semestral - {{ number_format((float) $plano->preco_semestral, 2, ',', '.') }} Kz</option>
                            <option value="annual">Anual - {{ number_format((float) $plano->preco_anual, 2, ',', '.') }} Kz</option>
                        </select>

                        <button type="submit" class="mt-5 inline-flex min-h-11 items-center justify-center rounded-lg bg-blue-700 px-4 text-sm font-semibold text-white transition hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            {{ (($plano->is_free ?? false) || (float) $plano->preco_mensal <= 0) ? 'Activar plano' : 'Continuar para checkout' }}
                        </button>
                    </form>
                @empty
                    <div class="rounded-lg border border-dashed border-slate-300 bg-white p-5 text-sm text-slate-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                        Nenhum plano activo encontrado. Contacte a equipa Logigate para activar a sua subscrição.
                    </div>
                @endforelse
            </section>
        @endif
    </div>
</x-app-layout>
