<?php

    namespace App\Http\Controllers\WebPage;

    use App\Http\Controllers\Controller;
    use App\Models\Licenciamento;
    use App\Models\MercadoriaAgrupada;
    use App\Models\PautaAduaneira;
    use Illuminate\Http\Request;

    class RastreamentoController extends Controller
    {
        public function consultarLicenciamento()
        {
            // Exibir o formulário de consulta
            return view('WebSite.rastreamento');
        }

        public function resultadoConsulta(Request $request)
        {
            // Validar o campo de pesquisa
            $request->validate([
                'codigo_licenciamento' => 'required|string',
            ]);

            $pauta = PautaAduaneira::all();

            // Buscar o licenciamento pelo código fornecido
            $licenciamento = Licenciamento::where('codigo_licenciamento', $request->codigo_licenciamento)->first();

            if (!$licenciamento) {
                // Retorna um erro se o código não for encontrado
                return redirect()->back()->with('error', 'Licenciamento não encontrado para o código fornecido.');
            }

            $mercadoriaAgrupadas = MercadoriaAgrupada::with('mercadorias')->where('licenciamento_id',$licenciamento->id)->get();

            // Se encontrado, redireciona para a página de detalhes ou exibe o resultado diretamente
            return view('WebSite.rastreamento_resultado', compact('licenciamento', 'mercadoriaAgrupadas', 'pauta'));
        }
    }
    