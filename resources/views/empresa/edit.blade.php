<x-app-layout>
    <br>
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
                <!-- Profile Photo File Input -->
                <input type="file" id="photo" class="hidden" />

                <label for="photo" value="{{ __('Photo') }}"></label>

                <!-- Current Profile Photo -->
                <div class="mt-2" id="current-photo">
                    <img src="{{ $empresa->Logotipo }}" alt="{{ $empresa->Empresa }}" class="rounded-full h-20 w-20 object-cover">
                </div>

                <!-- New Profile Photo Preview -->
                <div class="mt-2" id="new-photo-preview" style="display: none;">
                    <span class="block rounded-full w-20 h-20 bg-cover bg-no-repeat bg-center" id="photo-preview"></span>
                </div>

                <button class="mt-2 me-2" type="button" id="select-new-photo">
                    {{ __('Select A New Photo') }}
                </button>

                @if ($empresa->Logotipo)
                    <button type="button" class="mt-2" id="remove-photo">
                        {{ __('Remove Photo') }}
                    </button>
                @endif

                <div class="mt-2" id="photo-error"></div>
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
                    </ul>
                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade show active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                            <form  id="formEmpresa" action="{{ route('empresas.update', $empresa->id) }}" method="POST">
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
                                                    <option value=" {{ $provincia->id}}"> {{ $provincia->Nome}} </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="NIF">Municipio:</label>
                                            <select name="Cidade" id="Cidade" class="form-control">
                                                @foreach($cidades as $cidade)
                                                    <option value=" {{ $cidade->id}}"> {{ $cidade->Nome}} </option>
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
                                    <label for="Endereco_completo"> Endereço Completo:</label>
                                    <input type="text" class="form-control" id="Endereco_completo" name="Endereco_completo" value="{{ $empresa->Endereco_completo }}">
                                </div>
                                <!-- Adicione outros campos da empresa conforme necessário -->
                                <button type="submit" class="btn btn-primary">Salvar</button>
                            </form>
                        </div>
                        <div class="tab-pane fade" id="banco" role="tabpanel" aria-labelledby="banco-tab">
                            <span>Cadastrar todas as contas bancarias necessárias para documentos e facturas</span>
                            <form id="FormBanco" action="{{ route('banco.inserir') }}" method="post">
                                <label for="banco-select">Banco</label>
                                <select name="banco" id="banco-select" class="form-control">
                                    @foreach($ibans as $iban)
                                        <option value="{{$iban['sname']}}" data-code="{{$iban['code']}}">
                                            {{$iban['sname']}} - {{$iban['fname']}}
                                        </option>
                                    @endforeach
                                </select>

                                <label for="iban-input">IBAN (AO06)</label>
                                <x-input type="text" id="iban-input" name="iban-input" />

                                <label for="conta-input">Conta</label>
                                <x-input type="text" id="conta-input" name="conta-input" />

                                <x-button>{{ __('Inserir') }}</x-button>
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
                                                        <td>{{ $conta->code_banco}}</td>
                                                        <td>{{ $conta->iban}}</td>
                                                        <td>{{ $conta->conta}}</td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr> <td> Sem Conta bancaria registados</td> </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
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
        }
        .object-cover {
            object-fit: cover;
        }
        .hidden {
            display: none;
        }
    </style>

    <script>
        $(document).ready(function() {
            const photoInput = $('#photo');
            const photoPreview = $('#photo-preview');
            const currentPhoto = $('#current-photo');
            const newPhotoPreview = $('#new-photo-preview');
            const photoError = $('#photo-error');

            $('#select-new-photo').click(function(e) {
                e.preventDefault();
                photoInput.click();
            });

            photoInput.change(function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        photoPreview.css('background-image', 'url(' + e.target.result + ')');
                        newPhotoPreview.show();
                        currentPhoto.hide();
                    };
                    reader.readAsDataURL(file);
                }
            });

            $('#remove-photo').click(function() {
                // Adicione a lógica para remover a foto aqui, se necessário
                // Isso geralmente envolvia uma chamada ao servidor em Livewire
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            $('.form-control').select2();
        });
    </script>

    <!-- <script> 
        // Selecione o formulário
        const formE = document.getElementById('FormBanco');

        // Adicione um event listener para o envio do formulário
        formE.addEventListener('submit', async (event) => {
            // Impedir o envio padrão do formulário
            event.preventDefault();

            // Enviar o formulário via AJAX
            const formData = new FormData(formE);
            const url = formE.action;

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    body: formData
                });

                // Verificar se a resposta é bem-sucedida
                if (response.ok) {
                    // Converter a resposta para JSON
                    const data = await response.json();

                    // Exibir a mensagem de retorno usando Toastr
                    toastr.success(data.message); // Exibir mensagem de sucesso
                } else {
                    // Se a resposta não for bem-sucedida, exibir uma mensagem de erro genérica
                    toastr.error('Ocorreu um erro ao processar sua solicitação. Por favor, tente novamente mais tarde.');
                }
            } catch (error) {
                console.error('Erro ao enviar formulário:', error);
                // Em caso de erro, exibir uma mensagem de erro genérica
                toastr.error('Ocorreu um erro ao processar sua solicitação. Por favor, tente novamente mais tarde.');
            }
        });
    </script>
-->
    <script>
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