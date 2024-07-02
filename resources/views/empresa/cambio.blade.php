<x-app-layout>
    <div class="container">
        <h1>Editar Câmbio</h1>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('cambios.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="moeda">Selecionar Moeda</label>
                <select name="moeda_txt" id="moeda_txt" class="form-control">
                    <option value="">Selecionar</option>
                    @foreach($cambios as $cambio)
                        <option value="{{ $cambio->moeda }}" {{ $cambio->moeda == $cambio->moeda ? 'selected' : '' }}>
                            {{ $cambio->moeda }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="cambio">Câmbio</label>
                <input type="text" name="cambio" id="cambio" class="form-control" value="{{ old('cambio', $cambio->cambio) }}" required>
                @if ($errors->has('cambio'))
                    <span class="text-danger">{{ $errors->first('cambio') }}</span>
                @endif
            </div>

            <div class="form-group">
                <label for="data_cambio">Data do Câmbio</label>
                <input type="date" name="data_cambio" id="data_cambio" class="form-control" value="{{ old('data_cambio', $cambio->data_cambio) }}" required>
                @if ($errors->has('data_cambio'))
                    <span class="text-danger">{{ $errors->first('data_cambio') }}</span>
                @endif
            </div>

            <button type="submit" class="btn btn-primary">Atualizar</button>
        </form>

        <table class="table">
            <thead>
                <tr>
                    <th>Moeda</th>
                    <th>Cambio</th>
                    <th>Última Atualização</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cambios as $cambio)
                    <tr>
                        <td>{{ $cambio->moeda }}</td>
                        <td>{{ $cambio->cambio }}</td>
                        <td>{{ $cambio->data_cambio }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-app-layout>
