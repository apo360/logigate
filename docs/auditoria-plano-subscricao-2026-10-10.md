# Auditoria do sub-módulo Plano e Subscrição

> Relatório histórico anterior às reparações. Consultar o [relatório das correcções](correcoes-plano-subscricao-2026-10-10.md) para o estado actual. As reproduções de falhas foram substituídas por testes de aceitação das correcções.

Data: 10/10/2026. Revisão do código local e do esquema MySQL configurado como `logigate_testing`. Ambiente observado: PHP 8.5.10, Laravel 12.69.3 e Livewire 3.8.10.

## Conclusão

O sub-módulo é um **MVP parcial com uma arquitectura aproveitável, mas ainda sem estabilidade suficiente para operação paga**. Existem catálogo, escolha de periodicidade, checkout GPO/REF, webhook e activação de módulos. Falhas de transição entre estes componentes permitem activar um plano pago sem pagamento e deixar um pagamento confirmado sem acesso correspondente. A gestão do ciclo comercial completo também está incompleta.

Não foram alterados controllers, componentes, modelos, views ou dados comerciais nesta auditoria. Foram acrescentados instrumentos de diagnóstico e este relatório. As alterações anteriores à navegação foram preservadas.

## Inventário e percurso principal

| Camada | Componentes | Papel / situação |
|---|---|---|
| Rotas | `routes/web.php:100–101`, `routes/web.php:57`, `routes/api.php` | Catálogo e contratação autenticados; checkout com parâmetro obrigatório `conta`; endpoint AppyPay. |
| Controller principal | `app/Http/Controllers/BillingPlanController.php` | Consulta o estado, lista planos activos e cria/reutiliza subscrição pendente. |
| Livewire | `app/Livewire/CheckoutPayment.php` | Inicia pagamentos, apresenta referência/estado e consulta confirmação GPO. |
| Livewire / layout | `app/Livewire/SubscriptionWidget.php`, `resources/views/layouts/app.blade.php:241` | Resumo da subscrição, dias restantes, acesso a pagamento/renovação. |
| Views | `resources/views/billing/plans.blade.php`, `resources/views/pagamentos/pagamento-quick.blade.php`, `resources/views/livewire/checkout-payment.blade.php`, `resources/views/checkout/*.blade.php`, `resources/views/livewire/subscription-widget.blade.php` | Catálogo, estado activo/pendente, escolha do método, instruções e sucesso. |
| Casos de uso | `app/Application/Billing/UseCases/{StartCheckout,StartGpoPayment,StartRefPayment,FallbackToRefPayment,BuildPaymentViewData,HandleAppyPayWebhook}.php` | Coordenação do checkout, cálculo do valor, integração e confirmação. |
| Domínio | `app/Domains/Billing/Enums/*`, `app/Domains/Billing/ValueObjects/*`, `app/Application/Billing/DTOs/*` | Estados, ciclos, método, telefone, dinheiro e contratos internos. |
| Gateway | `app/Infrastructure/PaymentGateways/AppyPay/*`, `app/Http/Controllers/AppyPayWebhookController.php` | Clientes GPO/REF, autenticação e transformação das respostas; verificação HMAC do webhook. |
| Activação | `app/Events/SubscriptionActivated.php`, `app/Listeners/ActivateSubscription.php`, `app/Actions/Subscriptions/ActivateSubscriptionAction.php`, `app/Services/ModuloAtivacaoService.php` | Activa subscrição, determina datas e provisiona módulos vinculados à subscrição. |
| Acesso comercial | `app/Services/EffectiveModuleResolver.php`, `app/Http/Middleware/{CheckSubscription,EnsureModuleEntitlement}.php`, `app/Support/TenantContext.php` | Empresa corrente e verificação efectiva de subscrição/módulos. |
| Modelos principais | `Plano`, `PlanoModulo`, `PlanoItem`, `Subscricao`, `PagamentoOnline`, `WebhookEvent`, `Module`, `ActivatedModule`, `Empresa` | Catálogo, contrato, transacções, eventos e direitos comerciais. |

Percurso pretendido: **empresa → plano/ciclo → subscrição pendente → pagamento → webhook autenticado → activação → módulos → menus/acesso**. O percurso existe, mas os pontos entre pagamento e activação não oferecem recuperação consistente.

## Tabelas e estado observado

Os valores seguintes descrevem apenas a base local auditada, não uma base de produção.

