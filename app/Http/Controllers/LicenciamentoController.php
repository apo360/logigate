<?php

namespace App\Http\Controllers;

use App\Application\Licenciamento\Actions\ConstituirProcessoAction;
use App\Application\Licenciamento\Actions\DuplicarLicenciamentoAction;
use App\Application\Licenciamento\Actions\ExcluirLicenciamentoAction;
use App\Application\Licenciamento\Actions\Export\ExportLicenciamentosToCsvAction;
use App\Application\Licenciamento\Actions\Export\ExportLicenciamentosToExcelAction;
use App\Application\Licenciamento\Actions\GerarTxtLicenciamentoAction;
use App\Application\Licenciamento\Support\LicenciamentoFormSupport;
use App\Helpers\DatabaseErrorHandler;
use App\Domains\Licenciamento\Services\LicenciamentoImportExportService;
use App\Models\Licenciamento;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use App\Services\LicenciamentoService;
use Illuminate\Support\Facades\Auth;

class LicenciamentoController extends AuthenticatedController
{
    public function __construct(private readonly LicenciamentoService $licenciamentoService)
    {
        parent::__construct();
        $this->authorizeResource(Licenciamento::class, 'licenciamento');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Retornar a view com os licenciamentos paginados
        return view('Licenciamento.index');
    }
    
    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // A lógica de criação agora é tratada pelo Livewire, então apenas retornamos a view que contém o componente Livewire.
        return view('Licenciamento.create', [
                'customer_id' => $request->query('customer_id')
            ]);
    }

    public function storeDraft(Request $request){
        $this->authorize('create', Licenciamento::class);

        try {
            $this->licenciamentoService->createDraft($request->all(), $this->empresa->id);

            return redirect()->back()->with('warning', 'Licenciamento Salvo como Rascunho');
        } catch (QueryException $th) {
            return DatabaseErrorHandler::handle($th, $request);
        }
    }

    /**
     * Display the specified resource.
     */

    public function show(Licenciamento $licenciamento)
    {
        // A lógica de exibição agora é tratada pelo Livewire, então apenas retornamos a view que contém o componente Livewire.
        return view('Licenciamento.show', compact('licenciamento'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Licenciamento $licenciamento)
    {
        // Continue com o processo de edição
        return view('Licenciamento.edit', compact('licenciamento'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Licenciamento $licenciamento, ExcluirLicenciamentoAction $action)
    {
        try {
            $action->execute((int) $licenciamento->id);

            return redirect()->route('licenciamentos.index')
                            ->with('success', 'Licenciamento excluído com sucesso!');
        } catch (\Exception $e) {
            return redirect()->route('licenciamentos.index')
                            ->with('error', $e->getMessage() ?: 'Erro ao excluir o licenciamento. Tente novamente.');
        }
    }

    public function GerarTxT($IdProcesso, GerarTxtLicenciamentoAction $action)
    {
        $licenciamento = $this->resolveAuthorizedLicenciamento($IdProcesso, 'generateTxt');
        $result = $action->execute($licenciamento);

        return response()->streamDownload(function () use ($result): void {
            echo $result['content'];
        }, $result['filename']);
    }

    public function ConstituirProcesso($idLicenciamento, ConstituirProcessoAction $action)
    {
        $licenciamento = $this->resolveAuthorizedLicenciamento($idLicenciamento, 'constituteProcesso');
        $processo = $action->execute($licenciamento);

        return redirect()->route('processos.edit', $processo)->with('success', 'Processo constituído com sucesso!');
    }

    public function DuplicarLicenciamento($idLicenciamento, DuplicarLicenciamentoAction $action)
    {
        $licenciamento = $this->resolveAuthorizedLicenciamento($idLicenciamento, 'duplicate');
        $novo = $action->execute($licenciamento);

        return redirect()->route('licenciamentos.show', $novo)->with('success', 'Licenciamento duplicado com sucesso!');
    }

    public function exportCsv(ExportLicenciamentosToCsvAction $action)
    {
        $this->authorize('viewAny', Licenciamento::class);

        return $action->execute([]);
    }

    public function exportExcel(ExportLicenciamentosToExcelAction $action)
    {
        $this->authorize('viewAny', Licenciamento::class);

        return $action->execute([]);
    }

    public function import(Request $request, LicenciamentoImportExportService $service)
    {
        $this->authorize('create', Licenciamento::class);

        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls,txt|max:10240',
        ]);

        try {
            $licenciamento = $service->import($request->file('file'), $this->empresa->id, auth()->id());

            return redirect()->route('licenciamentos.show', $licenciamento)->with('success', 'Importação concluída!');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function pice()
    {
        $this->authorize('viewAny', Licenciamento::class);

        return view('Licenciamento.index');
    }

    private function resolveAuthorizedLicenciamento(mixed $licenciamento, string $ability): Licenciamento
    {
        if (!$licenciamento instanceof Licenciamento) {
            $empresaId = Auth::user()?->empresas()->value('empresas.id');
            abort_if(!$empresaId, 404);

            $licenciamento = Licenciamento::query()
                ->whereKey($licenciamento)
                ->where('empresa_id', $empresaId)
                ->firstOrFail();
        }

        $this->authorize($ability, $licenciamento);

        return $licenciamento->load(app(LicenciamentoFormSupport::class)->relations());
    }
}
