# Correcções de Plano e Subscrição

Data: 10/10/2026. Ambiente validado: PHP 8.5.10, Laravel 12.69.3 e Livewire 3.8.10.

## Resultado técnico

- Contratação, renovação e cancelamento restritos a Administrador e Gestor Financeiro da empresa; isolamento entre empresas no checkout, pagamentos e recibos.
- Condições comerciais guardadas na subscrição: preço, modalidade, IVA, limites e módulos. Alterar posteriormente o catálogo não altera a cobrança já preparada. Preços inválidos e ciclos pagos com preço zero são rejeitados.
- Checkout canónico sem depender do campo `conta`; rotas e componentes antigos encaminhados para o fluxo actual. Escolher um plano gratuito não activa uma subscrição paga pendente.
- Reserva de pagamento com idempotência e bloqueio da empresa; chamada HTTP fora da transacção da reserva. Pagamentos abertos são reutilizados. Falhas definitivas e resultados incertos são tratados separadamente para evitar cobranças duplicadas. Identificadores comerciais únicos substituem o identificador previsível por segundo.
- Webhooks assinados conservam o evento recebido e o estado de processamento. Confirmação do pagamento e activação ocorrem na mesma transacção. Repetições, eventos anteriores à criação local do pagamento e falhas de activação são recuperáveis. Pagamentos confirmados não são rebaixados por respostas tardias.
- Direitos de módulos consideram empresa, datas, estado e condições guardadas. Renovações/mudanças pagas antecipadamente começam no fim do período pago actual. Expiração não transforma activaçōes antigas em revogações explícitas.
- Cancelar uma escolha pendente não elimina acesso pago; cancelar renovação conserva o período contratado. Histórico paginado, recibo interno de pagamento e avisos na aplicação acrescentados.
- Limites de utilizadores e armazenamento verificados nos fluxos ajustados para contratos com condições guardadas. Contratos antigos sem essas condições preservam o comportamento anterior. Upload normal e upload do portal validam armazenamento antes de gravar e serializam uploads da empresa.

## Dados e validação

A migração local adicionou as condições comerciais, campos de recuperação de webhooks, chave de idempotência e tabela de notificações. A verificação por impressão digital das colunas comerciais originais confirmou preservação de **4 planos, 8 associações plano/módulo, 16 activaçōes, 2 subscrições e 0 pagamentos**.

Os testes foram executados exclusivamente numa base descartável com estrutura clonada, sem copiar dados comerciais. HTTP externo foi impedido e os serviços de armazenamento/pagamento foram substituídos nos testes. Resultado: **60 testes, 219 verificações, todos aprovados**. Cobrem confirmação e recuperação de pagamentos, isolamento, autorização, contratos futuros, planos gratuitos, preços, limites e integração com a navegação. `npm run build` terminou com sucesso.

Não foram efectuadas cobranças reais nem enviados emails. As alterações anteriores do menu foram preservadas.

## Decisões comerciais mantidas para revisão

Os três planos pagos sem módulos associados continuam disponíveis: não foram acrescentadas associações nem bloqueadas vendas. O comando `billing:review-plans` apresenta problemas de configuração de forma consultiva e distingue planos comercializados, inactivos/rascunhos e arquivados. Ver [catálogo e proposta de correspondência](proposta-modulos-planos-2026-10-10.md).

A quota de processos ficou **sem bloqueio**. A regra confirmada é mês civil em Angola, sem acumulação, apenas criações bem sucedidas e sem devolução por eliminação. Não se conta por duração do contrato nem por processos ainda existentes. Ver [tratamento proposto para mudanças, importações e primeiro mês](proposta-quota-processos-2026-10-10.md), antes de implementar o registo de consumo.

## Limitações e operação

`billing:maintain` recupera eventos/pagamentos pendentes de activação e expira contratos; `billing:maintain --notices` acrescenta avisos internos de renovação. Foram registados no scheduler da aplicação. O ambiente publicado precisa de executar o scheduler Laravel e dispor de cache com locks adequados; estes comandos não cobram automaticamente clientes.

Renovação é um fluxo explícito de contratação e pagamento. Débito recorrente automático requer contrato/mandato e configuração do prestador, ainda não implementados. O recibo é um comprovativo interno, **não uma factura fiscal**; emissão fiscal requer dados do emitente, regras de séries e integração própria.

A confirmação ponta a ponta com AppyPay real continua a exigir validação no ambiente do prestador. Os testes locais não certificam a disponibilidade externa nem o formato de todos os eventos possíveis. A medição de armazenamento usa documentos registados no sistema; não é um inventário integral de ficheiros antigos do bucket nem migra os serviços legados de cópia em massa.
