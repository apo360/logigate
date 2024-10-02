<x-app-layout>
    <x-breadcrumb :items="[
        ['name' => 'Empresa', 'url' => route('empresas.show', auth()->user()->empresas->first()->id)],
        ['name' => 'Usuarios', 'url' => route('usuarios.index')],
        ['name' => 'Lista de  Usuarios', 'url' => '']
    ]" separator="/" />

    <div class="card card-dark">
        <div class="card-header">
            <div class="card-title">Lista de Usuários</div>
        </div>
        <div class="card-body">

            <!-- Botão para criar novo usuário -->
            <a href="{{ route('usuarios.create') }}" class="btn btn-primary mb-3">Adicionar Usuário</a>

            <!-- Barra de pesquisa -->
            <form method="GET" action="{{ route('usuarios.index') }}" class="mb-3">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Pesquisar Usuários" value="{{ request('search') }}">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="submit">Pesquisar</button>
                    </div>
                </div>
            </form>

            <!-- Tabela de Usuários -->
            <table class="table table-sm table-hover table-flex">
                <thead>
                    <tr>
                        <th>Imagem</th>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Função</th>
                        <th>Última sessão</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                    <tr>
                        <td>
                            <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="rounded-circle" width="40" height="40" 
                                 style="border: 2px solid {{ $user->is_active ? 'green' : 'red' }};">
                        </td>
                        <td>
                            <a href="{{ route('usuarios.show', $user->id) }}">{{ $user->name }}</a>
                        </td>
                        <td>
                            {{ $user->email }}
                            @if(!$user->email_verified_at) 
                                <i class="fa fa-exclamation-triangle" title="E-mail não Verificado" style="color: darkgoldenrod;"></i> 
                            @endif
                        </td>
                        <td>{{ $user->roles->pluck('name')->first() }}</td>
                        <td>
                            @if($user->last_login_at)
                                {{ $user->last_login_at->format('d/m/Y H:i') }}
                            @else
                                Nunca
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('usuarios.permissions', $user->id) }}" class="btn btn-sm btn-secondary" data-toggle="tooltip" title="Gerir Permissões">Gerir Permissões</a>
                            @if($user->is_blocked)
                                <a href="#" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#unblockModal{{ $user->id }}"><i class="fas fa-lock" title="Desbloquear"></i></a>
                            @else
                                <a href="#" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#blockModal{{ $user->id }}"><i class="fas fa-unlock" title="Bloquear"></i></a>
                            @endif
                            <a href="#" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#resetPasswordModal{{ $user->id }}">Reiniciar Senha</a>
                        </td>
                    </tr>
<!-- Ensure you have included jQuery and Bootstrap JS at the end of the body tag -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
                    <!-- Modal para desbloquear -->
                    <div class="modal fade" id="unblockModal{{ $user->id }}" tabindex="-1" role="dialog" aria-labelledby="unblockModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="unblockModalLabel">Desbloquear Usuário</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    Tem certeza de que deseja desbloquear {{ $user->name }}?
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                    <a href="{{ route('usuarios.unblock', $user->id) }}" class="btn btn-danger">Desbloquear</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal para bloquear -->
                    <div class="modal fade" id="blockModal{{ $user->id }}" tabindex="-1" role="dialog" aria-labelledby="blockModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="blockModalLabel">Bloquear Usuário</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    Tem certeza de que deseja bloquear {{ $user->name }}?
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                    <a href="{{ route('usuarios.block', $user->id) }}" class="btn btn-warning">Bloquear</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal para reiniciar senha -->
                    <div class="modal fade" id="resetPasswordModal{{ $user->id }}" tabindex="-1" role="dialog" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="resetPasswordModalLabel">Reiniciar Senha</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    Tem certeza de que deseja reiniciar a senha para {{ $user->name }}?
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                    <a href="{{ route('usuarios.resetPassword', $user->id) }}" class="btn btn-primary">Reiniciar</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    @endforeach
                </tbody>
            </table>

            <!-- Paginação -->
            
        </div>
    </div>
</x-app-layout>
