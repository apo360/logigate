<?php
// app/Domains/Banco/Services/IbanValidatorService.php
namespace App\Domains\Banco\Services;

use App\Domains\Banco\Exceptions\IbanInvalidoException;

class IbanValidatorService
{
    public function validate(string $iban): array
    {
        $iban = preg_replace("/[^A-Z0-9]/", "", strtoupper($iban));

        if (!preg_match("/^([A-Z]{2})(\d{2})([A-Z\d]+)$/", $iban, $matches)) {
            throw IbanInvalidoException::formatoInvalido();
        }

        $pais = $matches[1];
        if ($pais !== 'AO') {
            throw IbanInvalidoException::paisNaoSuportado($pais);
        }

        $digitos = $this->converterLetrasParaNumeros($matches[3] . $matches[1] . $matches[2]);
        $checksum = $this->mod97($digitos);

        if ($checksum !== 1) {
            throw IbanInvalidoException::checksumInvalido();
        }

        $codigoBanco = substr($digitos, 0, 4);
        $banco = BancoListService::findByCode($codigoBanco);
        
        if (!$banco) {
            throw new IbanInvalidoException("Código de banco não reconhecido");
        }

        return [
            'code' => $banco['code'],
            'swift' => $banco['swift'],
            'sname' => $banco['sname'],
            'fname' => $banco['fname'],
        ];
    }

    private function converterLetrasParaNumeros(string $string): string
    {
        return preg_replace_callback("/[A-Z]/", function ($letra) {
            return ord($letra[0]) - 55;
        }, $string);
    }

    private function mod97(string $string): int
    {
        $checksum = substr($string, 0, 1);
        for ($offset = 2; $offset < strlen($string); $offset += 7) {
            $checksum = (int)($checksum . substr($string, $offset, 7)) % 97;
        }
        return $checksum;
    }
}