<?php

namespace App\Http\Controllers;

use App\Domains\Empresa\Actions\AtualizarEmpresaAction;
use App\Domains\Empresa\Actions\AtualizarLogotipoEmpresaAction;
use App\Domains\Empresa\Actions\CriarEmpresaAction;
use App\Domains\Empresa\Actions\ExcluirEmpresaAction;
use App\Domains\Empresa\Data\EmpresaData;
use App\Helpers\DatabaseErrorHandler;
use App\Http\Requests\EmpresaRequest;
use App\Models\Empresa;
use App\Models\EmpresaBanco;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Domains\Banco\Services\BancoListService;

class EmpresaController extends AuthenticatedController
{
    public function index()
    {
        $empresa = Auth::user()?->empresaAtiva();

        abort_unless($empresa, 403);

        return redirect()->route('empresas.edit', $empresa);
    }

    public function create()
    {
        return redirect()->route('empresas.index');
    }

    public function store(EmpresaRequest $request, CriarEmpresaAction $action)
    {
        try {
            $empresa = $action->execute(EmpresaData::fromArray($request->validated()));

            return $empresa;
        } catch (QueryException $e) {
            return DatabaseErrorHandler::handle($e, $request);
        }
    }

    public function storeLogo(Request $request, AtualizarLogotipoEmpresaAction $action)
    {
        $request->validate([
            'logotipo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        $empresa = Auth::user()?->empresaAtiva();
        abort_unless($empresa, 403);

        if (! $request->hasFile('logotipo')) {
            return redirect()->back()->with('success', 'Nenhum logotipo foi enviado.');
        }

        $action->execute(Auth::user(), $empresa, $request->file('logotipo'));

        return redirect()->back()->with('success', 'Logotipo actualizada com sucesso.');
    }

    public function show(Empresa $empresa)
    {
        $this->authorize('view', $empresa);

        return view('empresa.show', compact('empresa'));
    }

    public function edit(Empresa $empresa)
    {
        $this->authorize('update', $empresa);

        $listaBancos = BancoListService::getOptions();
        $contas = EmpresaBanco::where('empresa_id', $empresa->id)->get();

        return view('empresa.edit', compact('empresa', 'listaBancos', 'contas'));
    }

    public function update(Request $request, Empresa $empresa, AtualizarEmpresaAction $action)
    {
        $this->authorize('update', $empresa);

        $validated = $request->validate([
            'Empresa' => ['nullable', 'string', 'max:255'],
            'ActividadeComercial' => ['nullable', 'string', 'max:255'],
            'Designacao' => ['nullable', 'string', 'max:255'],
            'NIF' => ['nullable', 'string', 'max:100'],
            'Cedula' => ['nullable', 'string', 'max:100'],
            'Slogan' => ['nullable', 'string', 'max:100'],
            'Endereco_completo' => ['nullable', 'string', 'max:200'],
            'Provincia' => ['nullable', 'string', 'max:100'],
            'Cidade' => ['nullable', 'string', 'max:100'],
            'Dominio' => ['nullable', 'string', 'max:255'],
            'Email' => ['nullable', 'email', 'max:255'],
            'Fax' => ['nullable', 'string', 'max:100'],
            'Contacto_movel' => ['nullable', 'string', 'max:100'],
            'Contacto_fixo' => ['nullable', 'string', 'max:100'],
            'Sigla' => ['nullable', 'string', 'max:50'],
            'CodFactura' => ['nullable', 'string', 'max:50'],
            'CodProcesso' => ['nullable', 'string', 'max:50'],
            'ativo' => ['nullable', 'boolean'],
        ]);

        try {
            $action->execute(Auth::user(), $empresa, EmpresaData::fromArray($validated));

            return redirect()->back()->with('success', 'Empresa actualizada com sucesso.');
        } catch (QueryException $e) {
            return DatabaseErrorHandler::handle($e, $request);
        }
    }

    public function destroy(Empresa $empresa, ExcluirEmpresaAction $action)
    {
        $action->execute(Auth::user(), $empresa);

        return redirect()->route('empresas.index')->with('success', 'Empresa excluída com sucesso.');
    }
}
