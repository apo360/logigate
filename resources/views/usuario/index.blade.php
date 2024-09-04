<x-app-layout>
    <div style="padding: 10px;">
        <h3>Lista de Usúarios</h3>
        <div class="card">

        </div>
        {{ dd($users) }}
        
        <table class="table">
            <thead>
                <th>Nome Completo</th>
                <th>Função</th>
                <th>Role</th>
                <th>...</th>
            </thead>
            <tbody>
                @foreach($users as $user_)
                    <tr>
                        <td> {{$user_->name}} </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-app-layout>