<?php

namespace App\Http\Controllers;

use App\Application\Arquivo\Actions\DeleteDocumentoAction;
use App\Application\Arquivo\Actions\GerarUrlAssinadaAction;
use App\Models\DocumentoArquivo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class DocumentoArquivoController extends AuthenticatedController
{
    public function preview(DocumentoArquivo $documentoArquivo, GerarUrlAssinadaAction $action): RedirectResponse
    {
        $url = $action->execute((int) $documentoArquivo->id, Auth::user(), 10);

        return redirect()->away($url);
    }

    public function download(DocumentoArquivo $documentoArquivo, GerarUrlAssinadaAction $action): RedirectResponse
    {
        $url = $action->execute((int) $documentoArquivo->id, Auth::user(), 5);

        return redirect()->away($url);
    }

    public function destroy(DocumentoArquivo $documentoArquivo, DeleteDocumentoAction $action): RedirectResponse
    {
        $action->execute((int) $documentoArquivo->id, Auth::user());

        return back()->with('success', 'Documento removido com sucesso.');
    }
}
