<div>
    {{-- To attain knowledge, add things every day; To attain wisdom, subtract things every day. --}}
    <!-- 
    4. Subscrição e Pagamentos
        Funcionalidades:

        Plano actual
        Estado da subscrição
        Data de renovação
        Histórico de pagamentos
        Alterar plano
        Cancelar plano
        Limites do plano
        Módulos activos
-->

    <x-slot name="header">
        <span class="text-sm font-semibold text-slate-900">Escolher plano</span>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-5 px-4 py-6 sm:px-6 lg:px-0">
        <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Subscrição</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-950">Escolha um plano para continuar</h1>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
                {{ $empresa->Empresa }} ainda não tem uma subscrição activa. Planos pagos seguem para checkout AppyPay; planos gratuitos são activados imediatamente.
            </p>
        </section>

        @if($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse($planos as $plano)
                <form method="POST" action="{{ route('billing.start') }}" class="flex min-h-full flex-col rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    @csrf
                    <input type="hidden" name="plano_id" value="{{ $plano->id }}">

                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-bold text-slate-950">{{ $plano->nome }}</h2>
                            <p class="mt-1 text-sm leading-6 text-slate-600">{{ $plano->descricao }}</p>
                        </div>
                        @if($plano->destaque)
                            <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">Popular</span>
                        @endif
                    </div>

                    <div class="mt-4 grid gap-2 text-sm text-slate-600">
                        <div>Utilizadores: <strong class="text-slate-900">{{ $plano->limite_utilizadores }}</strong></div>
                        <div>Processos: <strong class="text-slate-900">{{ $plano->limite_processos }}</strong></div>
                        <div>Armazenamento: <strong class="text-slate-900">{{ $plano->limite_armazenamento_gb }} GB</strong></div>
                    </div>

                    <label for="modalidade-{{ $plano->id }}" class="mt-5 text-sm font-semibold text-slate-700">Modalidade</label>
                    <select id="modalidade-{{ $plano->id }}" name="modalidade_pagamento" class="mt-2 rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="monthly">Mensal - {{ number_format((float) $plano->preco_mensal, 2, ',', '.') }} Kz</option>
                        <option value="semestral">Semestral - {{ number_format((float) $plano->preco_semestral, 2, ',', '.') }} Kz</option>
                        <option value="annual">Anual - {{ number_format((float) $plano->preco_anual, 2, ',', '.') }} Kz</option>
                    </select>

                    <button type="submit" class="mt-5 inline-flex min-h-11 items-center justify-center rounded-lg bg-blue-700 px-4 text-sm font-semibold text-white transition hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        {{ ($plano->is_free ?? false) ? 'Activar plano' : 'Continuar para checkout' }}
                    </button>
                </form>
            @empty
                <div class="rounded-lg border border-dashed border-slate-300 bg-white p-5 text-sm text-slate-600">
                    Nenhum plano activo encontrado. Contacte a equipa Logigate para activar a sua subscrição.
                </div>
            @endforelse
        </section>
    </div>

</div>
