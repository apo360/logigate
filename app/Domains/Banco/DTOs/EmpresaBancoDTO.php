<?php
// app/Application/Banco/DTOs/EmpresaBancoDTO.php
namespace App\Application\Banco\DTOs;

class EmpresaBancoDTO
{
    public function __construct(
        public readonly int $empresa_id,
        public readonly string $code_banco,
        public readonly string $iban,
        public readonly string $conta
    ) {}

    public static function fromRequest(array $data, int $empresaId): self
    {
        return new self(
            empresa_id: $empresaId,
            code_banco: $data['banco'],
            iban: $data['iban-input'],
            conta: $data['conta-input']
        );
    }

    public function toArray(): array
    {
        return [
            'empresa_id' => $this->empresa_id,
            'code_banco' => $this->code_banco,
            'iban' => $this->iban,
            'conta' => $this->conta,
        ];
    }
}