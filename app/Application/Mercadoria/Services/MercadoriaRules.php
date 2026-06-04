<?php

namespace App\Application\Mercadoria\Services;

use App\Application\Mercadoria\DTOs\MercadoriaData;

final class MercadoriaRules
{
    public function validate(MercadoriaData $data): void
    {
        if (! in_array($data->context, ['processo', 'licenciamento'], true)) {
            throw new \InvalidArgumentException('Contexto de mercadoria inválido.');
        }

        if ($data->codigoAduaneiro === '') {
            throw new \InvalidArgumentException('O código aduaneiro é obrigatório.');
        }

        if ($data->quantidade <= 0) {
            throw new \InvalidArgumentException('A quantidade deve ser maior que zero.');
        }

        if ($data->precoUnitario < 0) {
            throw new \InvalidArgumentException('O preço unitário não pode ser negativo.');
        }

        $expectedTotal = round($data->quantidade * $data->precoUnitario, 2);

        if (abs($data->precoTotal - $expectedTotal) > 0.01) {
            throw new \InvalidArgumentException('O preço total deve ser igual a quantidade vezes o preço unitário.');
        }

        if ($this->isVehicleCode($data->codigoAduaneiro) && (! $data->marca || ! $data->modelo || ! $data->chassis)) {
            throw new \InvalidArgumentException('Marca, modelo e chassis são obrigatórios para veículos.');
        }

        if ($this->isMachineCode($data->codigoAduaneiro) && $data->potencia === null) {
            throw new \InvalidArgumentException('A potência é obrigatória para máquinas.');
        }
    }

    public function isVehicleCode(string $codigo): bool
    {
        foreach (['8701', '8702', '8703', '8704', '8705', '8706', '8707', '8709', '8711', '8712', '8713'] as $prefix) {
            if (str_starts_with($codigo, $prefix)) {
                return true;
            }
        }

        return false;
    }

    public function isMachineCode(string $codigo): bool
    {
        return str_starts_with($codigo, '84');
    }
}
