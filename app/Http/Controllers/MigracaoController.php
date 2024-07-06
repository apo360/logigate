<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\ImportCustomers;
use App\Jobs\ImportExportadores;
use App\Jobs\ImportProcessos;
use App\Models\Migracao;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MigracaoController extends Controller
{
    public function create(){
        $imports = Migracao::where('empresa_id', Auth::user()->empresas->first()->id)->get();
        return view('empresa.migracao', compact('imports'));
    }

    public function importCustomers(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,csv']);
        $filePath = $request->file('file')->store('imports');
        $import = Migracao::create([
            'type' => 'clientes',
            'file_path' => $filePath,
            'status' => 'pending',
            'empresa_id' => Auth::user()->empresas->first()->id,
        ]);

        ImportCustomers::dispatch($filePath, $import->id);

        return back()->with('success', 'A importação de clientes foi iniciada. Você será notificado quando estiver completa.');
    }

    public function importExportadores(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,csv']);
        $filePath = $request->file('file')->store('imports');
        $import = Migracao::create([
            'type' => 'exportadores',
            'file_path' => $filePath,
            'status' => 'pending',
            'empresa_id' => Auth::user()->empresas->first()->id,
        ]);

        ImportExportadores::dispatch($filePath, $import->id);

        return back()->with('success', 'A importação de exportadores foi iniciada. Você será notificado quando estiver completa.');
    }

    public function importProcessos(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,csv']);
        $filePath = $request->file('file')->store('imports');
        $import = Migracao::create([
            'type' => 'processos',
            'file_path' => $filePath,
            'status' => 'pending',
            'empresa_id' => Auth::user()->empresas->first()->id,
        ]);

        ImportProcessos::dispatch($filePath, $import->id);

        return back()->with('success', 'A importação de processos foi iniciada. Você será notificado quando estiver completa.');
    }
}
