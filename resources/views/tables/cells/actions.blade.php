<div class="btn-group" role="group">
    <a href="{{ route('processos.show', $row->id) }}" class="btn btn-sm btn-primary">Ver</a>
    <a href="{{ route('processos.edit', $row->id) }}" class="btn btn-sm btn-warning">Editar</a>
    <button wire:click="delete({{ $row->id }})" class="btn btn-sm btn-danger">Excluir</button>
</div>