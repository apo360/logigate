<?php

namespace App\Livewire\Arquivo;

use App\Application\Arquivo\Actions\DeleteDocumentoAction;
use App\Application\Arquivo\Actions\GerarUrlAssinadaAction;
use App\Application\Arquivo\Actions\ListarDocumentosAction;
use App\Application\Arquivo\Actions\UploadDocumentoAction;
use App\Application\Arquivo\DTOs\UploadDocumentoDTO;
use App\Domains\Arquivo\Enums\DocumentoCategoriaEnum;
use App\Domains\Arquivo\Enums\DocumentoContextoEnum;
use App\Models\Customer;
use App\Models\Licenciamento;
use App\Models\Processo;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class DocumentosManager extends Component
{
    use WithFileUploads;

    public string $contexto;
    public int $entidadeId;
    public string $categoria = 'documentos';
    public array $files = [];
    public $documentos;

    public function mount(string $contexto, int $entidadeId): void
    {
        $this->contexto = $contexto;
        $this->entidadeId = $entidadeId;
        $this->documentos = collect();
        $this->loadDocumentos();
    }

    public function uploadDocumentos(UploadDocumentoAction $action): void
    {
        $this->validate([
            'categoria' => ['required', 'string'],
            'files' => ['required', 'array', 'min:1'],
            'files.*' => ['file'],
        ]);

        $contexto = DocumentoContextoEnum::from($this->contexto);
        $categoria = DocumentoCategoriaEnum::from($this->categoria);

        try {
            foreach ($this->files as $file) {
                $action->execute(new UploadDocumentoDTO(
                    file: $file,
                    contexto: $contexto,
                    categoria: $categoria,
                    entidadeId: $this->entidadeId,
                    uploadedBy: (int) Auth::id(),
                ));
            }
        } catch (\Throwable $e) {
            session()->flash('error', 'Erro ao carregar documento: ' . $e->getMessage());
            return;
        }

        $this->reset('files');
        $this->loadDocumentos();
        $this->dispatch('toast', type: 'success', message: 'Documento(s) carregado(s) com sucesso.');
    }

    public function preview(int $documentoId, GerarUrlAssinadaAction $action)
    {
        return redirect()->away($action->execute($documentoId, Auth::user(), 10));
    }

    public function download(int $documentoId, GerarUrlAssinadaAction $action)
    {
        return redirect()->away($action->execute($documentoId, Auth::user(), 5));
    }

    public function remover(int $documentoId, DeleteDocumentoAction $action): void
    {
        $action->execute($documentoId, Auth::user());
        $this->loadDocumentos();
        $this->dispatch('toast', type: 'success', message: 'Documento removido com sucesso.');
    }

    public function render()
    {
        return view('livewire.arquivo.documentos-manager', [
            'categorias' => DocumentoCategoriaEnum::cases(),
        ]);
    }

    private function loadDocumentos(): void
    {
        if (! Auth::user()) {
            $this->documentos = collect();
            return;
        }

        $this->documentos = app(ListarDocumentosAction::class)->execute(
            DocumentoContextoEnum::from($this->contexto),
            $this->entidadeId,
            $this->resolveEmpresaId(),
            Auth::user()
        );
    }

    private function resolveEmpresaId(): int
    {
        return match ($this->contexto) {
            DocumentoContextoEnum::PROCESSO->value => (int) Processo::query()->whereKey($this->entidadeId)->value('empresa_id'),
            DocumentoContextoEnum::LICENCIAMENTO->value => (int) Licenciamento::query()->whereKey($this->entidadeId)->value('empresa_id'),
            DocumentoContextoEnum::CUSTOMER->value => (int) (Customer::query()->whereKey($this->entidadeId)->value('empresa_id') ?: Auth::user()->empresas()->value('empresas.id')),
            default => (int) Auth::user()->empresas()->value('empresas.id'),
        };
    }
}
