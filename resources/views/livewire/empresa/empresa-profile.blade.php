<div class="rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
    <div class="border-b border-slate-100 px-5 py-4 dark:border-slate-800">
        <h3 class="flex items-center gap-2 text-base font-semibold text-slate-900 dark:text-white">
            <i class="fas fa-building text-blue-600"></i>
            Informação da empresa
        </h3>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Dados fiscais, contacto e endereço usados em documentos e operações.</p>
    </div>

    <form wire:submit.prevent="update" class="space-y-6 p-5">
        @if (session()->has('message'))
            <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm font-medium text-green-800 dark:border-green-900/60 dark:bg-green-950/40 dark:text-green-200">
                {{ session('message') }}
            </div>
        @endif

        <section class="space-y-4">
            <div>
                <h4 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Dados fiscais</h4>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label for="empresa-nome" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Nome da empresa</label>
                    <input id="empresa-nome" wire:model.defer="Empresa" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white @error('Empresa') border-red-500 ring-red-500 @enderror">
                    @error('Empresa') <p class="mt-1 text-sm text-red-600 dark:text-red-300">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="empresa-nif" class="block text-sm font-medium text-slate-700 dark:text-slate-200">NIF</label>
                    <input id="empresa-nif" wire:model.defer="NIF" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white @error('NIF') border-red-500 ring-red-500 @enderror">
                    @error('NIF') <p class="mt-1 text-sm text-red-600 dark:text-red-300">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="empresa-cedula" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Cédula</label>
                    <input id="empresa-cedula" wire:model.defer="Cedula" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white @error('Cedula') border-red-500 ring-red-500 @enderror">
                    @error('Cedula') <p class="mt-1 text-sm text-red-600 dark:text-red-300">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="empresa-designacao" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Designação</label>
                    <select id="empresa-designacao" wire:model.defer="Designacao" class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                        <option value="Despachante Oficial">Despachante Oficial</option>
                        <option value="Praticante">Praticante</option>
                        <option value="Outro">Outro</option>
                    </select>
                    @error('Designacao') <p class="mt-1 text-sm text-red-600 dark:text-red-300">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="empresa-atividade" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Actividade comercial</label>
                    <input id="empresa-atividade" wire:model.defer="ActividadeComercial" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    @error('ActividadeComercial') <p class="mt-1 text-sm text-red-600 dark:text-red-300">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        <section class="space-y-4 border-t border-slate-100 pt-5 dark:border-slate-800">
            <h4 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Contacto</h4>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="empresa-email" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Email</label>
                    <input id="empresa-email" wire:model.defer="Email" type="email" class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    @error('Email') <p class="mt-1 text-sm text-red-600 dark:text-red-300">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="empresa-dominio" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Domínio</label>
                    <input id="empresa-dominio" wire:model.defer="Dominio" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    @error('Dominio') <p class="mt-1 text-sm text-red-600 dark:text-red-300">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="empresa-movel" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Contacto móvel</label>
                    <input id="empresa-movel" wire:model.defer="Contacto_movel" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    @error('Contacto_movel') <p class="mt-1 text-sm text-red-600 dark:text-red-300">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="empresa-fixo" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Contacto fixo</label>
                    <input id="empresa-fixo" wire:model.defer="Contacto_fixo" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    @error('Contacto_fixo') <p class="mt-1 text-sm text-red-600 dark:text-red-300">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        <section class="space-y-4 border-t border-slate-100 pt-5 dark:border-slate-800">
            <h4 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Endereço e documentos</h4>
            <div class="grid gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label for="empresa-endereco" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Endereço completo</label>
                    <input id="empresa-endereco" wire:model.defer="Endereco_completo" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    @error('Endereco_completo') <p class="mt-1 text-sm text-red-600 dark:text-red-300">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="empresa-provincia" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Província</label>
                    <input id="empresa-provincia" wire:model.defer="Provincia" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    @error('Provincia') <p class="mt-1 text-sm text-red-600 dark:text-red-300">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="empresa-cidade" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Cidade</label>
                    <input id="empresa-cidade" wire:model.defer="Cidade" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    @error('Cidade') <p class="mt-1 text-sm text-red-600 dark:text-red-300">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="empresa-cod-factura" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Código de factura</label>
                    <input id="empresa-cod-factura" wire:model.defer="CodFactura" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    @error('CodFactura') <p class="mt-1 text-sm text-red-600 dark:text-red-300">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="empresa-cod-processo" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Código de processo</label>
                    <input id="empresa-cod-processo" wire:model.defer="CodProcesso" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    @error('CodProcesso') <p class="mt-1 text-sm text-red-600 dark:text-red-300">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2">
                    <label for="empresa-slogan" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Slogan</label>
                    <input id="empresa-slogan" wire:model.defer="Slogan" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    @error('Slogan') <p class="mt-1 text-sm text-red-600 dark:text-red-300">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        <div class="flex justify-end border-t border-slate-100 pt-5 dark:border-slate-800">
            <button type="submit" wire:loading.attr="disabled" wire:target="update" class="inline-flex min-h-10 items-center gap-2 rounded-lg bg-blue-700 px-5 text-sm font-semibold text-white shadow-sm hover:bg-blue-800 disabled:cursor-not-allowed disabled:opacity-70">
                <i class="fas fa-spinner fa-pulse" wire:loading wire:target="update"></i>
                <i class="fas fa-save" wire:loading.remove wire:target="update"></i>
                <span wire:loading.remove wire:target="update">Salvar empresa</span>
                <span wire:loading wire:target="update">A guardar...</span>
            </button>
        </div>
    </form>
</div>
