<!-- resources/view/mercadorias/create_mercadoria.blade.php -->
<x-app-layout>
    @php
        $fob = 0;
        $seguro = 0;
        $frete = 0;
        $CIF = 0;
    @endphp
    @if(request()->has('licenciamento_id'))
        <x-breadcrumb :items="[
            ['name' => 'Licenciamentos', 'url' => route('licenciamentos.index')],
            ['name' => 'Visualizar Licenciamento', 'url' => route('licenciamentos.show', request()->get('licenciamento_id') )],
            ['name' => 'Nova Mercadorias', 'url' => '']
        ]" separator="/" />
        @php
            $licenciamento = App\Models\Licenciamento::find(request()->get('licenciamento_id'));
            $fob = $licenciamento->fob_total;
            $seguro = $licenciamento->seguro;
            $frete = $licenciamento->frete;
            $CIF = $fob + $seguro + $frete;
        @endphp
    @endif

    <form action="{{ route('mercadorias.store') }}" method="POST" id="formNovaMercadoria">
        @csrf
        <!-- Enviar os IDs escondidos no formulário -->
        @if(request()->has('licenciamento_id'))
            <input type="hidden" name="licenciamento_id" value="{{ request()->get('licenciamento_id') }}">
        @endif

        <div class="row">
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header justify-between h-16">
                        <div class="card-title flex"> <span>Mercadorias</span> </div>
                        <div class="flex float-right">
                            <!-- Botão de submissão -->
                            <button type="submit" class="btn btn-primary">Salvar Mercadoria</button>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 form-group">
                                <label for="">Tipo de Mercadoria</label>
                                <select class="form-control" id="subcategoria_id" name="subcategoria_id" required>
                                    <option value="">Selecione uma categoria</option>
                                    @foreach($sub_categorias as $subcategoria)
                                        <option value="{{$subcategoria->id}}" data-code="{{$subcategoria->cod_pauta}}">
                                            {{$subcategoria->cod_pauta}} - {{$subcategoria->descricao}}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3 form-group">
                                <label for="codigo_aduaneiro">Código Aduaneiro</label> <span class="erro_pauta"></span>
                                <input type="text" id="codigo_aduaneiro" name="codigo_aduaneiro" class="form-control" required list="pauta_list">
                                <datalist id="pauta_list">

                                </datalist>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="Quantidade" class="form-label">Quantidade</label>
                                <input type="number" class="form-control" id="Quantidade" name="Quantidade" min="1" placeholder="Ex.: 10" required>
                            </div>
                            
                        </div>

                        
                        <!-- Detalhes da Mercadoria -->
                        <div class="row">
                            <div class="col-md-9 mb-3">
                                <label for="descricao">Descrição da Mercadoria:</label>
                                <input type="text" name="Descricao" class="form-control" required>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="Peso" class="form-label">Peso (Kg)</label>
                                <input type="number" class="form-control" id="Peso" name="Peso" step="0.01" placeholder="Ex.: 500.50" value="0">
                            </div>
                        </div>

                        <!-- Detalhes Adicionais: Peso, Volume, etc. -->
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="preco_unitario" class="form-label">Valor Unitário (Moeda)</label>
                                <input type="number" class="form-control" id="preco_unitario" name="preco_unitario" step="0.01" placeholder="Ex.: 1000.00" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="preco_total" class="form-label">Valor Total (FOB)</label>
                                <input type="number" class="form-control" id="preco_total" name="preco_total" step="0.01" placeholder="Ex.: 10000.00">
                            </div>
                        </div>

                        <!-- Informações Adicionais por Categoria -->
                        <div class="info_veiculos" id="info_veiculos" style="display: none;">
                            <div class = "row">
                                <div class="col-md-4 mb-3">
                                    <label for="marca" class="form-label">Marca do Veículo</label>
                                    <input type="text" class="form-control" id="marca" name="marca" placeholder="Ex.: Toyota">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="modelo" class="form-label">Modelo do Veículo</label>
                                    <input type="text" class="form-control" id="modelo" name="modelo" placeholder="Ex.: Corolla">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="chassis" class="form-label">Nº do Chassis</label>
                                    <input type="text" class="form-control" id="chassis" name="chassis" placeholder="Ex.: MA3JFB...">
                                </div>
                            </div>
                            <div class = "row">
                                <div class="col-md-4 mb-3">
                                    <label for="ano_fabricacao" class="form-label">Ano de Fabricação</label>
                                    <input type="number" class="form-control" id="ano_fabricacao" name="ano_fabricacao" placeholder="Ex.: 2020">
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="modelo" class="form-label">H.S Code</label>
                                <input type="text" class="form-control" id="modelo" name="modelo" placeholder="Ex.: Corolla">
                            </div>
                        </div>

                        <div class="info_maquina" id="info_maquina" style="display: none;">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="marca" class="form-label">Marca da Máquina</label>
                                    <input type="text" class="form-control" id="marca" name="marca" placeholder="Ex.: Dell">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="potencia" class="form-label">Potência (kW)</label>
                                    <input type="number" class="form-control" id="potencia" name="potencia" step="0.01" placeholder="Ex.: 500.00">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
            <div class="card shadow-lg border-0">
                    <div class="card-header text-white fw-bold d-flex justify-content-between align-items-center" id="card-header">
                        <h4 class="mb-0"><i class="fas fa-file-invoice-dollar"></i> Detalhes do Licenciamento</h4>
                        <span class="badge" id="status-badge">Analisando...</span>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <!-- Valor FOB -->
                            <div class="col-md-12 mb-3">
                                <label class="fw-bold text-secondary"><i class="fas fa-coins"></i> FOB Total</label>
                                <p class="fs-5 text-primary mb-0"><strong>Kz {{ number_format($fob, 2) }}</strong></p>
                            </div>

                            <!-- Somatório das Mercadorias -->
                            <div class="col-md-12 mb-3">
                                <label class="fw-bold text-secondary"><i class="fas fa-box-open"></i> Somatório das Mercadorias</label>
                                <p class="fs-5 text-primary mb-0"><strong>Kz {{ number_format($somaPrecoTotal, 2) }}</strong></p>
                            </div>
                        </div>

                        <hr>

                        <!-- Barra de Progresso -->
                        <div class="progress mb-2">
                            <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: {{ $porcentagem }}%;" 
                                aria-valuenow="{{ $porcentagem }}" aria-valuemin="0" aria-valuemax="100">
                                {{ number_format($porcentagem, 2) }}%
                            </div>
                        </div>

                        <!-- Mensagens de Status -->
                        <p class="text-center fw-bold" id="progress-message"></p>

                        <!-- Alerta caso o valor exceda 100% -->
                        @if($porcentagem >= 100)
                            <div class="alert alert-danger text-center fw-bold">
                                <i class="fas fa-exclamation-triangle"></i> Atenção! O total das mercadorias excede o valor FOB do Licenciamento.
                            </div>
                        @endif
                    </div>
                </div>

                <!-- SCRIPT PARA ALTERAR A APARÊNCIA DINAMICAMENTE -->
                <script>
                    document.addEventListener("DOMContentLoaded", function () {
                        var porcentagem = {{ $porcentagem }};
                        var progressBar = document.getElementById('progress-bar');
                        var cardHeader = document.getElementById('card-header');
                        var statusBadge = document.getElementById('status-badge');
                        var progressMessage = document.getElementById('progress-message');

                        // Definição de cores e mensagens baseadas na porcentagem
                        if (porcentagem >= 100) {
                            progressBar.classList.add('bg-danger');
                            cardHeader.classList.add('bg-danger');
                            statusBadge.classList.add('bg-danger', 'text-white');
                            statusBadge.textContent = "Excedente!";
                            progressMessage.innerHTML = "O somatório das mercadorias <strong>excedeu</strong> o limite permitido.";
                        } else if (porcentagem >= 80) {
                            progressBar.classList.add('bg-warning');
                            cardHeader.classList.add('bg-warning');
                            statusBadge.classList.add('bg-warning', 'text-dark');
                            statusBadge.textContent = "Quase no limite";
                            progressMessage.innerHTML = "O valor das mercadorias está próximo ao limite do FOB.";
                        } else {
                            progressBar.classList.add('bg-success');
                            cardHeader.classList.add('bg-success');
                            statusBadge.classList.add('bg-success', 'text-white');
                            statusBadge.textContent = "Dentro do Limite";
                            progressMessage.innerHTML = "O somatório das mercadorias está dentro do permitido.";
                        }
                    });
                </script>
            </div>
        </div>
        <!-- Seleção de Categoria -->
    </form>
    <hr>
    <div class="card card-navy">
        <div class="card-header justify-between h-16">
            <div class="card-title flex"> <span>Adições Agrupadas</span> </div>
            <div class="flex float-right">
                <a href="#" id="pauta_mercadoria" class="event button" data-toggle="modal" data-target="#PautaModal">
                    <span class="icon-edit icon"></span>Pauta Aduaneira
                </a>
                <!-- <form action="{{ route('mercadorias.reagrupar', $licenciamento->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-warning">Reagrupar</button>
                </form> -->
            </div>
        </div>

        <div class="card-body">
            <table class="table table-flex table-flex--autocomplete">
                <thead>
                    <tr>
                        <th>Codigo Aduaneiro</th>
                        <th>Quantidade Total</th>
                        <th>Peso (Kg)</th>
                        <th>Preço (Moeda)</th>
                        <th>Quantidade</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($mercadoriasAgrupadas as $agrupamento)
                    <tr data-widget="expandable-table" aria-expanded="false">
                        <td>{{ $agrupamento->codigo_aduaneiro }}</td>
                        <td>{{ $agrupamento->quantidade_total }}</td>
                        <td>{{ $agrupamento->peso_total }}</td>
                        <td>{{ $agrupamento->preco_total }}</td>
                        <td>{{ count($agrupamento->mercadorias) }}</td>
                    </tr>

                    <!-- Linhas Detalhadas (Mercadorias Associadas) -->
                    <tr class="expandable-body">
                        <td colspan="5">
                            <table class="table table-sm mb-0">
                                <thead class="table-primary">
                                    <tr>
                                        <th>Descrição</th>
                                        <th>Quantidade</th>
                                        <th>Peso</th>
                                        <th>Preço Total</th>
                                        <th>Acções</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($agrupamento->mercadorias as $mercadoria)
                                    <tr id="mercadoria-{{ $mercadoria->id }}">
                                        <td>{{ $mercadoria->Descricao }}</td>
                                        <td>{{ $mercadoria->Quantidade }}</td>
                                        <td>{{ $mercadoria->Peso }}</td>
                                        <td>{{ $mercadoria->preco_total }}</td>
                                        <td>
                                            <a href="#" class="btn-edit" data-id="{{ $mercadoria->id }}">
                                                <i class="fas fa-edit" style="color: darkcyan;"></i>
                                            </a>
                                            <a href="#" class="btn-delete" data-id="{{ $mercadoria->id }}">
                                                <i class="fas fa-trash" style="color: red;"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal para edição de Mercadoria -->
    <div class="modal fade" id="modalEditarMercadoria" tabindex="-1" aria-labelledby="modalEditarMercadoriaLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarMercadoriaLabel">Editar Mercadoria</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formEditarMercadoria">
                        <input type="hidden" id="edit_id" name="id">
                        
                        <div class="form-group">
                            <label for="edit_descricao">Descrição da Mercadoria:</label>
                            <input type="text" name="Descricao" class="form-control" id="edit_descricao" required>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="edit_Quantidade">Quantidade</label>
                                <input type="number" class="form-control" id="edit_Quantidade" name="Quantidade" min="1" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="edit_Unidade">Unidade de Medida</label>
                                <select class="form-control" id="edit_Unidade" name="Unidade" required>
                                    <option value="kg">Kg</option>
                                    <option value="l">Litros</option>
                                    <option value="uni">Unidades</option>
                                    <option value="m">Metros</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="edit_Qualificacao">Qualificação</label>
                                <select name="Qualificacao" id="edit_Qualificacao" class="form-control">
                                    <option value="cont">Contentor</option>
                                    <option value="auto">Automóvel</option>
                                    <option value="outro">Outro</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="edit_Peso">Peso (Kg)</label>
                                <input type="number" class="form-control" id="edit_Peso" name="Peso" step="0.01">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="edit_volume">Volume (m³)</label>
                                <input type="number" class="form-control" id="edit_volume" name="volume" step="0.01">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="edit_preco_unitario">Valor Unitário (Moeda)</label>
                                <input type="number" class="form-control" id="edit_preco_unitario" name="preco_unitario" step="0.01" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="edit_preco_total">Valor Total (FOB)</label>
                                <input type="number" class="form-control" id="edit_preco_total" name="preco_total" step="0.01" required>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                            <a class="btn btn-primary" id="editMercadoriaForm">Salvar Alterações</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- FONT AWESOME PARA OS ÍCONES -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#subcategoria_id').focus().css('border', '2px solid #007bff'); // Exemplo com estilização após focar

            $('#subcategoria_id').on('change', function() {
                // Captura o código da subcategoria selecionada
                var cod_pauta = $(this).find(':selected').data('code');

                // Limpa a lista existente
                $('#pauta_list').empty();

                if (cod_pauta) {
                    // Faz a requisição AJAX para buscar os códigos aduaneiros com base no código da subcategoria
                    $.ajax({
                        url: `${window.location.origin}/get-codigo-aduaneiro/${cod_pauta}`,
                        method: 'GET',
                        success: function(data) {
                            $('#pauta_list').empty();
                            $.each(data, function(index, pauta) {
                                var codigoFormatado = pauta.codigo;
                                $('#pauta_list').append('<option value="' + codigoFormatado + '">' + codigoFormatado + ' - ' + pauta.descricao + '</option>');
                            });
                        },
                        error: function() {
                            alert('Erro ao carregar os códigos aduaneiros.');
                        }
                    });
                }
            });
        });

        $(document).ready(function() {
            // Validação ao sair do campo
            $('#codigo_aduaneiro').on('input', function() {
                var inputValue = $(this).val().trim();
                var isValid = false;
                var isIncomplete = false;

                // Percorre todas as opções do datalist
                $('#pauta_list option').each(function() {
                    var optionValue = $(this).val();

                    // Verifica se o valor digitado está exatamente na lista
                    if (optionValue === inputValue) {
                        isValid = true;
                    }

                    // Verifica se o valor digitado é um prefixo de um código maior
                    if (optionValue.startsWith(inputValue) && optionValue.length > inputValue.length) {
                        isIncomplete = true;
                    }
                });

                // Exibir erro se o valor não estiver na lista
                if (!isValid || isIncomplete) {
                    $('.erro_pauta').text(isIncomplete ? 'Código incompleto! Digite o código completo.' : 'Código inválido! Selecione um da lista.')
                        .css('color', 'red');
                    $(this).addClass('is-invalid');
                } else {
                    $('.erro_pauta').text('');
                    $(this).removeClass('is-invalid');
                }
            });

            // Evita submissão de formulário com código inválido
            $('#formNovaMercadoria').on('submit', function(event) {
                var inputValue = $('#codigo_aduaneiro').val().trim();
                var isValid = false;
                var isIncomplete = false;

                $('#pauta_list option').each(function() {
                    var optionValue = $(this).val();
                    if (optionValue === inputValue) {
                        isValid = true;
                    }
                    if (optionValue.startsWith(inputValue) && optionValue.length > inputValue.length) {
                        isIncomplete = true;
                    }
                });

                if (!isValid || isIncomplete) {
                    event.preventDefault();
                    alert(isIncomplete ? 'Erro: Código incompleto! Digite o código completo.' : 'Erro: Código inválido. Escolha um da lista.');
                }
            });
        });


        document.getElementById('Quantidade').addEventListener('input', calcularValorTotal);
        document.getElementById('preco_unitario').addEventListener('input', calcularValorTotal);

        function calcularValorTotal() {
            var quantidade = parseFloat(document.getElementById('Quantidade').value) || 0;
            var precoUnitario = parseFloat(document.getElementById('preco_unitario').value) || 0;
            var valorTotal = quantidade * precoUnitario;
            document.getElementById('preco_total').value = valorTotal.toFixed(2);
        }
    </script>
    <script>
        $(document).ready(function() {
            $('.btn-delete').click(function(e) {
                e.preventDefault();

                let mercadoriaId = $(this).data('id');
                let rowMercadoria = $(`#mercadoria-${mercadoriaId}`);
                let tableMercadorias = rowMercadoria.closest('tbody');
                let rowAgrupamento = rowMercadoria.closest('.expandable-body').prev('tr'); // Linha principal do agrupamento

                if (!confirm("Tem certeza que deseja excluir esta mercadoria?")) {
                    return;
                }

                $.ajax({
                    url: `{{ route('mercadorias.destroy', ':id') }}`.replace(':id', mercadoriaId),
                    type: 'POST', // Laravel exige POST para DELETE
                    dataType: 'json',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'DELETE'
                    },
                    success: function(response) {
                        if (response.success) {
                            rowMercadoria.remove();

                            // Se não houver mais mercadorias no grupo, remover o agrupamento principal
                            if (tableMercadorias.children('tr').length === 0) {
                                rowAgrupamento.remove();
                                tableMercadorias.closest('.expandable-body').remove();
                            }

                            alert(response.message);
                        } else {
                            alert("Erro: " + response.message);
                        }
                    },
                    error: function() {
                        alert("Erro ao excluir a mercadoria. Por favor, tente novamente.");
                    }
                });
            });
        });

        $(document).ready(function() {
             // Clique no botão Editar
            $('.btn-edit').click(function(e) {
                e.preventDefault();

                let mercadoriaId = $(this).data('id');

                // Buscar os dados da mercadoria via AJAX
                $.ajax({
                    url: `{{ route('mercadorias.edit', ':id') }}`.replace(':id', mercadoriaId),
                    type: 'GET',
                    success: function(data) {
                        // Preencher os campos do modal com os dados retornados
                        $('#edit_id').val(data.id);
                        $('#edit_descricao').val(data.Descricao);
                        $('#edit_Quantidade').val(data.Quantidade);
                        $('#edit_Unidade').val(data.Unidade);
                        $('#edit_Qualificacao').val(data.Qualificacao);
                        $('#edit_Peso').val(data.Peso);
                        $('#edit_volume').val(data.volume);
                        $('#edit_preco_unitario').val(data.preco_unitario);
                        $('#edit_preco_total').val(data.preco_total);

                        // Abrir o modal
                        $('#modalEditarMercadoria').modal('show');
                    },
                    error: function(xhr, status, error) {
                        alert("Erro ao buscar os dados da mercadoria.");
                    }
                });
            });

            // Submit do formulário de edição
            $('#editMercadoriaForm').submit(function(e) {
                e.preventDefault();

                let mercadoriaId = $('#edit_id').val();
                let formData = $(this).serialize();

                $.ajax({
                    url: `{{ route('mercadorias.update', ':id') }}`.replace(':id', mercadoriaId),
                    type: 'PUT', 
                    data: formData,
                    success: function(response) {
                        alert("Mercadoria atualizada com sucesso!");
                        $('#modalEditarMercadoria').modal('hide');
                        location.reload(); // Atualiza a página para refletir as mudanças
                    },
                    error: function(xhr, status, error) {
                        alert("Erro ao atualizar a mercadoria.");
                    }
                });
            });
        });
    </script>

</x-app-layout>