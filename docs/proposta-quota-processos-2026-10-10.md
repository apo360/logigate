# Quota mensal de processos — proposta para revisão

## Regras confirmadas

A quota pertence à empresa e ao mês civil no fuso `Africa/Luanda`, independentemente de pagamento mensal, trimestral, semestral ou anual. Renova no primeiro dia do mês, sem acumulação. Apenas novos processos criados com sucesso consomem uma unidade. Consultar e actualizar não consomem; eliminar não devolve unidades.

O bloqueio de criação permanece desactivado. Não foi implementado um contador baseado nos processos sobreviventes nem no período contratado: ambos violariam estas regras. As restantes correcções de subscrições não dependem desta aprovação.

## Tratamento recomendado

| Situação | Regra proposta | Exemplo |
|---|---|---|
| Subscrição iniciada a meio do mês | Quota mensal completa, sem rateio. Descontar consumo já registado pela empresa nesse mês; começar uma subscrição não reinicia o consumo. | Básico iniciado dia 20, sem consumo anterior: 100 criações até ao fim do mês; mais 100 no mês seguinte. |
| Mudança de plano | Manter o consumo do mês e aplicar o limite do plano quando a mudança produzir efeito. A implementação actual agenda a mudança para o fim do período pago. | 60 unidades consumidas e mudança efectiva para limite 500: restam 440. |
| Redução de plano | Se o consumo já exceder o novo limite, impedir apenas novas criações até ao mês seguinte. Preservar processos existentes e permitir consulta/actualização. | Consumo 120, novo limite 100: restam 0; no mês seguinte ficam disponíveis 100. |
| Importação | Cada novo processo efectivamente criado consome uma unidade. Actualizações, duplicados identificados e tentativas revertidas não consomem. | Um ficheiro com 30 novos e 20 actualizações consome 30 unidades. |
| Importação sem saldo suficiente | Validar a quantidade de novos processos antes de gravar e rejeitar o lote inteiro com mensagem de saldo disponível. Não importar parcialmente por defeito. | Restam 10 e o lote cria 30: rejeitar o lote, indicando 10 disponíveis. |
| Repetição de importação/pedido | Usar chave de idempotência para impedir processo e consumo duplicados. | Reenviar o mesmo item confirmado não cobra outra unidade. |

Os limites não se somam quando se muda de plano e não se renova a quota no dia de pagamento. Rateios financeiros ou mudanças imediatas não foram acrescentados ao fluxo actual.

## Registo técnico proposto, ainda não implementado

Manter um registo permanente de consumo por empresa, mês local e identificador do processo/item importado, com unicidade para impedir duplicação. O registo de consumo e a criação do processo devem ocorrer na mesma transacção; falhas e rollback não consomem. O consumo permanece mesmo após eliminação do processo, sem depender de uma relação que o apague em cascata.

Serializar a verificação e o incremento por empresa/mês para evitar que criações concorrentes ultrapassem o saldo. Delimitar o mês em Angola e converter os instantes para o fuso de armazenamento. Todos os canais de criação, incluindo importação, devem passar pelo mesmo serviço.

## Transição dos contratos actuais

Recomendo iniciar a contagem no primeiro dia de um mês após aprovação, com consumo inicial zero, preservando os acessos existentes. Não reconstruir consumo histórico contando apenas processos existentes: os eliminados tornariam esse valor incorrecto. Antes de activar bloqueios em contratos antigos, confirmar também os limites comerciais aplicáveis e comunicar a data de início.

## Pontos a rever antes da implementação

1. Quota completa no primeiro mês, sem rateio.
2. Consumo mensal da empresa mantido nas mudanças de plano, incluindo reduções.
3. Importação atómica quando o saldo é insuficiente; importação parcial apenas se vier a ser explicitamente solicitada.
4. Data de início e aplicação aos contratos existentes, sem reconstrução histórica incompleta.

Os limites de utilizadores e armazenamento preparados para contratos com condições guardadas continuam separados desta decisão. As associações de módulos também continuam sem aplicação automática.
