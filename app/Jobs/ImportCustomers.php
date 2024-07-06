<?php 

// app/Jobs/ImportCustomers.php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CustomersImport;
use App\Models\Migracao;
use Illuminate\Support\Facades\Log;
use App\Notifications\ImportCompleted;

class ImportCustomers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $importId;

    public function __construct($filePath, $importId)
    {
        $this->filePath = $filePath;
        $this->importId = $importId;
    }

    public function handle()
    {
        $import = Migracao::find($this->importId);
        try {
            Excel::import(new CustomersImport, $this->filePath);
            $import->status = 'completed';
            $import->save();

            // Notificar o usuário
            $user = auth()->user();
            $empresa = $import->empresa;
            if ($user) {
                $user->notify(new ImportCompleted('clientes', $empresa));
            }
        } catch (\Exception $e) {
            $import->status = 'failed';
            $import->save();
            Log::error('Erro na importação de clientes: ' . $e->getMessage());
        }
    }
}
