<x-app-layout>
    <div class="[&_ol]:flex-wrap [&_ol]:gap-y-1 [&_li]:min-w-0">
    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Processos', 'url' => route('processos.index')],
        ['name' => $processo->NrProcesso, 'url' => route('processos.show', $processo->id)],
        ['name' => 'Editar Processo']
    ]" />
    </div>

    @php
        $tabs = ['info' => 'Informações'];
        if (\App\Support\BusinessAuthorization::allows(auth()->user(), 'mercadorias.view')) {
            $tabs['mercadoria'] = 'Mercadorias';
        }
        $tabs['despesas'] = 'Despesas e imposições';
        if (auth()->user()->can('simulate', $processo) && strtoupper((string) $processo->tipoDeclaracao?->abrev) === 'IM' && !$processo->isExportacaoCrude()) {
            $tabs['simulacao'] = 'Simulação tributária';
        }
        if (auth()->user()->can('view', $processo)) {
            $tabs['docs'] = 'Documentos';
        }
    @endphp
    <div class="mx-auto max-w-8xl space-y-6 py-6"
         x-data="{
             tab: @js(is_string(request('tab')) && array_key_exists(request('tab'), $tabs) ? request('tab') : 'info'),
             selectTab(value) {
                 this.tab = value;
                 const url = new URL(window.location.href);
                 url.searchParams.set('tab', value);
                 window.history.replaceState({}, '', url);
             }
         }">
        <header class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <div class="mb-2 flex flex-wrap items-center gap-2 text-xs font-medium">
                        <span class="rounded-full bg-blue-50 px-3 py-1 text-blue-700">Processo aduaneiro</span>
                        @if ($processo->isExportacaoCrude())
                            <span class="rounded-full bg-yellow-50 px-3 py-1 text-yellow-800">Exportação CRUD</span>
                        @endif
                        <span class="rounded-full bg-gray-100 px-3 py-1 text-gray-700">{{ $processo->Estado }}</span>
                    </div>
                    <h1 class="break-words text-2xl font-bold text-gray-900">{{ $processo->NrProcesso }}</h1>
                    <p class="mt-1 text-sm text-gray-500">Actualize os dados, confira as despesas e organize os documentos do processo.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    @can('view', $processo)
                    <a href="{{ route('processos.tarifa', ['processo' => $processo->id]) }}" class="rounded-lg border border-blue-200 px-4 py-2 text-sm font-medium text-blue-700">Controlo de custos</a>
                    @endcan
                    <a href="{{ route('processos.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Pesquisar</a>
                    <a href="{{ route('processos.show', $processo->id) }}" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700">Visualizar processo</a>
                    <div x-data="{ open: false }" class="relative" @keydown.escape.stop.prevent="open = false; $refs.optionsButton.focus()">
                        <button type="button" x-ref="optionsButton" @click="open = !open" :aria-expanded="open.toString()" aria-controls="processo-edit-options"
                                class="flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Opções <span aria-hidden="true">⌄</span>
                        </button>
                        <div id="processo-edit-options" x-show="open" x-cloak @click.outside="open = false"
                             class="absolute right-0 z-50 mt-2 w-56 overflow-hidden rounded-xl border border-gray-200 bg-white py-1 shadow-lg">
                            <a href="{{ route('integracoes.facturacao-hongayetu.emitir-ft', ['processo_id' => $processo->id]) }}"
                               class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-50">Emitir factura</a>
                            @can('exportXml', $processo)
                                <a href="{{ route('gerar.xml', ['IdProcesso' => $processo->id]) }}" target="_blank" rel="noopener"
                                   class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-50">Exportar ASYCUDA</a>
                            @endcan
                            @can('print', $processo)
                                <form action="{{ route('processo.print.requisicao', ['IdProcesso' => $processo->id]) }}" method="POST" target="_blank">
                                    @csrf
                                    <button type="submit" class="block w-full px-4 py-3 text-left text-sm text-gray-700 hover:bg-gray-50">Requisição PDF</button>
                                </form>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <div class="rounded-xl border border-gray-200 bg-white p-1 shadow-sm">
            <div role="tablist" aria-label="Secções do processo" class="flex flex-wrap gap-1"
                 @keydown.arrow-right.prevent="const next = $event.target.nextElementSibling || $el.firstElementChild; next.focus(); next.click()"
                 @keydown.arrow-left.prevent="const previous = $event.target.previousElementSibling || $el.lastElementChild; previous.focus(); previous.click()"
                 @keydown.home.prevent="$el.firstElementChild.focus(); $el.firstElementChild.click()"
                 @keydown.end.prevent="$el.lastElementChild.focus(); $el.lastElementChild.click()">
                @foreach ($tabs as $key => $label)
                    <button type="button" role="tab" id="processo-tab-{{ $key }}" aria-controls="processo-panel-{{ $key }}"
                            @click="selectTab('{{ $key }}')" :aria-selected="(tab === '{{ $key }}').toString()" :tabindex="tab === '{{ $key }}' ? 0 : -1"
                            :class="tab === '{{ $key }}' ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'"
                            class="flex-1 whitespace-nowrap rounded-lg px-3 py-3 text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:flex-none sm:px-5">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        <section id="processo-panel-info" role="tabpanel" aria-labelledby="processo-tab-info" x-show="tab === 'info'" x-cloak>
            @if ($processo->isExportacaoCrude())
                <livewire:processo.exportacao-crude-edit :processo="$processo" />
            @else
                <livewire:processo.processo-edit :processo="$processo" />
            @endif
        </section>
        @if (isset($tabs['mercadoria']))
        <section id="processo-panel-mercadoria" role="tabpanel" aria-labelledby="processo-tab-mercadoria" x-show="tab === 'mercadoria'" x-cloak>
            <livewire:mercadorias.index context="processo" parentId="{{ $processo->id }}" />
        </section>
        @endif
        <section id="processo-panel-despesas" role="tabpanel" aria-labelledby="processo-tab-despesas" x-show="tab === 'despesas'" x-cloak>
            <livewire:processo.despesas :processo="$processo" />
        </section>
        @if (isset($tabs['simulacao']))
        <section id="processo-panel-simulacao" role="tabpanel" aria-labelledby="processo-tab-simulacao" x-show="tab === 'simulacao'" x-cloak>
            <livewire:pauta-aduaneira.simulador-processo :processo-id="$processo->id" />
        </section>
        @endif
        @if (isset($tabs['docs']))
        <section id="processo-panel-docs" role="tabpanel" aria-labelledby="processo-tab-docs" x-show="tab === 'docs'" x-cloak>
            <livewire:arquivo.documentos-do-modulo contexto="processo" :entidade-id="$processo->id" />
        </section>
        @endif
    </div>
</x-app-layout>
