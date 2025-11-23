<?php

namespace App\Http\Controllers;

use App\Models\SalesInvoice;
use illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Saft\Header;
use App\Models\Saft\MasterFiles\Cliente;
use App\Models\Saft\MasterFiles\Impostos;
use App\Models\Saft\MasterFiles\Produtos_Servicos;
use App\Models\Saft\SourceDocuments\SalesInvoices;
use SimpleXMLElement;

class SAFtController extends Controller
{
    
    /**
     * Metodos auxiliares para construir secções específicas do SAF-T
     * @param SimpleXMLElement $xml
     * @param array $data
     * @return SimpleXMLElement
     * @method buildAddress
     * @method buildCustomers
     * @method buildProducts
     * @method buildTaxTables
     * @method buildHeader
     * @method buildSourceDocuments
    */

    protected $safTParser;
    protected $openAIService;

    public function index()
    {
        return view('safT.upload');
    }

    public function parseSAFT(Request $request)
    {
        // Validação usando rules do Laravel
        $request->validate([
            'safTFile' => 'required|file|mimes:xml|max:5120', // 5MB
        ]);

        $file = $request->file('safTFile');
        if (!$file || !$file->isValid()) {
            return response()->json(['error' => 'Invalid file'], 400);
        }

        $path = $file->store('saft_files');
        if (!$path) {
            return response()->json(['error' => 'Failed to store file'], 500);
        }

        try {
            $data = $this->safTParser->parse(storage_path("app/{$path}"));

            // Usar o serviço injetado em vez de instanciar diretamente
            if ($this->openAIService) {
                $aiResponse = $this->openAIService->analyze('Process the SAFT data: ' . json_encode($data));
            } else {
                $aiResponse = null;
            }

            Storage::delete($path); // limpeza

            return response()->json([
                'data' => $data,
                'ai' => $aiResponse,
            ]);
        } catch (\Exception $e) {
            Storage::delete($path);
            return response()->json(['error' => 'Failed to parse SAFT file: ' . $e->getMessage()], 500);
        }
    }

    public function showForm()
    {
        return view('safT.form');
    }
    public function handleForm(Request $request)
    {
        // Handle the form submission logic here
        // For example, you can validate the input and save it to the database
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            // Add more validation rules as needed
        ]);

        // Process the validated data (e.g., save to database)
        // ...

        return redirect()->back()->with('success', 'Form submitted successfully!');
    }
    public function downloadFile($fileName)
    {
        // Logic to download a specific file
        $filePath = storage_path("app/saft_files/{$fileName}");
        if (!file_exists($filePath)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        return response()->download($filePath, $fileName);
    }
    public function deleteFile($fileName)
    {
        // Logic to delete a specific file
        $filePath = storage_path("app/saft_files/{$fileName}");
        if (!file_exists($filePath)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        if (unlink($filePath)) {
            return response()->json(['message' => 'File deleted successfully']);
        } else {
            return response()->json(['error' => 'Failed to delete file'], 500);
        }
    }
    public function listFiles()
    {
        // Logic to list all files in the SAFT directory
        $files = Storage::files('saft_files');
        $fileList = array_map(function ($file) {
            return basename($file);
        }, $files);

        return response()->json(['files' => $fileList]);
    }

    // Métodos para construir o SAF-T em XML para download e submissão.
    public function buildSAFT($fiscalYear, $startDate, $endDate)
    {
        // Lógica para construir o ficheiro SAF-T em XML
        $Audit = new \SimpleXMLElement(
        '<AuditFile xmlns="urn:OECD:StandardAuditFile-Tax:AO_1.01_01" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"></AuditFile>');
        
        // Adicionar secção Header - Criar HeaderModel com dados
        $headerData = new Header([
            'taxAccountingBasis' => 'F',
            'fiscalYear' => $fiscalYear,
            'startDate'  => $startDate,
            'endDate'    => $endDate,
        ]);
        
        // Construir XML Header
        $headerData->buildHeader($Audit);

        $SalesInvoices = SalesInvoice::whereBetween('invoice_date', [$startDate, $endDate])->where('empresa_id', $this->empresa->id)->get();
        /*|---------------Master Files -------------------------|*/
        $masterFiles = $Audit->addChild('MasterFiles');
        /*
        |--------------------------------------------------------------------------
        | 1. CLIENTES – sem duplicações
        |--------------------------------------------------------------------------
        */
        $clientesBuilder = new Cliente();
        $distinctCustomers = $SalesInvoices->map(fn ($invoice) => $invoice->customer)->unique('CustomerID');
        foreach ($distinctCustomers as $customer) {
            if ($customer) { 
                $clientesBuilder->Clientebuild($masterFiles, $customer);
            }
        }
        /*
        |--------------------------------------------------------------------------
        | 2. PRODUTOS – sem duplicações
        |--------------------------------------------------------------------------
        */
        $productsBuilder = new Produtos_Servicos();
        $distinctProducts = $SalesInvoices->flatMap(fn ($inv) => $inv->salesitem)   // obter todas as linhas
        ->map(fn ($item) => $item->produto)  // obter produto da linha
        ->filter()                           // remover null
        ->unique('ProductCode');             // produtos distintos por código

        foreach ($distinctProducts as $product) {
            $productsBuilder->ProdutosServicosbuild($masterFiles, $product);
        }
        /*
        |--------------------------------------------------------------------------
        | 3. Impostos – sem duplicações
        |--------------------------------------------------------------------------
        */
        $taxTableBuilder = new Impostos();
        $distinctTaxes = $SalesInvoices->flatMap(fn ($inv) => $inv->salesitem)// linhas do documento
            ->map(fn ($item) => $item->produto?->price?->taxa)         // taxa associada ao preço
            ->filter()                                                 // remover null
            ->unique('id');                                            // taxas distintas

        $TaxTable = $masterFiles->addChild('TaxTable');
        foreach ($distinctTaxes as $tax) {
            $taxTableBuilder->Impostosbuild($TaxTable, $tax);
        }
        /*|----------------------------------- Finalização dos Master Files ---------------------------------------|*/

        /*|----------------------------------- Source Documents ---------------------------------------|*/
        $sourceDocuments = $Audit->addChild('SourceDocuments');
        
        $invoiceSourceBuilder = new SalesInvoices();
        $invoiceSourceBuilder->SalesInvoicesbuild($sourceDocuments, $SalesInvoices);


        /*|----------------------------------- Finalização dos Source Documents ---------------------------------------|*/

        // Converter para XML string
        $xmlString = $Audit->asXML();

        // Return as a downloadable file
        return response($xmlString, 200)->header('Content-Type', 'application/xml')->header('Content-Disposition', 'attachment; filename="saf-t.xml"');
    }

}