<?php
namespace App\Application\Licenciamento\Actions\Import;
use App\Models\Licenciamento;
use Illuminate\Http\UploadedFile;
class ImportLicenciamentosFromTxtAction
{
    public function execute(UploadedFile $file, int $empresaId, int $userId): Licenciamento
    {
        throw new \InvalidArgumentException('Importação TXT suspensa até validar o contrato dos vínculos e mercadorias.');
    }
}
