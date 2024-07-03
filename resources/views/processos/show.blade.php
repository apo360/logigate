<!-- resources/views/processos/show.blade.php -->

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detalhes do Processo') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="container">

                    <!-- Exiba os detalhes do processo -->
                    <h3>Número do Processo: {{ $processo->NrProcesso }}</h3>
                    <p>Cliente: {{ $processo->cliente->CompanyName }}</p>
                    <p>Ref.ª do Cliente(Fatura): {{ $processo->RefCliente }}</p>
                    <p>Data de Abertura: {{ $processo->DataAbertura }}</p>
                    <!-- Exiba outros detalhes do processo, se houver -->

                    <hr>

                    <!-- Exiba as mercadorias relacionadas ao processo -->
                    <h3>Mercadorias:</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Marcas</th>
                                <th>Número</th>
                                <th>Quantidade</th>
                                <th>Qualificação</th>
                                <th>Designação</th>
                                <th>Peso</th>
                                <!-- Outros campos necessários -->
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($mercadorias as $mercadoria)
                                <tr>
                                    <td>{{ $mercadoria->marcas }}</td>
                                    <td>{{ $mercadoria->numero }}</td>
                                    <td>{{ $mercadoria->quantidade }}</td>
                                    <td>{{ $mercadoria->qualificacaoID }}</td>
                                    <td>{{ $mercadoria->designacao }}</td>
                                    <td>{{ $mercadoria->peso }}</td>
                                    <!-- Exiba outros campos da mercadoria, se houver -->
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
