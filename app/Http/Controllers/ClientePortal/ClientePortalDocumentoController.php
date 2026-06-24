<?php

namespace App\Http\Controllers\ClientePortal;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View as ViewFactory;
use Illuminate\View\View;

class ClientePortalDocumentoController extends Controller
{
    private const PORTAL_VISIBILIDADES = ['cliente', 'portal', 'publico'];

    public function index(): RedirectResponse|View
    {
        $customer = Auth::guard('cliente_portal')->user()->customer;
        $documentos = $customer->documentosArquivos()
            ->whereIn('visibilidade', self::PORTAL_VISIBILIDADES)
            ->latest('id')
            ->paginate(15);

        if (! ViewFactory::exists('WebSite.ClienteAppPage.documentos')) {
            return redirect()
                ->route('cliente.portal.dashboard')
                ->with('status', 'A listagem de documentos do Portal Cliente ainda não possui uma view dedicada.');
        }

        return view('WebSite.ClienteAppPage.documentos', compact('customer', 'documentos'));
    }

    public function download(int $documentoId): RedirectResponse
    {
        $customer = Auth::guard('cliente_portal')->user()->customer;
        $documento = $customer->documentosArquivos()
            ->whereKey($documentoId)
            ->firstOrFail();

        abort_unless(in_array($documento->visibilidade, self::PORTAL_VISIBILIDADES, true), 403);
        abort_unless((bool) $documento->storage_key, 404);

        $disk = $documento->storage_disk ?: config('filesystems.default', 's3');
        $url = Storage::disk($disk)->temporaryUrl($documento->storage_key, now()->addMinutes(5));

        return redirect()->away($url);
    }
}