| Tabela | Registos | Observação |
|---|---:|---|
| `planos` | 4 | Todos `activo`; preços dos quatro ciclos preenchidos; um preço não positivo em cada ciclo. Há unicidade de `codigo`. |
| `plano_modulos` | 8 | Só um dos quatro planos tem módulos associados. Os outros três podem activar sem provisionar módulos. Há FKs, mas não unicidade de `(plano_id, modulo_id)`. |
| `plano_items` | 0 | Catálogo de benefícios ainda não preenchido; `PlanoItem::$fillable` está vazio. |
| `modules` | 8 | Catálogo funcional e hierarquia por `parent_id`. |
| `subscricoes` | 2 | Ambas `ativa`; nenhuma sem plano. FKs de empresa/plano; sem restrição específica para impedir várias contratações pendentes da mesma empresa. |
| `activated_modules` | 16 | Todas ligadas a subscrições; FKs presentes. Sem unicidade comercial composta; activação depende também dos locks/idempotência da aplicação. |
| `pagamento_onlines` | 0 | Campos de método, valor, moeda, IDs, referência e datas presentes. MTID único e FK de subscrição; `empresa_id` só tem índice. `idempotency_key` sem índice único. |
| `webhook_events` | 0 | Unicidade `(provider, event_id)` presente; só existe `processed_at`, sem estado de falha/retry ou payload para reprocessamento autónomo. |
| `pagamentos` | 0 | Estrutura mista do fluxo antigo; não é a tabela do checkout SaaS principal. |
| `metodos_pagamento` | 0 | Assistente antigo não dispõe de métodos válidos. GPO/REF principal usa enums e não depende desta tabela. |

O esquema bruto, índices e FKs foram guardados em `storage/app/billing-audit/schema.json`. Nenhum payload de cliente ou pagamento foi incluído.

## Falhas confirmadas por reprodução

| Prioridade | Evidência | Consequência |
|---|---|---|
| **P0** | `BillingPlanController.php:97–124`: reutiliza a última pendente, mas decide a activação gratuita pelo plano acabado de seleccionar. Teste: pendente de plano pago + pedido de plano gratuito. | A pendente paga passa a `ativa`, conserva o plano pago e não tem pagamento. É uma falha de integridade comercial, mesmo quando o catálogo está oculto pela UI. |
| **P0** | `HandleAppyPayWebhook.php:106`: `SubscriptionActivated::dispatch(...)->afterCommit()`. O evento usa `Illuminate\Foundation\Events\Dispatchable`; a publicação devolve respostas dos listeners, não um objecto de job. | Confirmação paga termina com erro. O listener síncrono pode activar antes do erro, pelo que não é correcto afirmar que todas as confirmações deixam de activar. |
| **P0** | Evento marcado processado e pagamento marcado `paid` antes da execução do listener. Teste com falha simulada de activação e repetição do mesmo evento. | A repetição devolve `duplicate`; pagamento continua pago e subscrição pendente. Falta recuperação de activações. |
| **P1** | `HandleAppyPayWebhook.php:42–66`: grava o evento mesmo sem encontrar pagamento. Teste: evento → criação posterior do pagamento → repetição. | Evento fica consumido; repetição não actualiza pagamento. A chamada ao gateway dentro da transacção de `StartCheckout` torna plausível o callback chegar antes de o pagamento estar visível; essa corrida real não foi ensaiada. Controller responde 200 a `payment_not_found`. |
| **P1** | `MerchantTransactionId::generate()` usa método + relógio com resolução de um segundo. Teste com duas gerações do mesmo método no mesmo segundo. | IDs idênticos colidem com o índice único de pagamentos, inclusive entre empresas diferentes. |
| **P1** | `BillingPlanController::start()` valida existência do plano, sem exigir `status=activo`. Teste com plano inactivo e conta válida. | Pedido directo contrata plano retirado do catálogo. |
| **P1** | `empresa.conta` é nullable, mas checkout exige `{conta}`. Teste com empresa sem conta. | Contratação paga lança `UrlGenerationException`; a criação dentro da transacção é revertida. O link da view pendente também exige esse campo. |
| **P2** | Consulta do controller usa estado e expiração, sem validar `data_inicio`; resolver comercial valida início. Teste com subscrição futura. | Página mostra subscrição activa enquanto o controlo comercial ainda recusa acesso. |

## Problemas demonstrados pela leitura do código

