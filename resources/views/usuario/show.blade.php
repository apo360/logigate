<x-app-layout>
    <head>
        <style>
            .custom-switch .custom-control-input:checked ~ .custom-control-label::before {
                background-color: #28a745; /* Cor do fundo quando ativo */
                border-color: #28a745; /* Cor da borda quando ativo */
            }

            .custom-switch .custom-control-label::before {
                border-radius: 0.5rem; /* Arredondar borda do switch */
            }
        </style>
    </head>

    <x-breadcrumb :items="[
        ['name' => 'Empresa', 'url' => route('empresas.show', auth()->user()->empresas->first()->id)],
        ['name' => 'Usuarios', 'url' => route('usuarios.index')],
        ['name' => 'Detalhe do Usuario', 'url' => '']
    ]" separator="/" />

    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <div class="d-flex align-items-center">
                <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="rounded-circle" width="40" height="40">
                <span class="ml-2">{{ $user->name }}</span>
            </div>
            
            <div class="float-right d-flex align-items-center">
                <a href="{{ route('usuarios.edit', $user->id) }}" class="mr-2"><i class="fas fa-edit"></i></a>
                <form action="{{ $user->is_blocked ? route('usuarios.unblock', $user->id) : route('usuarios.block', $user->id) }}" method="GET" class="d-inline">
                    @csrf
                    
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="toggleSwitch{{ $user->id }}" name="is_active" 
                            {{ !$user->is_blocked ? 'checked' : '' }} 
                            onchange="this.form.submit()">
                        <label class="custom-control-label" for="toggleSwitch{{ $user->id }}">
                            {{ $user->is_blocked ? 'Bloqueado' : 'Ativo' }}
                        </label>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card-body">
            <p><strong>Email:</strong> {{ $user->email }}</p>
            <p><strong>Função:</strong> {{ $user->roles->pluck('name')->implode(', ') }}</p>
            <p><strong>Última sessão:</strong> {{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Nunca' }}</p>
            <p><strong>IP de Registro:</strong> {{ $user->ip_address }}</p>
            <p><strong>Data de Criação:</strong> {{ $user->created_at->format('d/m/Y H:i') }}</p>
            <p><strong>Data de Atualização:</strong> {{ $user->updated_at->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <span>Auditorias</span>
            </div>
        </div>
        
        <div class="card-body">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Evento</th>
                        <th>Data</th>
                        <th>IP</th>
                        <th>Alterações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($user->audits as $audit)
                        <tr>
                            <td>{{ ucfirst($audit->event) }}</td>
                            <td>{{ $audit->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $audit->ip_address }}</td>
                            <td>
                                <!-- Tabela de comparação entre valores antigos e novos -->
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Atributo</th>
                                            <th>Valor Antigo</th>
                                            <th>Valor Novo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($audit->new_values as $key => $newValue)
                                            <tr>
                                                <td>{{ ucfirst($key) }}</td>
                                                <td>{{ $audit->old_values[$key] ?? 'N/A' }}</td> <!-- Exibe 'N/A' se não houver valor antigo -->
                                                <td>{{ $newValue }}</td>
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
</x-app-layout>
