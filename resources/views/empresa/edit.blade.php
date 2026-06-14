<x-app-layout>
    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Empresa', 'url' => route('empresas.show', $empresa->id)],
        ['name' => 'Editar Empresa', 'url' => '']
    ]" separator="/" />

    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        {{-- Mensagem de sucesso --}}
        @if (session('success'))
            <div class="mb-6 rounded-lg border-l-4 border-green-500 bg-green-50 p-4 text-green-800 shadow-sm">
                <div class="flex items-center gap-2">
                    <i class="fas fa-check-circle text-green-600"></i>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- Coluna esquerda: Logo da empresa --}}
            <div class="lg:col-span-1">
                <details class="group rounded-xl border border-slate-200 bg-white shadow-sm" open>
                    <summary class="flex cursor-pointer items-center justify-between rounded-t-xl p-5 text-lg font-semibold text-slate-900 hover:bg-slate-50">
                        <span class="flex items-center gap-2">
                            <i class="fas fa-building text-blue-600"></i>
                            Perfil da Empresa
                        </span>
                        <span class="text-slate-400 transition-transform group-open:rotate-180">
                            <i class="fas fa-chevron-down"></i>
                        </span>
                    </summary>
                    <div class="border-t border-slate-100 p-5">
                        <livewire:empresa.empresa-logo :empresa="$empresa" />
                    </div>
                </details>
            </div>

            {{-- Coluna direita: Detalhes da empresa --}}
            <div class="lg:col-span-2">
                <details class="group rounded-xl border border-slate-200 bg-white shadow-sm" open>
                    <summary class="flex cursor-pointer items-center justify-between rounded-t-xl p-5 text-lg font-semibold text-slate-900 hover:bg-slate-50">
                        <span class="flex items-center gap-2">
                            <i class="fas fa-info-circle text-blue-600"></i>
                            Detalhes da Empresa
                        </span>
                        <span class="text-slate-400 transition-transform group-open:rotate-180">
                            <i class="fas fa-chevron-down"></i>
                        </span>
                    </summary>
                    <div class="border-t border-slate-100 p-5">
                        <livewire:empresa.empresa-profile :empresa="$empresa" />
                    </div>
                </details>
            </div>
        </div>

        {{-- Utilizadores da empresa --}}
        <div class="mt-6">
            <details class="group rounded-xl border border-slate-200 bg-white shadow-sm" open>
                <summary class="flex cursor-pointer items-center justify-between rounded-t-xl p-5 text-lg font-semibold text-slate-900 hover:bg-slate-50">
                    <span class="flex items-center gap-2">
                        <i class="fas fa-users text-blue-600"></i>
                        Utilizadores da Empresa
                    </span>
                    <span class="text-slate-400 transition-transform group-open:rotate-180">
                        <i class="fas fa-chevron-down"></i>
                    </span>
                </summary>
                <div class="border-t border-slate-100 p-5">
                    <livewire:empresa.empresa-users :empresa="$empresa" />
                </div>
            </details>
        </div>

        {{-- Contas Bancárias (adicionado com estrutura consistente) --}}
        <div class="mt-6">
            <details class="group rounded-xl border border-slate-200 bg-white shadow-sm">
                <summary class="flex cursor-pointer items-center justify-between rounded-t-xl p-5 text-lg font-semibold text-slate-900 hover:bg-slate-50">
                    <span class="flex items-center gap-2">
                        <i class="fas fa-university text-blue-600"></i>
                        Contas Bancárias
                    </span>
                    <span class="text-slate-400 transition-transform group-open:rotate-180">
                        <i class="fas fa-chevron-down"></i>
                    </span>
                </summary>
                <div class="border-t border-slate-100 p-5">
                    {{-- Aqui pode incluir um Livewire específico ou um componente estático --}}
                    <p class="text-sm text-slate-500">
                        <i class="fas fa-info-circle mr-1"></i> 
                        Nenhuma conta bancária registada. Clique em "Adicionar Conta" para começar.
                    </p>
                    {{-- Exemplo de botão para adicionar (caso exista componente) --}}
                    <div class="mt-4">
                        <livewire:empresa.empresa-contas-bancarias :empresa="$empresa" />
                    </div>
                </div>
            </details>
        </div>
    </div>
</x-app-layout>