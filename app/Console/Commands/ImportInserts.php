<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportInserts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:inserts {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa inserts de um arquivo txt para o banco de dados';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('file');

        // Verifica se o arquivo existe
        if (!File::exists($filePath)) {
            $this->error("O arquivo não foi encontrado: $filePath");
            return;
        }

        // Lê o conteúdo do arquivo
        $content = File::get($filePath);
        // Divide as queries pelo ponto e vírgula e remove espaços em branco
        $queries = array_filter(array_map('trim', explode(';', $content)));

        // Inicia a transação
        DB::beginTransaction();

        try {
            foreach ($queries as $query) {
                if (!empty($query)) {
                    // Executa cada comando insert
                    DB::statement($query);
                }
            }
            // Confirma a transação se tudo correr bem
            DB::commit();
            $this->info('Inserções realizadas com sucesso!');
        } catch (\Exception $e) {
            // Reverte a transação em caso de erro
            DB::rollBack();
            $this->error('Erro ao importar: ' . $e->getMessage());
        }
    }
}
