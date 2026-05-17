<?php

namespace App\Domains\Banco\Exceptions;

use Exception;

class IbanInvalidoException extends Exception
{
    /**
     * Cria uma nova exceção para IBAN inválido.
     *
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(string $message = "IBAN inválido", int $code = 400, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Exceção específica para formato inválido.
     */
    public static function formatoInvalido(): self
    {
        return new self("Formato do IBAN não é válido.");
    }

    /**
     * Exceção para país não suportado.
     */
    public static function paisNaoSuportado(string $pais): self
    {
        return new self("O país '{$pais}' não é suportado (apenas AO é aceito).");
    }

    /**
     * Exceção para checksum inválido (cálculo módulo 97 falhou).
     */
    public static function checksumInvalido(): self
    {
        return new self("Checksum do IBAN inválido. Verifique os dígitos de controle.");
    }

    /**
     * Exceção para banco não reconhecido.
     */
    public static function bancoNaoReconhecido(string $codigoBanco): self
    {
        return new self("Código de banco '{$codigoBanco}' não reconhecido na lista de instituições angolanas.");
    }
}