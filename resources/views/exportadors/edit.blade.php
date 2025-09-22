<x-app-layout>
    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Exportadores', 'url' => route('exportadors.index')],
        ['name' => $exportador->Exportador, 'url' => route('exportadors.show', $exportador->id)],
        ['name' => 'Editar Exportador', 'url' => route('exportadors.edit', $exportador->id)]
    ]" separator="/" />
    
    <div class="container mx-auto px-4 py-8">
        <div class="mb-6 d-flex justify-content-between">
            <div class="btn-group float-right">
                <a class="btn btn-outline-secondary" href="{{ route('exportadors.index') }}">
                    <i class="fas fa-search"></i> {{ __('Pesquisar') }}
                </a>
                <a class="btn btn-outline-primary" href=" {{ route('exportadors.create') }} " class="btn btn-outline-secondary">
                    <i class="fas fa-plus-o"></i> {{ __('Novo Exportador') }}
                </a>
                <div class="btn-group" role="group">
                    <button id="btnGroupDrop1" type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-filter"></i> {{ __('Opções') }}
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                        
                        <li>
                            <a href="{{ route('exportadors.show', $exportador->id) }}" class="button dropdown-item">
                                <i class="fas fa-eye"></i> {{ __('Visualizar') }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Formulário de edição -->
        <form action="{{ route('exportadors.update', $exportador->id) }}" method="POST" class="bg-white rounded-lg shadow p-6">
            @csrf
            @method('PUT')

            <!-- Linha 1: AccountID e ExportadorTaxID -->
            <div class="flex flex-wrap -mx-2">
                <!-- AccountID -->
                <div class="w-full md:w-1/2 px-2 mb-4">
                    <label for="AccountID" class="block text-sm font-medium text-gray-700">
                        <i class="fas fa-id-card mr-2"></i> Account ID
                    </label>
                    <input type="text" name="AccountID" id="AccountID" value="{{ old('AccountID', $exportador->AccountID) }}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" required>
                </div>

                <!-- ExportadorTaxID -->
                <div class="w-full md:w-1/2 px-2 mb-4">
                    <label for="ExportadorTaxID" class="block text-sm font-medium text-gray-700">
                        <i class="fas fa-file-invoice-dollar mr-2"></i> Tax ID
                    </label>
                    <input type="text" name="ExportadorTaxID" id="ExportadorTaxID" value="{{ old('ExportadorTaxID', $exportador->ExportadorTaxID) }}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" required>
                </div>
            </div>

            <!-- Linha 2: Exportador e Endereco -->
            <div class="flex flex-wrap -mx-2">
                <!-- Exportador -->
                <div class="w-full md:w-1/2 px-2 mb-4">
                    <label for="Exportador" class="block text-sm font-medium text-gray-700">
                        <i class="fas fa-user-tie mr-2"></i> Nome do Exportador
                    </label>
                    <input type="text" name="Exportador" id="Exportador" value="{{ old('Exportador', $exportador->Exportador) }}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" required>
                </div>

                <!-- Endereco -->
                <div class="w-full md:w-1/2 px-2 mb-4">
                    <label for="Endereco" class="block text-sm font-medium text-gray-700">
                        <i class="fas fa-map-marker-alt mr-2"></i> Endereço
                    </label>
                    <input type="text" name="Endereco" id="Endereco" value="{{ old('Endereco', $exportador->Endereco) }}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" required>
                </div>
            </div>

            <!-- Linha 3: Telefone e Email -->
            <div class="flex flex-wrap -mx-2">
                <!-- Telefone -->
                <div class="w-full md:w-1/2 px-2 mb-4">
                    <label for="Telefone" class="block text-sm font-medium text-gray-700">
                        <i class="fas fa-phone mr-2"></i> Telefone
                    </label>
                    <input type="text" name="Telefone" id="Telefone" value="{{ old('Telefone', $exportador->Telefone) }}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" required>
                </div>

                <!-- Email -->
                <div class="w-full md:w-1/2 px-2 mb-4">
                    <label for="Email" class="block text-sm font-medium text-gray-700">
                        <i class="fas fa-envelope mr-2"></i> Email
                    </label>
                    <input type="email" name="Email" id="Email" value="{{ old('Email', $exportador->Email) }}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" required>
                </div>
            </div>

            <!-- Linha 4: Website e Pais -->
            <div class="flex flex-wrap -mx-2">
                <!-- Website -->
                <div class="w-full md:w-1/2 px-2 mb-4">
                    <label for="Website" class="block text-sm font-medium text-gray-700">
                        <i class="fas fa-globe mr-2"></i> Website
                    </label>
                    <input type="text" name="Website" id="Website" value="{{ old('Website', $exportador->Website) }}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                </div>

                <!-- Pais -->
                <div class="w-full md:w-1/2 px-2 mb-4">
                    <label for="Pais" class="block text-sm font-medium text-gray-700">
                        <i class="fas fa-flag mr-2"></i> País
                    </label>
                    <select name="Pais" id="Pais" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 select2" required>
                        <option value="" disabled>Selecione um país</option>
                        @foreach ($paises as $codigo => $nome)
                            <option value="{{ $codigo }}" data-flag="{{ strtolower($codigo) }}" {{ old('Pais', $exportador->Pais) == $codigo ? 'selected' : '' }}>
                                <span class="flag-icon flag-icon-{{ strtolower($codigo) }}"></span> {{ $nome }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Linha 5: Cidade -->
            <div class="flex flex-wrap -mx-2">
                <!-- Cidade -->
                <div class="w-full px-2 mb-4">
                    <label for="Cidade" class="block text-sm font-medium text-gray-700">
                        <i class="fas fa-city mr-2"></i> Cidade
                    </label>
                    <input type="text" name="Cidade" id="Cidade" value="{{ old('Cidade', $exportador->Cidade) }}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" required>
                </div>
            </div>

            <!-- Botão de envio -->
            <div class="mt-6 text-right">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                    <i class="fas fa-save mr-2"></i> Salvar Alterações
                </button>
            </div>
        </form>
    </div>

    <!-- Script para inicializar o Select2 -->
    <script>
        $(document).ready(function() {
            $('#Pais').select2({
                templateResult: formatOption, // Personaliza a exibição das opções
                templateSelection: formatOption // Personaliza a exibição do item selecionado
            });

            function formatOption(option) {
                if (!option.id) {
                    return option.text;
                }
                var flagUrl = "https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.5.0/flags/4x3/" + $(option.element).data('flag') + ".svg";
                var $option = $(
                    '<span><img src="' + flagUrl + '" class="flag-icon" style="width: 20px; margin-right: 8px;" /> ' + option.text + '</span>'
                );
                return $option;
            }
        });
    </script>
</x-app-layout>