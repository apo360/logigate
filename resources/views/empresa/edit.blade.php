<x-app-layout>
<x-breadcrumb :items="[
    ['name' => 'Dashboard', 'url' => route('dashboard')],
    ['name' => 'Empresa', 'url' => route('empresas.show', $empresa->id)],
    ['name' => 'Editar Empresa', 'url' => '']
]" separator="/" />

    <div class="row mt-4" style="padding-left: 20px;">
        <div class="col-md-3 card">
            <div class="card-header">
                <span class="card-title">
                    Logotipo
                </span>
            </div>
            <!-- Profile Photo -->
            <div class="card-body">

                <div class="col-span-6 sm:col-span-4">
                <form action="{{ route('empresa.logotipo') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="col-span-6 sm:col-span-4">
        <!-- Input para selecionar arquivo -->
        <input type="file" id="logotipo" name="logotipo" accept="image/*" class="hidden" onchange="previewPhoto(event)">

        <!-- Foto atual -->
        <div class="mt-2" id="current-photo">
            @if ($empresa->Logotipo)
                <img src="{{ $empresa->Logotipo }}" alt="Logotipo da Empresa" class="rounded-full h-60 w-60 object-cover">
            @else
                <p>{{ __('Nenhum logotipo disponível') }}</p>
            @endif
        </div>

        <!-- Preview do novo logotipo -->
        <div class="mt-2" id="new-photo-preview">
            <span class="block rounded-full w-40 h-40 bg-cover bg-no-repeat bg-center" id="photo-preview"></span>
        </div>

        <div class="row mt-4">
            <!-- Botão de Selecionar Imagem -->
            <div class="col-md-6">
                <label for="logotipo" class="btn btn-sm btn-success">
                    {{ __('Selecionar Imagem') }}
                </label>
            </div>

            <!-- Botão de Remover Imagem -->
            <div class="col-md-6">
                @if ($empresa->Logotipo)
                    <a href="" class="btn btn-sm btn-danger">
                        {{ __('Remover Imagem') }}
                    </a>
                @endif
            </div>
        </div>

        <!-- Botão para Gravar -->
        <div class="mt-4">
            <button type="submit" class="btn btn-primary">
                {{ __('Salvar Logotipo') }}
            </button>
        </div>

        <!-- Mensagens de Erro -->
        <div class="mt-2 text-danger">
            @error('logotipo')
                <span>{{ $message }}</span>
            @enderror
        </div>
    </div>
