# Planos e módulos — proposta para decisão

Leitura do catálogo local em 10/10/2026. Não foram alteradas associações, preços, estados ou acessos. A validação de planos incompletos é consultiva: `php artisan billing:review-plans` ou `--json`.

## Planos comercializados actualmente

Todos os quatro planos estão `activo`, aparecem no catálogo público e não estão arquivados. Não há rascunhos ou planos inactivos nesta base. A classificação da ferramenta distingue comercializados (`activo`), rascunhos/inactivos (outros estados) e arquivados (soft delete).

| Plano | Mensal | Trimestral | Semestral | Anual | Utilizadores | Processos | Armazenamento |
|---|---:|---:|---:|---:|---:|---:|---:|
| Gratuito | 0 | 0 | 0 | 0 | 1 | 10 | 1 GB |
| Básico | 15.000 | 42.000 | 78.000 | 150.000 | 5 | 100 | 10 GB |
| Profissional | 30.000 | 84.000 | 156.000 | 300.000 | 15 | 500 | 50 GB |
| Empresarial | 60.000 | 168.000 | 312.000 | 600.000 | 50 | 2.000 | 200 GB |

Preços de catálogo em AOA, antes do acréscimo aplicado no checkout. O código mantém a taxa anteriormente usada, agora configurável por `BILLING_VAT_RATE`; não foi feita revisão fiscal nesta tarefa.

Descrições exactas:

- **Gratuito:** “Plano inicial gratuito para experimentar a plataforma.”
- **Básico:** “Plano ideal para pequenas empresas.”
- **Profissional:** “Plano recomendado para empresas em crescimento.”
- **Empresarial:** “Plano completo para grandes empresas e operações intensivas.”

`plano_items` está vazio: não há uma lista específica de funcionalidades anunciadas por plano na base. A página pública usa nome, descrição e limites. A apresentação geral anuncia processos, licenciamentos, documentos e clientes, mas não estabelece uma matriz diferente para cada plano.

O Gratuito já tem os oito módulos associados. Básico, Profissional e Empresarial não têm associações. A ferramenta assinala `missing_module_mapping` nos três planos pagos, sem bloquear a venda nem corrigir automaticamente.

## Catálogo de módulos existente

| ID | Módulo | Pai | Descrição | Preço no módulo |
|---|---|---|---|---:|
| 1 | Gestão Aduaneira | — | Gestão de processos aduaneiros de importação e exportação. | 500 |
| 2 | Licenciamentos | Gestão Aduaneira | Gestão de licenças de importação e exportação. | 100 |
| 3 | Processos | Gestão Aduaneira | Gestão dos processos de importação e exportação. | 150 |
| 4 | Declarações Aduaneiras | Gestão Aduaneira | Emissão e gestão de declarações aduaneiras. | 180 |
| 5 | Pauta Aduaneira | Gestão Aduaneira | Consulta e cálculos baseados na pauta aduaneira oficial. | 120 |
| 6 | Faturação Aduaneira | — | Gestão de faturação específica para serviços aduaneiros. | 200 |
| 7 | Contabilidade Aduaneira | — | Mapas, lançamentos e controlo contábil aduaneiro. | 250 |
| 8 | Gestão de Clientes | Faturação Aduaneira | Gestão de clientes e contas correntes. | 100 |

O preço de um módulo não é somado automaticamente ao preço do plano. A contratação de extras do antigo assistente foi retirada do percurso de compra; precisa de condições comerciais próprias antes de voltar a ser oferecida.

As relações pai/filho são organizacionais. Associar Gestão Aduaneira não concede automaticamente os quatro filhos. Gestão de Clientes ser filha de Faturação também não significa que a funcionalidade de facturação esteja concedida.

## Proposta recomendada: especialização pela capacidade da equipa

Para preservar a lógica actual — o Gratuito já tem todos os módulos — recomendo disponibilizar **os mesmos oito módulos nos três planos pagos** e diferenciar pela capacidade anunciada:

| Plano | Correspondência proposta | Público / diferenciação |
|---|---|---|
| Básico | IDs 1 a 8 | Despachante ou pequena equipa; 5 utilizadores, 100 processos, 10 GB. |
| Profissional | IDs 1 a 8 | Equipa com operação regular e maior volume; 15 utilizadores, 500 processos, 50 GB. |
| Empresarial | IDs 1 a 8 | Equipa maior e operações intensivas; 50 utilizadores, 2.000 processos, 200 GB. |

Esta proposta evita que um cliente que sai do Gratuito para um plano pago perca funcionalidades. Não implica acrescentar funções que ainda não existem: cada módulo continua sujeito às permissões por papel e aos controlos comerciais já implementados.

## Alternativa: diferenciar também os módulos

Se pretende vender funcionalidades por nível, uma matriz possível é:

- **Básico:** Gestão Aduaneira, Licenciamentos, Processos, Declarações, Pauta e Gestão de Clientes (1, 2, 3, 4, 5, 8).
- **Profissional:** os anteriores mais Faturação Aduaneira (6).
- **Empresarial:** os oito, incluindo Contabilidade Aduaneira (7).

Esta alternativa exige rever a oferta gratuita para novas adesões, porque o Gratuito actual teria mais módulos do que os níveis pagos inferiores. Qualquer revisão do Gratuito deve preservar contratos/acessos existentes e definir a experiência de demonstração para novos clientes. Também exige confirmar a apresentação de Gestão de Clientes sem o direito à facturação: o filho não concede o pai.

## Decisões necessárias antes de aplicar associações

1. Aprovar a proposta recomendada (mesmos módulos, capacidades diferentes) ou a alternativa por funcionalidades.
2. A quota de processos foi definida pelo utilizador como mensal civil, no fuso de Angola, sem acumulação e sem devolução após eliminação. O bloqueio continua desactivado até à revisão das mudanças de plano, importações e início a meio do mês, apresentada em [proposta de quotas](proposta-quota-processos-2026-10-10.md).
3. Confirmar a duração de experimentação do Gratuito e quais os direitos oferecidos a novas adesões. Os acessos actuais não foram reduzidos.
4. Confirmar se os preços de módulos são para extras avulsos ou apenas referências internas; não são cobranças adicionais automáticas.
5. Aprovar textos de funcionalidades por plano para preencher `plano_items` e alinhar página pública e aplicação.

Não é necessário aprovar novamente as correcções técnicas. Estas decisões dizem respeito à oferta comercial e às associações de módulos que pediu expressamente para não aplicar nesta fase.
