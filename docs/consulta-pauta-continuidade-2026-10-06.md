# Consulta da pauta — implementação do desenho aprovado

Base: main 24112ebed211c1761324974e63c1e2dc9ada1192. Referência visual e funcional: protótipo privado aprovado em 06/10/2026.

## Resultado

A view pública de consulta recebe o desenho aprovado, com pesquisa, sugestões, capítulos 01/02/84/85/87/90, resultados, paginação e detalhe ligados às APIs atuais. A seleção explícita de uma mercadoria adapta o guia, permite abrir o simulador existente com o código e leva código/descrição ao marketplace. O regresso recupera pesquisa, página e seleção, confirmando os detalhes na API.

Os assets são locais, sem dependências novas nem etapa de build. Código pautal continua a ser texto, com zeros iniciais. IVA e IEQ são apresentados a partir da resposta real, incluindo 0%; valores ausentes não são convertidos em zero. Unidade, requisitos e observações aparecem apenas quando a fonte fornece esses campos. Texto da API é escapado no resultado e no detalhe.

## Contratos preservados

- Listagem/código: GET /api/v1/pauta, parâmetros codigo e page.
- Descrição: GET /api/v1/pauta/busca, parâmetros q, tipo=descricao e limit=20. Essa resposta não fornece last_page; não é criada paginação fictícia.
- Sugestões: GET /api/v1/pauta/sugestoes?termo=..., respeitando o limite existente de 20 caracteres.
- Estatísticas: GET /api/v1/pauta/estatisticas.
- Detalhe: GET /api/v1/pauta/{codigo}.
- Simulador: route pauta.simulador, com codigo; SimuladorPauta::mount já aceita esse parâmetro. Fórmulas, taxas e autenticação não foram alteradas.
- Rotas públicas: consultar.pauta e marketplace continuam iguais.

## Marketplace: limite conhecido e preservado

A view existente refere /api/v1/marketplace/despachantes, busca e contactar, mas não existem no repositório rotas/controladores públicos para esses endpoints nem uma fonte de perfis públicos com autorização de publicação ou histórico por código.

Não foi criada uma listagem a partir de empresas, clientes, processos ou mercadorias privados. Não há perfis fictícios, métricas, rankings ou avaliações inventadas na aplicação.

O guia informa que o histórico ainda está indisponível. O marketplace recebe o contexto da pauta, confirma a descrição na API e permite voltar à mesma consulta. Sua pesquisa/listagem continuam a usar os parâmetros existentes; codigo_pautal não é apresentado como filtro real de um backend que ainda não existe. Falhas do diretório passam a ter mensagem visível.

Parâmetros novos de navegação, consumidos apenas pelas views:

- Na consulta: termo, pagina, consulta=1 e codigo_pautal.
- No marketplace: codigo_pautal, mercadoria, pauta_termo e pauta_pagina.
- A descrição recebida por URL não é considerada autoritativa: ela é obtida novamente da pauta.
- O retorno usa sempre a rota interna da consulta; não aceita return_to ou URLs fornecidas pelo visitante.
- O formulário de contacto existente pode receber a mercadoria confirmada como texto inicial. O payload e as regras atuais de autenticação são mantidos; nenhum pedido foi enviado nesta implementação.

## Verificação realizada

- node --check nos três assets JavaScript e no script inline do marketplace.
- node tests/Frontend/pauta-consulta.cjs: 15 verificações funcionais aprovadas com respostas HTTP simuladas.
- Verificação de IDs/targets DOM, assets referidos e git diff --check.
- Casos: pesquisa por código/descrição, zero fiscal vs. valor ausente, campos condicionais, sugestões, paginação real, seleção, retorno, parâmetros forjados, respostas antigas, erros, resultados vazios, limpar e menu móvel.

Os testes novos não usam banco nem executam migrations/seeders. Não foi executado PautaAduaneiraTest: utiliza RefreshDatabase.

Este ambiente não dispõe de PHP/vendor Laravel. A QA visual no navegador não foi realizada. A compilação Blade em Laravel, os endpoints reais em staging e a inspeção visual nos tamanhos 360/390/768/1440 ainda precisam de validação antes da aprovação do PR para merge.

## Âmbito e aplicação

Alterações limitadas a duas views públicas, três assets JS, um CSS local, testes frontend e este documento. Sem alterações em routes, controllers, models, migrations, faturação, subscrições/pagamentos, autenticação, AppyPay, Hongayetu, SAF-T AO, Processo, Licenciamento ou S3.

Entrega em branch/PR de revisão, sem merge ou deploy. Não é necessário migrate para esta alteração.

Revisão em staging: abrir /consultar-pauta-aduaneira, pesquisar 0203, abrir um detalhe, usar a mercadoria, verificar o guia e o link do simulador, seguir para /mercado e regressar. Confirmar os estados de indisponibilidade do diretório até existir uma implementação pública aprovada.
