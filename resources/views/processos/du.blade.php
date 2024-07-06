<!-- resources/views/processos/listar.blade.php -->

<x-app-layout>
    <div class="card">
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        
        <!-- Input para buscar o processo -->
        <form method="POST" action="{{ route('processos.buscar') }}">
            @csrf
            <label for="processo_search">Numero do Processo</label>
            <x-input name="processo_search" id="processo_search" value="{{ old('processo_search', $numeroProcesso ?? '') }}" />
            <x-button class="mt-4" style="background-color: navy;">
                {{ __('Listar') }}
            </x-button>
        </form>

        <a href="" class="mt-4 btn btn-dark"> Adicionar Mercadoria</a>
        <!-- Lista todas as mercadorias em uma tabela -->
        @if(isset($mercadorias) && $mercadorias->count() > 0)
            <form method="POST" action="{{ route('processos.atualizarCodigoAduaneiro') }}">
                @csrf
                <table class="mt-4 table table-sm">
                    <thead>
                        <tr>
                            <th>Numero da Pauta</th>
                            <th>Descrição</th>
                            <th>Quantidade</th>
                            <th>Preço Unitário</th>
                            <th>FOB</th>
                            <!-- Adicione mais colunas conforme necessário -->
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($mercadorias as $mercadoria)
                            <tr>
                                <td>
                                    <x-input type="hidden" name="mercadoria_id[]" value="{{ $mercadoria->id }}" />
                                    <x-input name="codigo_aduaneiro[]" value="{{ $mercadoria->codigo_aduaneiro ?? '' }}" list="pauta_list"/>
                                    <datalist id="pauta_list">
                                        @foreach($pautaAduaneira as $pauta)
                                        <option value="{{ $pauta->codigo }}">{{ $pauta->descricao }}</option>
                                        @endforeach
                                    </datalist>
                                </td>
                                <td>{{ $mercadoria->Descricao }}</td>
                                <td>{{ $mercadoria->Quantidade }}</td>
                                <td>{{ $mercadoria->preco_unitario }}</td>
                                <td>{{ $mercadoria->preco_total }}</td>
                                <!-- Adicione mais colunas conforme necessário -->
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <x-button class="mt-4" style="background-color: navy;">
                    {{ __('Atualizar Codigos Aduaneiros') }}
                </x-button>
            </form>
        @elseif(isset($numeroProcesso))
            <p class="mt-4">Nenhuma mercadoria encontrada para o processo {{ $numeroProcesso }}.</p>
        @endif
    </div>
</x-app-layout>
