<?php

namespace App\Http\Controllers;

use App\Domains\Banco\Services\BancoListService;
use App\Domains\Banco\Services\IbanValidatorService;
use App\Domains\Banco\Services\EmpresaBancoService;
use App\Application\Banco\DTOs\EmpresaBancoDTO;
use App\Domains\Banco\Exceptions\IbanInvalidoException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IbanController extends Controller
{
    public function __construct(
        private IbanValidatorService $validator,
        private EmpresaBancoService $bancoService
    ) {}

    public function getBankDetails()
    {
        return BancoListService::getOptions();
    }

    public function validateIban(Request $request)
    {
        $request->validate(['iban' => 'required|string']);

        try {
            $detalhes = $this->validator->validate($request->input('iban'));
            return response()->json($detalhes);
        } catch (IbanInvalidoException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function insertConta(Request $request)
    {
        $request->validate([
            'banco' => 'required|string',
            'iban-input' => 'required|string',
            'conta-input' => 'required|string',
        ]);

        $empresaId = Auth::user()->empresas->first()->id;
        $dto = EmpresaBancoDTO::fromRequest($request->all(), $empresaId);

        try {
            $this->bancoService->criarConta($dto);
            return redirect()->back()->with('success', 'Conta Bancária inserida com sucesso.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Erro ao inserir conta: ' . $e->getMessage()]);
        }
    }
}