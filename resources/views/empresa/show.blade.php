<x-app-layout>
    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Empresa', 'url' => route('empresas.edit', $empresa->id)],
        ['name' => 'Visualisar Empresa', 'url' => '']
    ]" separator="/" />
    <div class="container">
        <div class="card">
            <div class="card-header">
                Detalhes da Empresa
            </div>
            <div class="card-body">
                <p><strong>Nome da Empresa:</strong> {{ $empresa->Empresa }}</p>
                <p><strong>Atividade Comercial:</strong> {{ $empresa->ActividadeComercial }}</p>
                <p><strong>NIF:</strong> {{ $empresa->NIF }}</p>
                <!-- Adicione outros campos da empresa conforme necessário -->
            </div>
        </div>
    </div>
</x-app-layout>