1. **Estado do gateway ignorado na criação.** `AppyPayResponseMapper::success()` devolve `success=true` e um estado separado. `StartGpoPayment.php:48` e `StartRefPayment.php:61` consultam apenas o booleano. Uma resposta HTTP bem sucedida com estado comercial `Failed` pode ficar localmente `processing`/`pending`; a recuperação/fallback pode não ocorrer. Necessário testar os estados reais do contrato do gateway.
2. **Referência sem acompanhamento automático.** `CheckoutPayment::getShouldPollProperty()` só faz polling GPO. A view REF não tem refresh manual. O webhook pode confirmar no servidor, mas a página aberta com referência não avança automaticamente para sucesso.
3. **Preço sem fotografia contratual suficiente.** Criação de pagamento recalcula pelo preço actual do plano e factor fixo `1.14`, em vez de preservar integralmente a cotação aceite. Nome, ciclo, benefícios, base e taxa devem ter uma fotografia imutável. `valor_pago` na subscrição já recebe o preço base antes de existir pagamento; o significado difere do total pago. Esta é uma observação técnica, não uma validação fiscal da taxa.
4. **Fontes de estado diferentes.** Widget prefere registos `ativa`, incluindo expirados, antes de pendentes recentes. Controller, relação da empresa, macros de compatibilidade e resolver têm critérios distintos. Centralizar a escolha da subscrição efectiva e a consulta da pendente.
5. **Chamadas externas com transacção aberta.** `StartCheckout` mantém lock da empresa e transacção enquanto GPO/REF chama HTTP. Separar reserva persistente, pedido externo e confirmação/reconciliação para reduzir bloqueios e lidar com timeout/rollback depois de aceitação externa.
6. **Endurecimento de ownership.** `CheckoutPayment::refreshPaymentStatus()` procura `paymentId` público sem filtro explícito de empresa. A relação `Subscricao` tem escopo de tenant em HTTP, portanto esta auditoria **não confirmou fuga entre empresas**. Ainda assim, IDs/queries devem ser ligados explicitamente à empresa e cobertos por testes de manipulação Livewire. Também definir quem, entre membros da empresa, tem autoridade para contratar/renovar.
7. **Ciclo comercial incompleto.** Não foi encontrada execução das quotas de utilizadores/processos/armazenamento. `renovacao_automatica` existe no modelo sem cobrança recorrente identificada. Faltam percurso completo de upgrade/downgrade, cancelamento, reconciliação, histórico e facturação/recibo SaaS. `ActivateSubscriptionAction` contém comentários para factura/registo financeiro, sem execução. O resolver bloqueia direitos expirados, mas isso não substitui manutenção e comunicação do ciclo de cobrança.

## Código antigo e duplicado

O fluxo antigo ainda está acessível por `GET /subscricao/{empresa}` (`routes/web.php:234`) → `ModuleSubscriptionController::show()` → `empresa.subscricao` → `SubscriptionWizard`. A resposta 410 da rota POST antiga não bloqueia os métodos Livewire do assistente.

`SubscriptionWizard::finalizarSubscricao()` usa total público sem recalcular e validar todos os dados no servidor no passo final; tenta criar `Pagamento` com campos que o modelo não permite em mass assignment, não envia `metodo_pagamento_id`, e redirecciona para `pagamento.referencia`, sem rota identificada. Não há transacção que una subscrição e pagamento. Os extras guardados em JSON não são provisionados pelo serviço principal de módulos. Há ainda propriedade pública `empresa` e computed `getEmpresaProperty()` com o mesmo nome, que devem ser normalizados. Este fluxo precisa de ser retirado da navegação ou adaptado ao mesmo caso de uso principal.

`Empresa\EmpresaSubscricao` é outra implementação de contratação; não foi encontrada montagem/rota activa na pesquisa. `PlanoController` e `PagamentoOnlineController` contêm CRUD vazio. `CheckoutService`, `SubscricaoController` e serviços `app/Services/AppyPay/*` coexistem com a integração nova; não devem ser considerados todos caminhos activos sem identificar consumidores.

Em particular, `SubscricaoController::checkExpiradas()` usa `data_fim`, `module_id` e `deactivated_at`, ausentes nas tabelas auditadas. Não foi identificado agendamento deste método: é dívida técnica latente, não falha de cron demonstrada. `Plano::getPreco()` não trata trimestral como o enum principal. `SubscricaoServiceProvider` mantém macros de compatibilidade com critérios próprios de estado/módulos.

## O que já está bem encaminhado

