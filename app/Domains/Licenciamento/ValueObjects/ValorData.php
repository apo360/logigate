<?php

namespace App\Domains\Licenciamento\ValueObjects;

class ValorData
{
    /**
     * ValorData é um Value Object que representa uma data no contexto do licenciamento. Ele encapsula a lógica de validação e formatação de datas, garantindo que as datas sejam sempre válidas e consistentes em toda a aplicação. O formato esperado para a data é "YYYY-MM-DD", mas isso pode ser ajustado conforme necessário para atender aos requisitos específicos do sistema.
     * A classe ValorData pode ser utilizada em diversas partes do sistema onde uma data é necessária, como na criação de licenciamento, atualização de status, ou qualquer outra funcionalidade que envolva manipulação de datas. Ela ajuda a centralizar a lógica relacionada a datas, facilitando a manutenção e a evolução do código.
     * Exemplo de uso:
     * $dataAbertura = new ValorData('2024-06-01');
     * echo $dataAbertura; // Saída: 2024-06-01
     * $dataInvalida = new ValorData('2024-13-01'); // Lança uma InvalidArgumentException devido à data inválida
     * Determinar o formato de data e as regras de validação é crucial para garantir a integridade dos dados e evitar erros relacionados a datas em toda a aplicação. A classe ValorData é uma ferramenta valiosa para alcançar esse objetivo, proporcionando uma maneira consistente e confiável de lidar com datas no contexto do licenciamento.
     * Determinar as diferenças entre ValorData e outras classes de data, como Carbon, é importante para entender quando usar cada uma. Enquanto Carbon é uma biblioteca poderosa para manipulação de datas e horas, ValorData é um Value Object específico para representar datas no contexto do licenciamento, com regras de validação e formatação personalizadas. A escolha entre eles dependerá das necessidades específicas do sistema e do nível de complexidade necessário para lidar com datas em diferentes partes da aplicação.
     * Determinar que DataFecho não pode ser anterior a DataAbertura é uma regra de negócio importante para garantir a consistência dos dados. Isso pode ser implementado dentro da classe ValorData ou em uma camada de serviço que gerencia a lógica de negócios relacionada ao licenciamento. Garantir que DataFecho seja posterior a DataAbertura ajuda a evitar erros e inconsistências no sistema, garantindo que as datas sejam sempre lógicas e coerentes com o fluxo natural do tempo.
     */

    private \DateTimeImmutable $dateTime;

    public function __construct(string $data, string $formato = 'Y-m-d')
    {
        $dateTime = \DateTimeImmutable::createFromFormat($formato, $data);
        if (!$dateTime || $dateTime->format($formato) !== $data) {
            throw new \InvalidArgumentException("Data inválida ou formato incorreto: $data (esperado $formato)");
        }
        $this->dateTime = $dateTime;
    }

    public function getData(string $formato = 'Y-m-d'): string
    {
        return $this->dateTime->format($formato);
    }

    public function toDateTimeImmutable(): \DateTimeImmutable
    {
        return $this->dateTime;
    }

    public function equals(ValorData $other): bool
    {
        return $this->dateTime == $other->dateTime;
    }

    public function isAfter(ValorData $other): bool
    {
        return $this->dateTime > $other->dateTime;
    }

    public function isBefore(ValorData $other): bool
    {
        return $this->dateTime < $other->dateTime;
    }

    public function __toString(): string
    {
        return $this->getData();
    }
}