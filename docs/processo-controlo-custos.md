# Controlo de Custos e Tributos

O menu existente de Processos / Impostos e Tarifas passa a chamar-se **Controlo de Custos e Tributos**, mantendo ID, posição, módulo, slug e rota `processos.tarifa` (`/processo/tarifas`). A migração não altera valores dos processos nem converte automaticamente despesas em pagamentos.

## Utilização

1. Abra um processo na área de controlo ou use **Controlo de custos** na edição.
2. Para importações, abra o simulador, confira os parâmetros e escolha **Guardar versão da simulação**. Os resultados são recalculados no servidor. Uma versão contém autor, data, parâmetros, valores de entrada e taxas. Dados incompletos produzem uma versão parcial; alterações nas entradas ou taxas tornam a estimativa desactualizada.
3. Registe os valores em AOA com âmbito **Tributos** ou **Outros custos**, data, referência e descrição. Associe um comprovativo activo do arquivo do próprio processo. Sem comprovativo, o registo fica pendente e não confirma o total da fase. Anule com motivo e crie outro registo para corrigir.
4. **Declarado** representa o total numa revisão, usando a última revisão activa. **Pago**, **Despesa** e **Facturado** são parcelas cumulativas. Uma parcela não confirmada impede apresentar a fase como um total confirmado. Ausência de informação é apresentada como **Por confirmar**; zero exige registo explícito com origem.
5. Para **Facturado**, seleccione uma FT/FR emitida ou paga em AOA, da mesma empresa, processo e cliente. Indique a parcela efectivamente atribuída ao âmbito; associe o pagamento/despesa individual para controlar a recuperação. O conjunto das afectações não pode exceder a factura nem o valor da origem.
6. Use **Actualizar conferência** após alterações nas fontes. A gravação/anulação de registos também actualiza a conferência. Guardar uma versão de simulação actualiza a conferência quando o autor também pode consultar e actualizar o processo. A consulta calcula as condições actuais sem escrever na base; a fila geral reflecte a última conferência guardada de cada processo. Os critérios alterados exigem nova conferência dos restantes processos.
7. Atribua responsável da empresa, prazo e explicação a cada pendência. Pode encaminhar, justificar ou resolver. A condição continua visível enquanto for detectada. Novos valores ou reaparecimento da condição reabrem a pendência, preservando o histórico anterior.

## Regras de conferência

- Estimativa de tributos versus último total declarado; pagamentos versus declarado; despesas documentadas versus total do formulário de despesas. É necessário conferir se a composição e o âmbito são equivalentes.
- Registos sem comprovativo activo ou com factura anulada/eliminada deixam os valores por confirmar. Os estados documentais pendentes não confirmam valores.
- Pagamentos confirmados sem afectações válidas de facturação geram pendências de recuperação. Pagamentos de clientes na conta corrente e pagamentos da subscrição SaaS não são pagamentos aduaneiros.
- A conferência antes do fecho assinala declaração, pagamento ou facturação de tributos ainda por confirmar. É uma lista de revisão; não altera as regras existentes de finalização nem emite documentos fiscais.
- Operações comparáveis usam apenas declarações documentadas da própria empresa: mesma natureza (incluindo legado separado), regime por abreviatura e código, moeda e conjunto de códigos pautais. Usa-se a última revisão activa de cada processo e o valor aduaneiro guardado nessa revisão. A comparação é pela taxa efectiva `tributos / valor aduaneiro`, não pelo montante bruto.
- Por omissão: janela de 180 dias, mínimo de 3 comparáveis, desvio relativo superior a 10% e absoluto superior a 1.000 Kz. Ambos os limiares têm de ser ultrapassados; uma base zero usa apenas o absoluto. A pesquisa examina até 500 declarações recentes, e a interface identifica a amostra e as referências.
- O simulador existente calcula direitos de importação, IVA e IEQ conforme as taxas cadastradas. Não foi acrescentada uma fórmula fiscal para exportação de crude. Estes processos utilizam os registos documentados; a estimativa de importação não se aplica.

## Permissões e persistência

`processos.view` permite consultar; `processos.update` permite registar/anular, tratar alertas e alterar os critérios da empresa; `processos.simulate` permite calcular/guardar versões. A montagem do simulador nas páginas de edição e visualização respeita esta permissão e a natureza da operação. O arquivo mantém as permissões próprias de consulta e download.

As operações revalidam a empresa activa e a autorização no servidor. As afectações usam bloqueios transaccionais do processo e da factura. As tabelas `processo_custo_*` guardam versões, registos, alertas, critérios e eventos com empresa/autor. Não há endpoints de alteração ou exclusão de versões e eventos; os registos financeiros são anulados com motivo. O histórico mostra os últimos 100 eventos.

Para outra instalação, aplicar apenas as duas migrações desta funcionalidade, seguindo o fluxo de deploy do projecto:

```sh
php artisan migrate --path=database/migrations/2026_10_17_120000_create_processo_custo_control.php
php artisan migrate --path=database/migrations/2026_10_17_120001_link_existing_custo_menu.php
npm run build
```

As bases locais `logigate_testing` e a sandbox preparada foram migradas pelo script `scripts/apply-processo-custos-local.php`, que verifica a preservação dos dados anteriores e dos IDs dos menus. O script recusa outras bases.

## Verificação

`tests/Feature/Processo/ControloCustosTest.php` cobre persistência Livewire, valores desconhecidos/zero, revisões, versões e taxas históricas, dados incompletos, âmbitos financeiros, comprovativos pendentes, facturas anuladas, limites de afectação, duplicações, permissões, isolamento entre empresas, responsáveis, recorrência e comparação normalizada.

`scripts/verify-processo-custos.cjs` verifica o HTML real exportado pelo teste, navegação, valores enviados ao Livewire, recuperação do botão e apresentação em 320/390/768/1440 px. Os pedidos de escrita são simulados exclusivamente nessa verificação de navegador; a persistência real é testada em PHP/MySQL com transacções revertidas. Resultados e capturas ficam em `storage/app/processo-custos-qa`.

Verificação local em 17/10/2026: 13 testes novos passaram; na suite completa de Processos passaram 73 de 74 testes (622 asserções). A falha remanescente é a expectativa já existente de 404 em `ProcessoTenantIsolationTest:28`, que recebe 403. A compilação Vite passou. A fila apresenta até 20 pendências ordenadas por prazo dos processos filtrados.
