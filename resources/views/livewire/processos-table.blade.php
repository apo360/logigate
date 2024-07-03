<div>
    <div class="row mb-4">
        <div class="col">
            <input type="text" class="form-control" placeholder="Buscar..." wire:model.debounce.300ms="search">
        </div>
        <div class="col">
            <select class="form-control" wire:model="situacao">
                <option value="">Todas Situações</option>
                <option value="Em processamento">Em processamento</option>
                <option value="Desembaraçado">Desembaraçado</option>
                <option value="Retido">Retido</option>
            </select>
        </div>
        <div class="col">
            <select class="form-control" wire:model="tipoProcesso">
                <option value="">Todos Tipos</option>
                <option value="Tipo 1">Tipo 1</option>
                <option value="Tipo 2">Tipo 2</option>
                <!-- Adicione mais opções conforme necessário -->
            </select>
        </div>
        <div class="col">
            <input type="date" class="form-control" wire:model="dataCriacao">
        </div>
    </div>

    <table class="table table-hover">
        <thead>
            <tr>
                <th>Número do Processo</th>
                <th>Cliente</th>
                <th>Tipo de Processo</th>
                <th>Situação</th>
                <th>Porto de Origem</th>
                <th>Data de Abertura</th>
            </tr>
        </thead>
        <tbody>
                @foreach ($processos as $processo)
                    <tr class="bg-dark-blue hover:bg-darker-blue">
                        <td>{{ $processo->NrProcesso }}</td>
                        <td>{{ $processo->CompanyName }}</td>
                        <td>{{ $processo->TipoProcesso }}</td>
                        <td>
                            <span class="badge {{ $processo->Situacao == 'Em processamento' ? 'badge-warning' : ($processo->Situacao == 'Desembaraçado' ? 'badge-success' : 'badge-danger') }}">
                                {{ $processo->Situacao }}
                            </span>
                        </td>
                        <td>{{ $processo->PortoOrigem }}</td>
                        <td>{{ $processo->DataAbertura }}</td>
                    </tr>
                @endforeach
            
        </tbody>
    </table>
  
</div>

<!-- Adicione o CSS diretamente no Blade ou em seu arquivo CSS -->
<style>
.bg-dark-blue {
    background-color: #1b3a57 !important; /* Azul-escuro */
    color: white !important; /* Texto branco para melhor contraste */
}

.bg-darker-blue:hover {
    background-color: #142a40 !important; /* Azul-escuro mais forte para hover */
}
</style>
