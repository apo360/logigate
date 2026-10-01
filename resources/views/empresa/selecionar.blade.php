<x-guest-layout>
    <div class="mx-auto max-w-lg p-6">
        <h1 class="mb-4 text-xl font-semibold">Selecionar empresa</h1>
        <form method="POST" action="{{ route('empresa-context.update') }}">
            @csrf
            <label for="empresa_id">Empresa ativa</label>
            <select id="empresa_id" name="empresa_id" required class="mt-2 block w-full rounded border p-2">
                <option value="">Selecione uma empresa</option>
                @foreach ($empresas as $empresa)
                    <option value="{{ $empresa->id }}" @selected((int) $activeEmpresaId === (int) $empresa->id)>{{ $empresa->Empresa }}</option>
                @endforeach
            </select>
            @error('empresa_id') <p>{{ $message }}</p> @enderror
            @if ($empresas->isEmpty())
                <p class="mt-4">Não tem acesso a nenhuma empresa.</p>
            @else
                <button type="submit" class="mt-4 rounded bg-blue-700 px-4 py-2 text-white">Continuar</button>
            @endif
        </form>
        <form method="POST" action="{{ route('logout') }}" class="mt-4">
            @csrf
            <button type="submit">Sair</button>
        </form>
    </div>
</x-guest-layout>
