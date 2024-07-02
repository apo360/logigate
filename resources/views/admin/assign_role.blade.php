<h1>Atribuir Papel ao Usuário</h1>

    <form method="POST" action="{{ route('users.assignRole', $user->id) }}">
        @csrf
        <div>
            <label for="role">Selecione o Papel:</label>
            <select name="role" id="role" required>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit">Atribuir Papel</button>
    </form>