<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\ImportCustomers;
use App\Jobs\ImportExportadores;
use App\Jobs\ImportProcessos;
use App\Models\Migracao;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MigracaoController extends AuthenticatedController
{
    public function create(){
        $imports = Migracao::where('empresa_id', \App\Support\TenantContext::empresa()->id)->get();
        return view('empresa.migracao', compact('imports'));
    }

    public function importCustomers(Request $request)
    {
        $this->authorize('create', \App\Models\Customer::class);
        abort_unless(\Illuminate\Support\Facades\Schema::hasColumn((new Migracao())->getTable(), 'result'), 503, 'Actualize o schema das importações antes de usar esta função.');
        $request->validate(['file' => 'required|file|mimes:xlsx,csv|max:10240']);
        $filePath = $request->file('file')->store('imports');
        $import = Migracao::create([
            'type' => 'clientes',
            'file_path' => $filePath,
            'status' => 'pending',
            'actor_id' => Auth::id(),
            'empresa_id' => \App\Support\TenantContext::empresa()->id,
        ]);

        ImportCustomers::dispatch($filePath, $import->id, (int) $this->empresa->id, (int) Auth::id());

        return back()->with('success', 'A importação de clientes foi iniciada. Consulte o resultado no histórico de importações.');
    }

    public function importExportadores(Request $request)
    {
        $this->authorize('create', \App\Models\Exportador::class);
        $request->validate(['file' => 'required|mimes:xlsx,csv']);
        $filePath = $request->file('file')->store('imports');
        $import = Migracao::create([
            'type' => 'exportadores',
            'file_path' => $filePath,
            'status' => 'pending',
            'empresa_id' => \App\Support\TenantContext::empresa()->id,
        ]);

        ImportExportadores::dispatch($filePath, $import->id, (int) $this->empresa->id, (int) Auth::id());

        return back()->with('success', 'A importação de exportadores foi iniciada. Consulte o resultado no histórico de importações.');
    }

    public function importProcessos(Request $request)
    {
        $this->authorize('create', \App\Models\Processo::class);
        abort_unless(\Illuminate\Support\Facades\Schema::hasColumn((new Migracao())->getTable(), 'result'), 503, 'Actualize o schema das importações antes de usar esta função.');
        $request->validate(['file' => 'required|file|mimes:xlsx,csv|max:10240']);
        $filePath = $request->file('file')->store('imports');
        $import = Migracao::create([
            'type' => 'processos',
            'file_path' => $filePath,
            'status' => 'pending',
            'actor_id' => Auth::id(),
            'empresa_id' => \App\Support\TenantContext::empresa()->id,
        ]);

        ImportProcessos::dispatch($filePath, $import->id, (int) $this->empresa->id, (int) Auth::id());

        return back()->with('success', 'A importação de processos foi iniciada. Consulte o resultado no histórico de importações.');
    }
}
