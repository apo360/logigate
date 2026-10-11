<x-app-layout>
    <x-slot name="header">
        <span class="text-sm font-semibold text-slate-900 dark:text-slate-100">Subscrição e Pagamentos</span>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-5">
        @foreach($notices as $notice)
            <p class="rounded-lg bg-amber-50 p-4 text-amber-900">{{ $notice->data['message'] ?? '' }}</p>
        @endforeach
        @if(session('success'))
            <p role="status" class="rounded-lg bg-green-50 p-4 text-green-900">{{ session('success') }}</p>
        @endif
        @if($errors->any())
            <div role="alert" class="rounded-lg bg-red-50 p-4 text-red-900">{{ $errors->first() }}</div>
        @endif
        @if(! $canManage)
            <p class="rounded-lg bg-blue-50 p-4 text-blue-900">Pode consultar o plano e os pagamentos. A contratação, renovação e cancelamento são geridos pelo Administrador ou Gestor Financeiro da empresa.</p>
        @endif
        @if($futureSubscription)
            <section class="rounded-lg bg-blue-50 p-5 text-blue-950">
                <strong>Próximo período confirmado:</strong> {{ data_get($futureSubscription->commercial_snapshot, 'plan_name') ?? $futureSubscription->plano?->nome }}.
                Início: {{ $futureSubscription->data_inicio?->format('d/m/Y') }}. O plano actual mantém-se até essa data.
            </section>
        @endif
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
        @endif
        @if($pendingSubscription)
            <section class="rounded-lg border border-amber-200 bg-amber-50 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">Pagamento pendente</p>
                <div class="mt-2 flex flex-col justify-between gap-4 md:flex-row md:items-center">
                    <div class="text-sm text-amber-900">
                        Existe uma subscrição pendente para
                        <strong>{{ $pendingSubscription->plano->nome ?? 'o plano seleccionado' }}</strong>.
                        Continue para finalizar o checkout.
                    </div>
                    <a href="{{ route('billing.checkout') }}"
                       class="inline-flex items-center justify-center rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-amber-700">
                        <i class="fa fa-credit-card mr-2"></i>
                        Continuar pagamento
                    </a>
                </div>
                @if($canManage)
                    <form method="POST" action="{{ route('billing.cancel-pending') }}" class="mt-4">
                        @csrf
                        <button type="submit" class="text-sm font-semibold text-amber-900">Cancelar contratação pendente</button>
                        <p class="mt-1 text-xs text-amber-900">Disponível quando não existe pagamento por reconciliar.</p>
                    </form>
                @endif
            </section>
        @endif
        @if(! $pendingSubscription && ! $futureSubscription && $canManage)
            @if($activeSubscription)
                <p class="text-sm text-slate-700 dark:text-slate-300">Renove ou escolha um plano para o próximo período. A alteração entra em vigor após o fim do período actual, sem retirar os acessos já pagos. A renovação requer confirmação de pagamento.</p>
                <form method="POST" action="{{ route('billing.stop-renewal') }}">
                    @csrf
                    <button type="submit" class="text-sm font-semibold text-slate-700 dark:text-slate-300">Desactivar renovação automática</button>
                </form>
            @endif
            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @forelse($planos as $plano)
                    <form method="POST" action="{{ route('billing.start') }}" class="flex min-h-full flex-col rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                        @csrf
                        <input type="hidden" name="plano_id" value="{{ $plano->id }}">
                        <input type="hidden" name="renew" value="{{ $activeSubscription ? 1 : 0 }}">

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
                        @if($plano->modulos->isNotEmpty())
                            <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">Módulos: {{ $plano->modulos->pluck('module_name')->unique()->join(', ') }}</p>
                        @endif

                        <label for="modalidade-{{ $plano->id }}" class="mt-5 text-sm font-semibold text-slate-700 dark:text-slate-200">Modalidade</label>
                        <select id="modalidade-{{ $plano->id }}" name="modalidade_pagamento" class="mt-2 rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                            <option value="monthly">Mensal - {{ number_format((float) $plano->preco_mensal, 2, ',', '.') }} Kz</option>
                            <option value="trimestral">Trimestral - {{ number_format((float) $plano->preco_trimestral, 2, ',', '.') }} Kz</option>
                            <option value="semestral">Semestral - {{ number_format((float) $plano->preco_semestral, 2, ',', '.') }} Kz</option>
                            <option value="annual">Anual - {{ number_format((float) $plano->preco_anual, 2, ',', '.') }} Kz</option>
                        </select>

                        <button type="submit" class="mt-5 inline-flex min-h-11 items-center justify-center rounded-lg bg-blue-700 px-4 text-sm font-semibold text-white transition hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            {{ $activeSubscription ? 'Confirmar próximo período' : ($plano->isGratuito() ? 'Activar plano' : 'Continuar para checkout') }}
                        </button>
                    </form>
                @empty
                    <div class="rounded-lg border border-dashed border-slate-300 bg-white p-5 text-sm text-slate-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                        Nenhum plano activo encontrado. Contacte a equipa Logigate para activar a sua subscrição.
                    </div>
                @endforelse
            </section>
        @endif
        <section class="rounded-lg border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-900">
            <h2 class="font-semibold text-slate-900 dark:text-white">Histórico de pagamentos</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-700 dark:text-slate-300">
                    <thead><tr><th class="p-2">Data</th><th class="p-2">Transacção</th><th class="p-2">Método</th><th class="p-2">Total</th><th class="p-2">Estado</th><th class="p-2">Comprovativo</th></tr></thead>
                    <tbody>
                        @forelse($payments as $payment)
                            <tr class="border-t border-slate-200 dark:border-slate-700">
                                <td class="p-2">{{ $payment->created_at?->format('d/m/Y H:i') }}</td><td class="p-2 font-mono">{{ $payment->merchant_transaction_id }}</td><td class="p-2">{{ $payment->method }}</td>
                                <td class="p-2">{{ number_format($payment->amount, 2, ',', '.') }} {{ $payment->currency }}</td>
                                <td class="p-2">{{ ['paid' => 'Pago', 'pending' => 'Pendente', 'processing' => 'Em confirmação', 'failed' => 'Falhado', 'expired' => 'Expirado'][$payment->status] ?? $payment->status }}</td>
                                <td class="p-2">@if($payment->status === 'paid')<a href="{{ route('billing.receipt', $payment->id) }}" class="text-blue-700">Ver comprovativo</a>@endif</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="p-2">Ainda não existem pagamentos registados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $payments->links() }}</div>
        </section>
    </div>
</x-app-layout>
