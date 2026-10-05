# Testes da V1 — 05/10/2026

Execução autorizada pelo utilizador após o adiamento anterior. Os resultados iniciais abaixo são preservados como histórico; a secção de resolução regista a execução posterior. Os ensaios operacionais continuam pendentes.

## Resultados finais

Actualização após a resolução das fixtures, permissões e Livewire: a execução conjunta dos mesmos 200 testes terminou com **199 aprovados, 1 ignorado, 0 falhas, 0 erros e 1766 assertions**. As 33 depreciações permanecem. Evidências: storage/logs/v1-resolved-tests.xml e storage/logs/v1-resolved-tests.txt. A tabela abaixo descreve a primeira execução.

| Bateria | Testes | Assertions | Falhas | Erros | Ignorados |
| --- | ---: | ---: | ---: | ---: | ---: |
| Regressões recentes, Exportadores, importações, fila e concorrência | 37 | 366 | 0 | 0 | 0 |
| Isolamento de empresa, RBAC, S3 sem chamadas reais e contratos ASYCUDA | 84 | 1012 | 0 | 0 | 1 |
| Bateria antiga dos módulos operacionais | 79 | 192 | 22 | 23 | 0 |
| Total, sem contar repetições de desenvolvimento | 200 | 1570 | 22 | 23 | 1 |

154 testes passaram. Cada execução reportou 33 depreciações; estas continuam pendentes. O teste ignorado exige LOGIGATE_DEMO_LOGIN/LOGIGATE_DEMO_PASSWORD e não foi executado com credenciais reais.

Evidências locais: storage/logs/v1-regression-tests.xml, storage/logs/v1-security-tests.xml, storage/logs/v1-legacy-tests.xml e storage/logs/v1-legacy-tests.txt. Estes ficheiros são artefactos locais da execução, não uma garantia de retenção pelo Git.

## Ambiente e protecções

PHP 8.5.10, Laravel 11, MySQL local. Os scripts tests/prepare-v1-sandbox.php e tests/upgrade-v1-sandbox.php prepararam o clone logigate_testing_v1_a76b0c1a31 a partir de logigate_testing. O nome fica no manifesto local ignorado tests/.v1-sandbox.json. O clone foi preservado para investigação; não foi removido automaticamente.

Foram aplicadas apenas no clone as migrations 2026_10_01_210001, 2026_10_01_210002, 2026_10_05_100001 e 2026_10_05_100002. O runner usa --v1-sandbox, valida o banco seleccionado e a ligação local. As baterias executadas usam transacções ou fixtures restritas ao clone, sem RefreshDatabase. HTTP inesperado foi bloqueado e os discos local/S3 foram simulados. Os workers concorrentes não criaram pastas no S3 real.

## Cobertura nova comprovada

- Exportador: modal de criação, evento de sucesso, detalhe e formulário de edição renderizados, actualização canónica, desassociação preservando o perfil, contador por empresa, pivot e tabela Livewire.
- Importações: linhas válidas/inválidas de Cliente e Licenciamento, referências de outra empresa, colunas proibidas de Processo, CIF recalculado, contexto de execução da fila restaurado, entrega duplicada e permissão revogada.
- Concorrência: dois processos PHP reservaram doze números distintos; duas conversões simultâneas devolveram um único processo e um único vínculo permanente. A conversão repetida continuou a devolver esse processo após remover os itens de teste.
- Regressões de Mercadoria, ciclo de Processo, DTO, guia fiscal, Customer e isolamento de empresa foram executadas em conjunto.

## Falhas da bateria antiga

O detalhe por método e stack trace está no JUnit e no log. Há problemas de preparação de fixtures e possíveis regressões funcionais; não se conclui que todas as falhas sejam apenas problemas dos testes.

- ProcessoShowActions: fixtures de permissões sem membership/contexto RBAC exigido. ProcessoPolicy: autorização positiva não satisfeita.
- ProcessoLivewire e LicenciamentoLivewire: estado esperado ausente, erros de acesso a valores nulos e diferenças entre 403/404.
- ImportarDeclaracaoAsycuda e operações de Contentor/Mercadoria: empresa activa ausente ou escrita recusada pelas protecções actuais.
- LicenciamentoShowActions: respostas 403 onde os testes esperam conteúdo da página, além de estado Livewire ausente.
- MercadoriaLicenciamentoLivewire: listagem/estado ausentes e expectativas antigas de incremento dos totais declarados, que precisam ser revistas conforme o contrato de FOB/pesos aprovado.
- EmpresaLivewireForms: fixtures dependem de empresas.id=1, inexistente no banco copiado; não foi criada uma empresa global artificial para contornar a validação.

Policies, tenancy e RBAC não foram enfraquecidos para tornar os testes verdes.

## Correcções verificadas durante os testes

- Pre-check da migration de séries: SELECT limitado às colunas agrupadas, compatível com ONLY_FULL_GROUP_BY.
- Modal de Exportador: reset apenas dos campos, preservando o contexto de empresa bloqueado.
- CustomersImport: alinhamento de TipoCliente/Status com a validação e o DTO do cadastro.
- LicenciamentosImport: cabeçalho sem adições e obrigatoriedade coerente com colunas não anuláveis, preservando o número gerado pelo servidor.
- Criação de Licenciamento: valores nulos de DTO não anulam defaults existentes de colunas obrigatórias na base.

## Ainda pendente

As 45 falhas/erros da primeira execução foram resolvidas. As fixtures agora autenticam o ator e seleccionam explicitamente a empresa activa, atribuem permissões dentro dessa empresa e usam papéis válidos do RBAC. As fixtures de Empresa criam os seus próprios registos transaccionais, sem depender do ID 1. As permissões negativas e o isolamento entre empresas continuam comprovados pela bateria conjunta.

Os testes de Mercadoria e ASYCUDA preservam o FOB/peso declarado conforme o contrato aprovado, verificando separadamente o total dos itens. O detalhe e o resumo de Processo passaram a consultar os itens de cada agrupamento pelo caminho existente e limitado ao proprietário; foram removidas leituras de uma relação mercadorias inexistente. O detalhe/prontidão de Licenciamento deixou de carregar a relação fatura inexistente: o vínculo, o estado e o processo continuam disponíveis, e o valor sem documento relacionado aparece como não informado. Não foi implementado um vínculo financeiro novo nem alterado o módulo de facturação.

Os testes de logotipo usam configuração fictícia e storage simulado, com paths obtidos do S3PathBuilder actual. O teste de integração usa URL de teste explicitamente configurada e resposta HTTP simulada. Nenhuma Policy, RBAC, tenancy ou configuração global de S3 foi enfraquecida.

Não foi executada a suíte global indiscriminadamente. Os testes de autenticação, billing, pagamentos e integrações externas continuam fora do escopo autorizado original. Testes antigos com RefreshDatabase e escrita em logs reais, como PautaAduaneiraTest e ProtectedLogsRouteTest, precisam de adaptação/revisão antes de execução segura; LegacyRbacConsolidationTest não foi executado. Não houve chamadas externas reais.

Também não foram comprovados migrations desde zero, rollback de DDL, runtime final PHP 8.2, revisão visual em navegador, job real de importação de Exportadores, todas as falhas de infraestrutura/fila, auditoria de pivots, compensação no storage real, aceitação de TXT pelo destino, backup/restauro ou deployment. Os testes concorrentes exercitam processos reais, mas não substituem ensaio prolongado de workers e deadlocks.
