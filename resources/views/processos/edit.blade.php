<x-app-layout>
    <!-- BREADCRUMB -->
    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Processos', 'url' => route('processos.index')],
        ['name' => $processo->NrProcesso, 'url' => route('processos.show', $processo->id)],
        ['name' => 'Editar Processo']
    ]"/>

    <div class="py-6 max-w-7xl mx-auto" x-data="{ tab: 'info' }">

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

                        <a href="{{ route('processo.print.requisicao', ['IdProcesso' => $processo->id]) }}"
                           target="_blank" class="dropdown-item">Requisição PDF</a>

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
            <button @click="tab='docs'"       :class="tab==='docs' ? 'tab-active' : 'tab'">Documentos</button>
            <button @click="tab='resumo'"     :class="tab==='resumo' ? 'tab-active' : 'tab'">Resumo</button>
        </div>

        {{-- CONTEÚDO DAS TABS --}}
        {{-- Usando x-cloak para evitar flicker na troca de tabs --}}

        {{-- TAB 1 — INFO --}}
        <div x-show="tab === 'info'" x-cloak>
            <livewire:processos.form :processo="$processo" mode="edit"/>
        </div>

        {{-- TAB 2 — MERCADORIAS --}}
        <div x-show="tab === 'mercadoria'" x-cloak>
            <livewire:mercadorias.index context="processo" parentId="{{ $processo->id }}" />
        </div>

        <!-- TAB 3 — DESPESAS -->
        <div x-show="tab === 'despesas'" x-cloak>
            <!-- CORREÇÃO: Passar o model Processo em vez de apenas o ID -->
            <livewire:processos.despesas :processo="$processo" />
        </div>

        {{-- TAB 4 — DOCUMENTOS --}}
        <div x-show="tab === 'docs'" x-cloak>
            @if(isset($documentos) && count($documentos) > 0)
            <div class="bg-white rounded-lg border shadow-sm p-6">
                <h3 class="text-lg font-semibold mb-4">Documentos do Processo</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($documentos as $documento)
                    <div class="border rounded-lg p-4 hover:bg-gray-50 transition-colors">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="font-medium text-gray-900">{{ $documento->tipo }}</h4>
                                <p class="text-sm text-gray-500 mt-1">
                                    {{ $documento->created_at->format('d/m/Y H:i') }}
                                </p>
                            </div>
                            <a href="{{ asset('storage/' . $documento->caminho) }}" 
                               target="_blank"
                               class="text-blue-600 hover:text-blue-800">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @else
            <div class="bg-white rounded-lg border shadow-sm p-12 text-center">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum documento anexado</h3>
                <p class="text-gray-500 mb-4">Adicione documentos ao processo</p>
                <a href="{{ route('documentos.create', ['processo_id' => $processo->id]) }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Adicionar Documento
                </a>
            </div>
            @endif
        </div>

        {{-- TAB 5 — RESUMO --}}
        <div x-show="tab === 'resumo'" x-cloak>
            <livewire:processos.resumo-asys :processo="$processo" />
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