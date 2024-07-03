<!-- resources/views/tables/cells/actions.blade.php -->
<div class="d-flex justify-content-around">
    <a href="{{ route('processos.show', $row->id) }}" class="btn btn-info btn-sm">
        <i class="fas fa-eye"></i> Ver
    </a>
    <a href="{{ route('processos.edit', $row->id) }}" class="btn btn-warning btn-sm">
        <i class="fas fa-edit"></i> Editar
    </a>
    <form action="{{ route('processos.destroy', $row->id) }}" method="POST" style="display:inline;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger btn-sm">
            <i class="fas fa-trash-alt"></i> Excluir
        </button>
    </form>
</div>