- Separação entre domínio, DTOs, casos de uso e infraestrutura de pagamento.
- Empresa corrente definida por `TenantContext`, com campo da empresa bloqueado no checkout e verificação na hidratação.
- Preço calculado no servidor no fluxo principal, telefone validado e protecção básica contra cliques concorrentes.
- Verificação HMAC do corpo bruto do webhook, comparação `hash_equals`, rejeição de segredo/assinatura ausentes.
- Índice único de eventos e MTID; locks de empresa/subscrição; activação com `activated_at` e provisionamento vinculado à subscrição.
- Direitos comerciais verificam empresa, datas e módulos; os registos locais de activação não estão órfãos.
- O código carregou e os cenários auditados executaram no PHP/Laravel actuais. As falhas centrais encontradas são de lógica e integração; não são apenas incompatibilidades de sintaxe com PHP 8.5.

## Maturidade por dimensão

| Dimensão | Avaliação |
|---|---|
| Arquitectura e modelos | Intermédia; boa separação nova, com caminhos antigos ainda divergentes. |
| Catálogo e configuração comercial | Parcial; três planos sem módulos e quotas sem execução identificada. |
| Contratação inicial | Parcial; falha crítica de activação e validações incompletas. |
| Checkout e confirmação | Baixa estabilidade; estados, IDs, callback e recuperação precisam de correcção. |
| Provisionamento e controlo de acesso | Base funcional; deve receber o estado comercial correcto e consistente. |
| Renovação, facturação e operações | Incompleta; campos e botões não equivalem a um ciclo executável. |
| Evidência para produção | Insuficiente; base sem histórico de pagamentos e sem ensaio real do gateway nesta auditoria. |

## Ordem recomendada para tornar funcional

1. Corrigir P0: correspondência plano/ciclo/pendente, activação gratuita apenas do contrato correcto e confirmação paga recuperável. Tornar evento/activação idempotentes após falha, e distinguir recebido/processado/falhado.
2. Corrigir P1: gerar MTID único dentro do limite suportado, persistir antes da chamada externa, reprocessar callbacks antecipados, aplicar estado comercial do gateway e validar plano activo/conta disponível.
3. Consolidar a UI num fluxo: redireccionar o assistente antigo para `billing.plans`, unificar estado do widget, acompanhar REF e apresentar histórico de pagamentos e erros recuperáveis.
4. Completar dados dos planos, fotografia de preço/impostos/benefícios, autorização comercial por papel e execução de quotas. Definir explicitamente os planos sem módulos como intencionais ou preencher associações.
5. Implementar renovação/cancelamento/facturação conforme a política comercial e validar ponta a ponta em sandbox AppyPay: GPO e REF pagos/recusados/expirados, timeout, duplicação, callbacks fora de ordem, falha de activação e isolamento entre empresas.

Critério mínimo de conclusão: contrato correcto + pagamento reconciliável + activação exactamente uma vez + módulos correctos + estado igual na página/widget/middleware, com recuperação demonstrada após falha.

## Verificação realizada e limites

Executado `php tests/run-billing-audit-isolated.php`: **15 testes, 46 assertions**. São **8 reproduções diagnósticas de defeitos/riscos confirmados** e **7 testes unitários existentes** de domínio/mapper. O sucesso dos testes diagnósticos confirma os comportamentos problemáticos descritos; não significa que o módulo passou critérios de aceitação.

O runner criou uma base com nome aleatório restrito, copiou apenas estrutura, usou fixtures, bloqueou pedidos HTTP externos e eliminou a própria base no fim. Nenhuma migration, cobrança real ou alteração dos dados da base local foi feita. A primeira execução revelou a ausência de `conta` na fixture; esse caso foi separado num diagnóstico específico e o teste de plano inactivo passou a usar conta válida.

Não foram ensaiados callbacks reais AppyPay, concorrência com múltiplos processos, facturação fiscal, entrega de emails ou implantação. O clone estrutural usa `CREATE TABLE LIKE`, que não transporta FKs; as FKs foram verificadas separadamente por leitura do esquema original, e os testes não constituem validação de todas as restrições SQL. Existem testes adicionais de segurança/webhook no repositório que não foram executados nesta auditoria para evitar os seus percursos de migrations sobre a base comercial.

Artefactos: `scripts/audit-billing-schema.php`, `tests/run-billing-audit-isolated.php`, `tests/Feature/Billing/BillingMaturityAuditTest.php`, `storage/app/billing-audit/schema.json` e `storage/logs/billing-audit-tests.txt`.
