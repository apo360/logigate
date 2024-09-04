<x-app-layout>
    <!-- Adicione o CSS do Bootstrap -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <!-- Seu CSS adicional, se necessário -->
    <style>
        .card {
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .invoice-details,
        .document-actions,
        .related-documents {
            margin-bottom: 20px;
        }

        .action-buttons .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
            width: 100%;
        }

        .action-buttons .btn i {
            margin-right: 5px;
        }

        .table th, .table td {
            vertical-align: middle;
            text-align: center;
        }

        textarea {
            width: 100%;
            padding: 10px;
            resize: vertical;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .btn-outline-secondary {
            border-color: #6c757d;
            color: #6c757d;
            background-color: #fff;
        }

        .btn-outline-secondary:hover {
            background-color: #6c757d;
            color: #fff;
        }

    </style>

    <div style="padding: 10px;">
        @if(session('success'))
            <div class="card card-default">
                <div class="font-medium text-green-600">{{ __('Sucesso!') }}</div>

                <p class="mt-3 text-sm text-green-600">
                    {{ session('success') }}
                </p>
            </div>
        @endif

        <br>

        <div class="card">
    <div class="row">
        <div class="col-md-8">
            <header class="mb-4">
                <h4>{{$documento->invoice_no}}</h4>
                <hr>
                <p class="mb-2">
                    <strong>Tax ID: {{$documento->customer->CustomerTaxID}}</strong><br>
                    {{$documento->customer->CompanyName}}<br>
                    <i class="fas fa-phone"></i> {{$documento->customer->Telephone}}<br>
                    <i class="fas fa-envelope"></i> {{$documento->customer->Email}}<br>
                    <i class="fas fa-map-marker-alt"></i> {{$documento->customer->endereco ? $documento->customer->endereco->AddressDetail : 'Sem endereço'}}
                </p>
            </header>

            <section class="invoice-details mb-4">
                <h5>Documento</h5>
                <table class="table table-sm table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>Data</th>
                            <th>Documento</th>
                            <th>Facturação</th>
                            <th>Pago</th>
                            <th>Data de Pagamento</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{$documento->invoice_date}}</td>
                            <td>{{$documento->invoice_no}}</td>
                            <td>{{$documento->salesdoctotal->gross_total ?? '0.00'}} Kz</td>
                            <td>{{$documento->salesdoctotal->montante_pagamento ?? '0.00'}} Kz</td>
                            <td>{{$documento->salesdoctotal->data_pagamento ?? ''}}</td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <section class="document-actions mb-4">
                <h5>Detalhes</h5>
                <div class="d-flex justify-content-between">
                    <a href="#" id="add-new-client-button" data-toggle="modal" data-target="#ListItemModal" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-box-open"></i> Itens
                    </a>
                    <a href="#" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-receipt"></i> Impostos
                    </a>
                    <a href="#" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-percentage"></i> Montante Retidos
                    </a>
                    <span class="text-muted"><i class="fas fa-user"></i> Operador: {{$documento->user->name}}</span>
                </div>
            </section>

            <section class="related-documents mb-4">
                <h5>Documentos Relacionados</h5>
                <table class="table table-sm table-bordered">
                    <thead class="thead-light">
                        <th>Data</th>
                        <th>Documento</th>
                        <th>Estado</th>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </section>

            <section class="related-documents mb-4">
                <textarea class="form-control" cols="30" rows="4" placeholder="Observações"></textarea>
            </section>
        </div>
        <div class="col-md-4">
            <div class="btn-group-vertical w-100 action-buttons">
                <a href="{{ route('documento.print', ['invoiceNo' => $documento->id]) }}" class="btn btn-sm btn-primary mb-2">
                    <i class="fas fa-print"></i> Imprimir
                </a>
                <button type="button" class="btn btn-sm btn-secondary mb-2">
                    <i class="fas fa-envelope"></i> Enviar por Email
                </button>
                <a href="{{ route('documento.download', ['invoiceNo' => $documento->id]) }}" class="btn btn-sm btn-primary mb-2">
                    <i class="fas fa-download"></i> Download
                </a>
                <button type="button" class="btn btn-sm btn-secondary mb-2">
                    <i class="fas fa-bell"></i> Notificar na App
                </button>
                @if($documento->invoiceType->Code == 'FT' || $documento->invoiceType->Code == 'FG')
                    <a href="{{ route('documento.ViewPagamento', ['id' => $documento->id]) }}" class="btn btn-sm btn-success mb-2">
                        <i class="fas fa-credit-card"></i> Efectuar Pagamento
                    </a>
                @endif
                <a href="{{ route('documentos.edit', $documento) }}" class="btn btn-sm btn-danger">
                    <i class="fas fa-times-circle"></i> Anular Factura
                </a>
            </div>
        </div>
    </div>
</div>



    </div>

    <!-- Modal para adicionar novo cliente -->
    <div class="modal fade" id="ListItemModal" tabindex="-1" role="dialog" aria-labelledby="ListItemModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document"> <!-- Use modal-md para tamanho médio -->
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newClientModalLabel">Lista de Itens</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mt-4">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Cod</th>
                                            <th>Item</th>
                                            <th>Quantidade</th>
                                            <th>Preço Unit (kz)</th>
                                            <th>Total (kz)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $totalProdutos = 0; // Inicialize o total
                                        @endphp

                                        @foreach($documento->salesitem as $Item)
                                            <tr>
                                                <td>{{$Item->produto->ProductCode}}</td>
                                                <td>{{$Item->produto->ProductDescription}}</td>
                                                <td>{{$Item->quantity}}</td>
                                                <td>{{$Item->unit_price}}</td>
                                                <td>{{$Item->credit_amount}}</td>
                                                
                                                @php
                                                    $totalProdutos += $Item->credit_amount; // Adicione o valor ao total
                                                @endphp
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="4">Total de Produtos:</td>
                                            <td>{{$totalProdutos}}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

     <!-- Inclua o jQuery (já incluído) -->
     <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Inclua o JS do Bootstrap -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</x-app-layout>