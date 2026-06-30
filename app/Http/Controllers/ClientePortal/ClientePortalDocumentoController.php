<?php

namespace App\Http\Controllers\ClientePortal;

use App\Application\Arquivo\Services\ArquivoStorageService;
use App\Application\Arquivo\Actions\UploadDocumentoClientePortalAction;
use App\Domains\Arquivo\Enums\DocumentoCategoriaEnum;
use App\Domains\Arquivo\ValueObjects\S3Path;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\DocumentoArquivo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View as ViewFactory;
use Illuminate\View\View;

class ClientePortalDocumentoController extends Controller
{
    public function index(): RedirectResponse|View
    {
        $portal = Auth::guard('cliente_portal')->user();
        $customer = $portal->customer;
        $documentos = DocumentoArquivo::query()
            ->with('uploadedBy:id,name')
            ->where('empresa_id', $portal->empresa_id)
            ->where('customer_id', $portal->customer_id)
            ->where('contexto', 'customer')
            ->where('documentable_type', Customer::class)
            ->where('documentable_id', $portal->customer_id)
            ->latest('id')
            ->limit(100)
            ->get()
            ->filter(fn (DocumentoArquivo $documento): bool => Gate::forUser($portal)->allows('viewPortal', $documento))
            ->values();

        if (! ViewFactory::exists('WebSite.ClienteAppPage.documentos')) {
            return redirect()
                ->route('cliente.portal.dashboard')
                ->with('status', 'A listagem de documentos do Portal Cliente ainda não possui uma view dedicada.');
        }

        return view('WebSite.ClienteAppPage.documentos', compact('portal', 'customer', 'documentos'));
    }

    public function upload(Request $request, UploadDocumentoClientePortalAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'documento' => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx'],
            'categoria' => ['nullable', 'string', 'in:documentos,documentos_identificacao,contratos,comprovativos,recibos,outros'],
            'observacao' => ['nullable', 'string', 'max:500'],
        ]);

        $categoria = DocumentoCategoriaEnum::tryFrom((string) ($validated['categoria'] ?? DocumentoCategoriaEnum::DOCUMENTOS->value))
            ?? DocumentoCategoriaEnum::DOCUMENTOS;

        try {
            $action->execute(
                Auth::guard('cliente_portal')->user(),
                $request->file('documento'),
                $categoria,
                trim((string) ($validated['observacao'] ?? '')) ?: null,
            );
        } catch (\Throwable $exception) {
            return back()->with('status', 'Não foi possível enviar o documento: ' . $exception->getMessage());
        }

        return back()->with('status', 'Documento enviado com sucesso.');
    }

    public function download(int $documentoId, ArquivoStorageService $storage): RedirectResponse
    {
        $portal = Auth::guard('cliente_portal')->user();
        $documento = DocumentoArquivo::query()
            ->where('empresa_id', $portal->empresa_id)
            ->where('customer_id', $portal->customer_id)
            ->where('contexto', 'customer')
            ->where('documentable_type', Customer::class)
            ->where('documentable_id', $portal->customer_id)
            ->whereKey($documentoId)
            ->firstOrFail();

        Gate::forUser($portal)->authorize('downloadPortal', $documento);
        abort_unless((bool) $documento->storage_key, 404);
        abort_unless($storage->isConfigured(), 503, 'S3 não configurado para Gestão Documental.');

        return redirect()->away($storage->temporaryUrl(new S3Path($documento->storage_key), 5));
    }
}
