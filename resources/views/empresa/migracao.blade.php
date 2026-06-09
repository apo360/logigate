<x-app-layout>
    <x-slot name="header">
        <span class="text-sm font-semibold text-slate-900 dark:text-slate-100">Migrações / Importações</span>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-5">
        <livewire:empresa.empresa-migracoes />

        <section class="grid gap-4 md:grid-cols-3">
            <x-import-form route="{{ route('import.customers') }}" buttonText="Importar Clientes" texto="Importar dados de clientes para o sistema." />
            <x-import-form route="{{ route('import.exportadores') }}" buttonText="Importar Exportadores" texto="Importar dados dos exportadores para o sistema." />
            <x-import-form route="{{ route('import.processos') }}" buttonText="Importar Processos" texto="Importar processos para o sistema." />
        </section>

        @if(session('status'))
            <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                <ul class="list-inside list-disc">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <div class="mb-4 flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Status das importações</h2>
                    <p class="text-sm text-slate-600 dark:text-slate-300">Histórico dos ficheiros submetidos para esta empresa.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                    <thead class="bg-slate-50 dark:bg-slate-800">
                        <tr>
                            <th class="px-4 py-2 text-left font-semibold text-slate-700 dark:text-slate-200">Tipo</th>
                            <th class="px-4 py-2 text-left font-semibold text-slate-700 dark:text-slate-200">Estado</th>
                            <th class="px-4 py-2 text-left font-semibold text-slate-700 dark:text-slate-200">Criado em</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($imports as $import)
                            <tr>
                                <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ $import->type }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700">
                                        {{ $import->status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ $import->created_at?->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                                    Nenhuma importação registada.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
