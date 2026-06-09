<x-app-layout>
    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Empresa', 'url' => route('empresas.show', $empresa->id)],
        ['name' => 'Editar Empresa', 'url' => '']
    ]" separator="/" />

    <div class="container-fluid mt-4">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="row">
            <div class="col-lg-4 mb-4">
                <livewire:empresa.empresa-logo :empresa="$empresa" />
            </div>

            <div class="col-lg-8 mb-4">
                <livewire:empresa.empresa-profile :empresa="$empresa" />
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <livewire:empresa.empresa-users :empresa="$empresa" />
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <strong>Contas bancárias</strong>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('banco.inserir') }}" method="post" class="row g-3">
                            @csrf
                            <div class="col-md-4">
                                <label for="banco-select" class="form-label">Banco</label>
                                <select name="banco" id="banco-select" class="form-control">
                                   @foreach($listaBancos as $codigo => $nomeBanco)
                                        <option value="{{ $codigo }}">{{ $nomeBanco }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="iban-input" class="form-label">IBAN</label>
                                <input type="text" id="iban-input" name="iban-input" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label for="conta-input" class="form-label">Conta</label>
                                <input type="text" id="conta-input" name="conta-input" class="form-control">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Adicionar conta</button>
                            </div>
                        </form>

                        <div class="table-responsive mt-4">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Banco</th>
                                        <th>IBAN</th>
                                        <th>Conta</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($contas as $conta)
                                        <tr>
                                            <td>{{ $conta->code_banco }}</td>
                                            <td>{{ $conta->iban }}</td>
                                            <td>{{ $conta->conta }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3">Nenhuma conta bancária cadastrada.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
