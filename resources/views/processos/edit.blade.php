<x-app-layout>
    <!-- BREADCRUMB -->
    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Processos', 'url' => route('processos.index')],
        ['name' => $processo->NrProcesso, 'url' => route('processos.show', $processo->id)],
        ['name' => 'Editar Processo']
    ]"/>

    <div class="py-6 max-w-7xl mx-auto" x-data="{ tab: @js(request('tab', 'info')) }">

        {{-- CABEÇALHO DE AÇÕES --}}
        <div class="flex items-center justify-between mb-6">
            <div class="flex gap-2">
                <a href="{{ route('processos.index') }}" class="btn-secondary">Pesquisar</a>
                <a href="{{ route('processos.show', $processo->id) }}" class="btn-secondary">Visualizar</a>

                {{-- Dropdown Opções --}}
                <div x-data="{ open:false }" class="relative">
                    <button @click.stop="open = !open" class="btn-secondary">
                        Opções
                    </button>

                    <div x-show="open" @click.outside="open = false"
                         class="absolute right-0 mt-2 w-56 bg-white shadow rounded-lg border z-50">

                        <a href="{{ route('documentos.create', ['processo_id' => $processo->id]) }}"
                           class="dropdown-item">Factura</a>

                        <a href="{{ route('gerar.xml', ['IdProcesso' => $processo->id]) }}"
                           target="_blank" class="dropdown-item">Asycuda</a>

                        <form action="{{ route('processo.print.requisicao', ['IdProcesso' => $processo->id]) }}"
                              method="POST" target="_blank">
                            @csrf
                            <button type="submit" class="dropdown-item text-left w-full">Requisição PDF</button>
                        </form>

                        <a href="{{ route('gerar.txt', ['IdProcesso' => $processo->id]) }}"
                           target="_blank" class="dropdown-item">Licenciamento TXT</a>
                    </div>
                </div>
            </div>
        </div>


        {{-- TABS MODERNOS --}}
        <div class="border-b mb-6 flex gap-6 text-sm font-medium">
            <button @click="tab='info'"       :class="tab==='info' ? 'tab-active' : 'tab'">Página Info</button>
            <button @click="tab='mercadoria'" :class="tab==='mercadoria' ? 'tab-active' : 'tab'">Mercadorias</button>
            <button @click="tab='despesas'"   :class="tab==='despesas' ? 'tab-active' : 'tab'">Despesas & Imposições</button>
            <button @click="tab='simulacao'"   :class="tab==='simulacao' ? 'tab-active' : 'tab'">Simulação Aduaneira</button>
            <button @click="tab='docs'"       :class="tab==='docs' ? 'tab-active' : 'tab'">Documentos</button>
            <button @click="tab='resumo'"     :class="tab==='resumo' ? 'tab-active' : 'tab'">Resumo</button>
        </div>

        {{-- CONTEÚDO DAS TABS --}}
        {{-- Usando x-cloak para evitar flicker na troca de tabs --}}

        {{-- TAB 1 — INFO --}}
        <div x-show="tab === 'info'" x-cloak>
            <livewire:processo.processo-edit :processo="$processo" />
        </div>

        {{-- TAB 2 — MERCADORIAS --}}
        <div x-show="tab === 'mercadoria'" x-cloak>
            <livewire:mercadorias.index context="processo" parentId="{{ $processo->id }}" />
        </div>

        <!-- TAB 3 — DESPESAS -->
        <div x-show="tab === 'despesas'" x-cloak>
            <!-- CORREÇÃO: Passar o model Processo em vez de apenas o ID -->
            <livewire:processo.despesas :processo="$processo" />
        </div>

        {{-- TAB 4 — SIMULAÇÃO ADUANEIRA --}}
        <div x-show="tab === 'simulacao'" x-cloak>
            <livewire:pauta-aduaneira.simulador-processo :processo-id="$processo->id" />
        </div>

        {{-- TAB 5 — DOCUMENTOS --}}
        <div x-show="tab === 'docs'" x-cloak>
            <livewire:arquivo.documentos-manager contexto="processo" :entidade-id="$processo->id" />
        </div>

        {{-- TAB 6 — RESUMO --}}
        <div x-show="tab === 'resumo'" x-cloak>
            <livewire:processo.resumo-asys :processo="$processo" />
        </div>

    </div>
</x-app-layout>

<style>
    /* Estilo para os tabs */
    .tab {
        @apply px-4 py-2 text-gray-600 hover:text-gray-900 hover:border-b-2 hover:border-gray-300 transition-colors;
    }
    
    .tab-active {
        @apply px-4 py-2 text-blue-600 border-b-2 border-blue-600 font-semibold;
    }
    
    .btn-secondary {
        @apply px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors;
    }
    
    .dropdown-item {
        @apply block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100;
    }
    
    /* Evitar flicker durante troca de tabs */
    [x-cloak] { display: none !important; }
</style>
