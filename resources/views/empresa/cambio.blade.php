<x-app-layout>
    <div class="mx-auto max-w-6xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
        <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Configurações</p>
                    <h1 class="mt-1 text-2xl font-bold text-slate-950 dark:text-white">Câmbios</h1>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600 dark:text-slate-300">
                        Actualize a taxa de câmbio utilizada pelos fluxos existentes.
                    </p>
                </div>
                <a href="{{ route('configuracoes.index') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                    <i class="fas fa-arrow-left"></i>
                    Configurações
                </a>
            </div>
        </section>

        @if (session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm font-medium text-green-800 dark:border-green-900/60 dark:bg-green-950/40 dark:text-green-200">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-200">
                {{ $errors->first() }}
            </div>
        @endif

        @php($selectedCambio = $cambios->first())

        <section class="grid gap-6 lg:grid-cols-3">
            <form action="{{ route('cambios.update') }}" method="POST" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900 lg:col-span-1">
                @csrf
                @method('PUT')

                <div class="space-y-4">
                    <div>
                        <label for="moeda_txt" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Moeda</label>
                        <select name="moeda_txt" id="moeda_txt" class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                            <option value="">Selecionar</option>
                            @foreach($cambios as $cambio)
                                <option value="{{ $cambio->moeda }}" @selected(old('moeda_txt', $selectedCambio?->moeda) === $cambio->moeda)>
                                    {{ $cambio->moeda }}
                                </option>
                            @endforeach
                        </select>
                        @error('moeda_txt') <p class="mt-1 text-sm text-red-600 dark:text-red-300">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="cambio" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Câmbio</label>
                        <input type="text" name="cambio" id="cambio" value="{{ old('cambio', $selectedCambio?->cambio) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white" required>
                        @error('cambio') <p class="mt-1 text-sm text-red-600 dark:text-red-300">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="data_cambio" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Data do câmbio</label>
                        <input type="date" name="data_cambio" id="data_cambio" value="{{ old('data_cambio', $selectedCambio?->data_cambio) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white" required>
                        @error('data_cambio') <p class="mt-1 text-sm text-red-600 dark:text-red-300">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-800">
                        <i class="fas fa-save"></i>
                        Atualizar câmbio
                    </button>
                </div>
            </form>

            <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900 lg:col-span-2">
                <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <h2 class="font-semibold text-slate-950 dark:text-white">Moedas registadas</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                        <thead class="bg-slate-50 dark:bg-slate-800">
                            <tr>
                                <th class="px-5 py-3 text-left font-semibold text-slate-700 dark:text-slate-200">Moeda</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-700 dark:text-slate-200">Câmbio</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-700 dark:text-slate-200">Última actualização</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($cambios as $cambio)
                                <tr>
                                    <td class="px-5 py-3 text-slate-800 dark:text-slate-200">{{ $cambio->moeda }}</td>
                                    <td class="px-5 py-3 text-slate-700 dark:text-slate-300">{{ $cambio->cambio }}</td>
                                    <td class="px-5 py-3 text-slate-700 dark:text-slate-300">{{ $cambio->data_cambio ?? 'N/D' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-5 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                                        Nenhum câmbio registado.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
