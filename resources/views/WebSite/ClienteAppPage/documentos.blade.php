<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <title>Logigate | Documentos</title>
</head>
<body>
    <h1>Documentos</h1>
    <p>{{ $customer->CompanyName ?? $portal->username ?? 'Cliente' }}</p>

    @if(session('status'))
        <p>{{ session('status') }}</p>
    @endif

    <p><a href="{{ route('cliente.portal.dashboard') }}">Voltar ao painel</a></p>

    <h2>Enviar Documento</h2>
    <form action="{{ route('cliente.portal.documentos.upload') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <p>
            <label for="documento">Ficheiro</label><br>
            <input id="documento" type="file" name="documento" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx">
            @error('documento') <br><small>{{ $message }}</small> @enderror
        </p>

        <p>
            <label for="categoria">Categoria</label><br>
            <select id="categoria" name="categoria">
                <option value="documentos">Geral</option>
                <option value="documentos_identificacao">BI / Identificação</option>
                <option value="contratos">Procuração / Contrato</option>
                <option value="comprovativos">Comprovativo</option>
                <option value="recibos">Documento fiscal</option>
                <option value="outros">Outro</option>
            </select>
            @error('categoria') <br><small>{{ $message }}</small> @enderror
        </p>

        <p>
            <label for="observacao">Observação</label><br>
            <textarea id="observacao" name="observacao" rows="3" maxlength="500"></textarea>
            @error('observacao') <br><small>{{ $message }}</small> @enderror
        </p>

        <button type="submit">Enviar Documento</button>
    </form>

    @if($documentos->isNotEmpty())
        <h2>Meus Documentos</h2>
        <table border="1" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Categoria</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Data</th>
                    <th>Acção</th>
                </tr>
            </thead>
            <tbody>
                @foreach($documentos as $documento)
                    <tr>
                        <td>{{ $documento->nome_original }}</td>
                        <td>{{ $documento->categoria }}</td>
                        <td>{{ strtoupper($documento->extension ?: '-') }}</td>
                        <td>{{ $documento->status ?? 'activo' }}</td>
                        <td>{{ optional($documento->created_at)->format('d/m/Y H:i') }}</td>
                        <td>
                            <a href="{{ route('cliente.portal.documentos.download', $documento->id) }}">Download</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>Nenhum documento disponível no portal.</p>
    @endif

    <form action="{{ route('cliente.portal.logout') }}" method="POST">
        @csrf
        <button type="submit">Sair</button>
    </form>
</body>
</html>