</form>

                </div>

                <x-section-border />
                <p>{{ $empresa->Designacao }} : {{ $empresa->Empresa }}</p>
                <p>Cedula: {{ $empresa->Cedula }}</p>
                <p>NIF: {{ $empresa->NIF }}</p>

            </div>
        </div>

        <div class="col-md-7">
            <div class="card">
                <div class="card-header">
                    Editar Empresa
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="true">Perfil</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="banco-tab" data-bs-toggle="tab" data-bs-target="#banco" type="button" role="tab" aria-controls="banco" aria-selected="false">Banco</button>
                        </li>
                        <li>
                            <button class="nav-link" id="doc-tab" data-bs-toggle="tab" data-bs-target="#doc" type="button" role="doc" aria-selected="false">Documenetos</button>
                        </li>
                    </ul>

                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade show active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                            <form id="formEmpresa" action="{{ route('empresas.update', $empresa->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <!-- Campos de edição da empresa -->
                                <div class="form-group">
                                    <label for="Slogan">Slogan:</label>
                                    <input type="text" class="form-control" id="Slogan" name="Slogan" value="{{ $empresa->Slogan }}">
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="NIF">Província:</label>
                                            <select name="Provincia" id="Provincia" class="form-control">
                                                @foreach($provincias as $provincia)
                                                    <option value="{{ $provincia->id }}"> {{ $provincia->Nome }} </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="NIF">Município:</label>
                                            <select name="Cidade" id="Cidade" class="form-control">
                                                @foreach($cidades as $cidade)
                                                    <option value="{{ $cidade->id }}"> {{ $cidade->Nome }} </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="Contacto_fixo">Telefone:</label>
                                            <input type="text" class="form-control" id="Contacto_fixo" name="Contacto_fixo" value="{{ $empresa->Contacto_fixo }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="Fax">Fax:</label>
                                            <input type="text" class="form-control" id="Fax" name="Fax" value="{{ $empresa->Fax }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="Email">Email:</label>
                                    <input type="text" class="form-control" id="Email" name="Email" value="{{ auth()->user()->email }}" disabled>
                                </div>

                                <div class="form-group">
                                    <label for="Endereco_completo">Endereço Completo:</label>
                                    <input type="text" class="form-control" id="Endereco_completo" name="Endereco_completo" value="{{ $empresa->Endereco_completo }}">
                                </div>

                                <button type="submit" class="btn btn-primary">Salvar</button>
                            </form>
                        </div>

                        <div class="tab-pane fade" id="banco" role="tabpanel" aria-labelledby="banco-tab">
                            <span>Cadastrar todas as contas bancárias necessárias para documentos e faturas</span>
                            <form id="FormBanco" action="{{ route('banco.inserir') }}" method="post">
                                @csrf
                                <label for="banco-select">Banco</label>
                                <select name="banco" id="banco-select" class="form-control">
                                    @foreach($ibans as $iban)
                                        <option value="{{ $iban['sname'] }}" data-code="{{ $iban['code'] }}">
                                            {{ $iban['sname'] }} - {{ $iban['fname'] }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="row mt-2">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label for="iban-input">IBAN</label>
                                            <input type="text" id="iban-input" name="iban-input" class="form-control" />
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="conta-input">Conta</label>
                                            <input type="text" id="conta-input" name="conta-input" class="form-control" />
                                        </div>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary mt-2">Inserir</button>
                            </form>

                            <div class="mt-4 card">
                                <div class="card-header">
                                    <span class="card-title">
                                        Lista de Contas Bancárias
                                    </span>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <thead>
                                            <th>BANCO</th>
                                            <th>IBAN</th>
                                            <th>CONTA</th>
                                        </thead>
                                        <tbody>
                                            @if($contas->count() > 0)
                                                @foreach($contas as $conta)
                                                    <tr>
                                                        <td>{{ $conta->code_banco }}</td>
                                                        <td>{{ $conta->iban }}</td>
                                                        <td>{{ $conta->conta }}</td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr> 
                                                    <td colspan="3">Sem Conta bancária registrada</td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="doc" role="tabpanel" aria-labelledby="doc-tab">
                            
                            <span>
                                Se a empresa tiver outros documentos como certificados, licenças ou comprovativos, pode adicionar esses arquivos.
                            </span>

                            <div class="form-group">
                                <label for="">Tipo de Documentos</label>
                                <select name="type_doc" id="type_doc">
                                    <option value="">Selecionar</option>
                                    <option value="">Certificado</option>
                                    <option value="">Licenças</option>
                                    <option value="">Comprovativos</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="documents">Documentos</label>
                                <input type="file" name="documents[]" id="documents" multiple>
                                <div id="document-preview" class="mt-2"></div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .rounded-full {
            border-radius: 50%;
            width: 50px;
        }
        .object-cover {
            object-fit: cover;
        }
        .hidden {
            display: none;
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        $(document).ready(function() {

            // Validação em tempo real
            $('#Endereco_completo').on('input', function() {
                var nome = $(this).val();
                if (nome.length < 3) {
                    $('#nome-error').text('O Endereço deve ter pelo menos 3 caracteres.');
                } else {
                    $('#nome-error').text('');
                }
            });

            $('#documents').on('change', function() {
                $('#document-preview').html('');
                for (let i = 0; i < this.files.length; i++) {
                    let file = this.files[i];
                    $('#document-preview').append('<p>' + file.name + '</p>');
                }
            });
            
            // Abrir o seletor de arquivos quando clicar no botão
            $('#select-new-photo').on('click', function (e) {
                e.preventDefault();
                $('#logotipo').click();
            });

            // Mostrar o preview da nova imagem selecionada
            $('#logotipo').change(function () {
                var input = this;
                if (input.files && input.files[0]) {
                    var reader = new FileReader();

                    reader.onload = function (e) {
                        $('#new-photo-preview').show(); // Exibe a área de preview
                        $('#photo-preview').css('background-image', 'url(' + e.target.result + ')'); // Define a imagem de fundo
                    }

                    reader.readAsDataURL(input.files[0]);
                }
            });

            // Remover foto atual
            $('#remove-photo').click(function() {
                photoPreview.hide();
                currentPhoto.hide();
                newPhotoPreview.hide();
                photoError.text('Logotipo removido. Não se esqueça de carregar uma nova imagem antes de salvar.');
            });

            // Quando a imagem for alterada, faz a submissão via AJAX
            /*$('#logotipo').change(function () {
                // Verifica se um arquivo foi selecionado
                if ($('#logotipo')[0].files.length === 0) {
                    $('#photo-error').html('Por favor, selecione uma imagem.');
                    return;
                }

                var formData = new FormData();
                formData.append('logotipo', $('#logotipo')[0].files[0]);

                // Incluir o token CSRF no cabeçalho da requisição
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $.ajax({
                    url: "{{ route('empresa.logotipo') }}", // URL correta para salvar o logotipo
                    type: 'POST',
                    data: formData,
                    processData: false, // Necessário para enviar o FormData corretamente
                    contentType: false, // Não deixe o jQuery definir o contentType automaticamente
                    success: function (response) {
                        if (response.newLogoUrl) {
                            // Substitua a imagem antiga pelo logotipo novo após a submissão
                            $('#current-photo img').attr('src', response.newLogoUrl);

                            // Limpa o preview da nova imagem
                            $('#new-photo-preview').hide();
                            $('#photo-error').html('');
                        } else {
                            $('#photo-error').html('Erro ao carregar o logotipo. Por favor, tente novamente.');
                        }
                    },
                    error: function (xhr, status, error) {
                        // Mostrar mensagem de erro se houver falha
                        $('#photo-error').html('Erro ao carregar a imagem. Por favor, tente novamente.');
                    }
                });
            });*/

            // Validação de IBAN com base no código Logotipo banco selecionado
            $('#banco-select').change(function() {
                const bancoCode = $(this).find(':selected').data('code');
                $('#iban-input').val(`AO06.${bancoCode}.`);
            });

            // Submissão de formulário de banco via AJAX
            $('#FormBanco').submit(function(e) {
                e.preventDefault();
                const formData = $(this).serialize();

                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        toastr.success('Conta bancária adicionada com sucesso.');
                    },
                    error: function() {
                        toastr.error('Erro ao adicionar conta bancária.');
                    }
                });
            });

            // Carregar cidades com base na província selecionada
            $('#Provincia').change(function() {
                const provinciaId = $(this).val();
                $.ajax({
                    url: `/cidades/${provinciaId}`,
                    method: 'GET',
                    success: function(response) {
                        const cidadeSelect = $('#Cidade');
                        cidadeSelect.empty();
                        response.cidades.forEach(function(cidade) {
                            cidadeSelect.append(`<option value="${cidade.id}">${cidade.Nome}</option>`);
                        });
                    }
                });
            });
        });

        document.addEventListener('DOMContentLoaded', function () {
            const ibanInput = document.getElementById('iban-input');
            const bancoSelect = document.getElementById('banco-select');

            ibanInput.addEventListener('input', function () {
                const selectedOption = bancoSelect.options[bancoSelect.selectedIndex];
                const bancoCode = selectedOption.getAttribute('data-code');
                const ibanValue = ibanInput.value;

                if (ibanValue.startsWith(bancoCode)) {
                    ibanInput.classList.remove('is-invalid');
                    ibanInput.classList.add('is-valid');
                } else {
                    ibanInput.classList.remove('is-valid');
                    ibanInput.classList.add('is-invalid');
                }
            });

            bancoSelect.addEventListener('change', function () {
                ibanInput.dispatchEvent(new Event('input'));
            });
        });
    </script>
</x-app-layout>
