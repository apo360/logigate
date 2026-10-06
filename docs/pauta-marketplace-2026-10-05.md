# Continuidade Pauta–Marketplace — 5 de Outubro de 2026

Implementação local, sem deploy/push/merge. Mantidas as alterações anteriores da landing e do marketplace. A landing não foi modificada nesta evolução. Stack confirmada: Laravel 11.32, Livewire 3.7.1, PHP 8.5.10, Vite 5.4.21 e Tailwind 3.4.4; a consulta pública da pauta usa Tailwind 2 e AOS via CDN, conforme já existia. Não se acrescentou Livewire, Alpine ou AOS.

## Diagnóstico e evidências

* `app/Models/Processo.php`: empresa proprietária em `empresa_id`, cliente em `customer_id`, mercadorias 1:N por `Fk_Importacao`; soft deletes e isolamento `BelongsToTenant`.
* `app/Models/Licenciamento.php`: proprietário `empresa_id`, mercadorias por `licenciamento_id`. `procLicenMercadorias` referencia uma classe inexistente e usa `processo_id`; não serve de prova de equivalência entre operações.
* `database/migrations/2024_09_25_153928_create_processo_licenciamento_mercadoria_table.php`: ambos os IDs são opcionais; o pivot não comprova uma relação única processo–licenciamento. Nenhuma soma dos dois históricos foi implementada.
* `app/Domains/Licenciamento/Services/EstadoLicenciamentoService.php`: “Licenciado” depende de ficheiro gerado e factura paga. `data_entrada` não é data de conclusão. Esta fonte permanece indisponível para recorrência concluída.
* `app/Application/Processo/Actions/FinalizarProcessoAction.php`: finaliza com `Estado=Finalizado` e `DataFecho=hoje`. `ProcessoLifecycleRules` e `ProcessoObserver` impedem reabertura pelo fluxo actual. Datas históricas importadas não comprovam intervalos comparáveis de despacho.
* `app/Models/Mercadoria.php` e migration de snapshots de 2026-06-05: identidade `pauta_aduaneira_id`, código textual `codigo_pautal_snapshot`, instante `pauta_snapshot_at`. Vínculos antigos só por código não entram na projeção.
* `app/Models/PautaAduaneira.php`: código textual; não há versão/nomenclatura ou parentesco formal. Pontos, comprimento e prefixos não bastam para ampliar uma categoria. Nenhum agrupamento por prefixo é publicado.
* `app/Models/Empresa.php`: tenant interno; `Designacao` distingue “Despachante Oficial”, “Praticante”, “Outro”, mas não autoriza publicação nem demonstra prestação a terceiros. Só os dois primeiros, activos, com adesão expressa como prestador e campos públicos autorizados, são candidatos. Estes indicadores não validam licença profissional.
* `app/Models/Scopes/TenantScope.php`: nega consultas web sem tenant, com excepção preexistente para console. Não foi removido ou alterado. O comando agrega por `empresa_id` explícito usando Eloquent/soft delete; as páginas só consultam tabelas próprias do marketplace.
* View real: `resources/views/WebSite/consultar_pauta.blade.php`. Marketplace: `resources/views/WebSite/marketplace.blade.php`. A fonte de pesquisa continua a ser `PautaSearchService` e seu repositório existente.

## Implementado

Pesquisa GET por descrição ou código usando pauta real; escolha explícita de identidade; filtros de localização pública exacta, meses completos e recorrência; paginação; resumo da mercadoria; limpar selecção; retorno contextual. Resultados e conteúdo essencial do marketplace são renderizados no servidor e permanecem disponíveis sem JS.

Guia depois da selecção concreta nos detalhes da pauta, até três candidatos, lateral a partir de 1000 px e abaixo no móvel; loading/erro/vazio; referências textuais seguras; cancelamento e sequências contra respostas atrasadas. Resultado da pauta acessível por teclado; modal com Escape, foco inicial e contenção do Tab. Movimento reduzido desactiva a animação do modal. A API existente da pauta recebeu apenas o campo aditivo `id`, sem alteração de taxas, conteúdo ou cálculos.

Metadados próprios em `marketplace_profiles`, `marketplace_specialties`, `marketplace_activity_months`. Perfis novos são ocultos por defeito. Nunca se copia nome/contacto/endereço dos tenants automaticamente. Não foram publicados perfis reais. Revogação, empresa inactiva, consentimento ausente e revisão incompatível são verificadas em cada consulta. API sem cache; snapshots de actividade expiram após 24 horas. Um snapshot expirado significa histórico indisponível, não zero.

## Metodologia e privacidade

Janela inicial: últimos **12 meses de calendário completos**, encerrada no primeiro dia do mês actual, exclusivo. Em 05/10/2026 cobre 01/10/2025–30/09/2026. Meses parciais não são misturados com meses completos. Configuração em `config/marketplace.php`; filtros aceitam 1 até a janela configurada.

Fonte única: processos finalizados, não apagados, com abertura e fecho presentes e não invertidos, dentro da janela por `DataFecho`. Rascunhos ficam em tabelas próprias e não são consultados; abertos/cancelados/outros estados são excluídos. Sem marcador fiável de testes no schema operacional: **a revisão explícita do histórico deve confirmar um conjunto real sem operações de teste**. Se isso não puder ser atestado, não activar `--history-reviewed` e publicar apenas especialidades declaradas. Não se inventa um filtro de teste por nome ou referência.

Mercadoria deve ter FK pautal, snapshot de código exactamente igual ao catálogo e instante de snapshot. É obrigatória referência de revisão da identidade/nomenclatura em `MARKETPLACE_CATALOGUE_REVISION`; não significa que o schema passou a versionar a pauta. O operador deve confirmar compatibilidade de todas as identidades usadas; se não puder, não activar o histórico. Alterar/importar catálogo exige rever esta referência e recalcular. Não se combinam códigos iguais de IDs diferentes.

`COUNT(DISTINCT processos.id)` por perfil, ID pautal e mês; linhas duplicadas contam uma operação nessa mercadoria. Operações com várias mercadorias contam uma vez em cada identidade, sem gerar um total profissional somado. Agregação SQL via `insertUsing`, sem carregar operações em memória. Número de meses é contagem de meses distintos; última actividade é máximo de `DataFecho`. Actualização transaccional por perfil, com bloqueio, substitui somente resumos próprios e é idempotente.

Ordenação: histórico exacto antes de especialidade declarada; meses activos desc., última actividade desc., operações desc., ID público asc. Valores ausentes permanecem null; sem penalização de qualidade ou nota opaca. Sem filtros de categoria/tempo que a consulta ignoraria. Todos os filtros aplicados por ambas as páginas usam o mesmo serviço.

DTO público: `id` do perfil, `public_name`, `public_location`, `operations`, `active_months`, `last_operation`. Sem `empresa_id`, clientes, referências, documentos, valores, regimes privados ou consentimentos. Consentimento/revisão e snapshots são metadados internos. Especialidade declarada vem de adesão e é rotulada separadamente. Históricos antigos/incompletos podem estar ausentes.

## Contratos HTTP

* `GET /mercado?q=arroz`: candidatos da pauta, seleccionados por ID+codigo em links. `q` até 100 caracteres.
* `GET /mercado?pauta_id=123&codigo=0203.11.00&months=12&location=Luanda&recurrence=2&page=1`: ID e código devem corresponder; código é string com dígitos/pontos até 50 caracteres. Só código é aceite quando identifica um registo único. Página inteira positiva até 10000; mudança de filtro reinicia paginação pois formulário não transmite `page`.
* `GET /mercado/guia?...`: mesmos critérios, três resultados/página, limitação de 60 pedidos/minuto, JSON e `Cache-Control: no-store`. Sem identidade, 422. ID/código obsoleto ou ambíguo, 422. Links são gerados pelo servidor, sem destino externo arbitrário.
* Retorno: `/consultar-pauta-aduaneira?pauta_id=123&codigo=0203.11.00&months=12&location=Luanda&recurrence=2`. JS abre o resultado no fluxo existente e consulta o guia; parâmetros são validados no endpoint do guia. Identidade divergente da resposta pautal não apresenta guia. Seleccionar outro resultado actualiza URL e guia; respostas anteriores são ignoradas.
* Limpar leva a `/mercado`. Editar o código no filtro desactiva o ID anterior; sem JS, use a pesquisa para alterar a selecção.

## Configuração local (PowerShell)

A migration própria foi validada em bases descartáveis e aplicada à base local de testes configurada, com `migrate:status` a confirmar `Ran` (batch 3). A ligação foi confirmada pelo ambiente, host loopback, configuração e nome real da base. O script `scripts/migrate-marketplace-local.php` aplica exclusivamente esta migration nessa base e recusa qualquer outra ligação. Nenhuma empresa é publicada ao migrar. Antes de habilitar outro ambiente, verificar host/base locais por meios seguros, sem imprimir `.env` ou credenciais. Com a base local confirmada:

```powershell
php artisan migrate --path=database/migrations/2026_10_05_000001_create_marketplace_tables.php
# Alternativa restrita à base local de testes confirmada neste projecto:
php scripts/migrate-marketplace-local.php
# Referência real de revisão, apenas após confirmar nomenclatura e identidade:
$env:MARKETPLACE_CATALOGUE_REVISION='referencia-da-revisao-local'
# O consentimento deve autorizar os campos passados e, separadamente, agregados.
# Sem --publish o perfil permanece oculto. IDs abaixo são placeholders.
php artisan marketplace:profile 123 --name='Nome público autorizado' --location='Luanda' --provider --consent='referencia-autorizacao' --specialties=456
# Só após revisão do conjunto operacional, sem testes e versões incompatíveis:
php artisan marketplace:profile 123 --name='Nome público autorizado' --location='Luanda' --provider --consent='referencia-autorizacao' --history-reviewed --specialties=456 --publish
php artisan marketplace:refresh 789 # ID do perfil público, não ID da empresa
php artisan marketplace:profile 123 --withdraw
```

Os comandos são tarefas de operador local, não endpoints públicos, não mudam auth/permissões. `--publish` representa autorização explícita; não foi executado para tenants reais. Cada chamada de cadastro substitui os metadados do perfil e lista de especialidades, deixa oculto salvo `--publish` e invalida o resumo. A referência de consentimento não deve conter dados pessoais desnecessários. Guardar o consentimento fora do catálogo segundo o procedimento existente da equipa.

Recalcular diariamente cada perfil aderente que tenha autorização de histórico; não se configurou scheduler em produção. Sem isso, após 24 h só especialidades ou catálogo geral ficam disponíveis. Ao mudar versão de catálogo, elevar a revisão configurada e confirmar novo histórico antes de recalcular. Índices em `processos(empresa_id,Estado,DataFecho)` e `mercadorias(Fk_Importacao,pauta_aduaneira_id)` podem ser avaliados com EXPLAIN/volume; nenhum índice operacional foi aplicado.

## Indisponível por falta de suporte

Tempos, medianas/P25/P75 e ordenação por tempo: abertura/fecho do processo não identificam etapas comparáveis do despacho, marcos de licenciamento e revisão de reaberturas históricas não estão suficientemente suportados. Não se publicam dias ou garantias; mínimo de 10 observações e comparabilidade por regime/local deverão ser implementados quando existirem marcos fiáveis, com exclusões auditáveis e sem remover casos lentos automaticamente.

Ampliação por categoria: falta versão/hierarquia formal. Históricos de licenciamento: falta marco de conclusão e equivalência fiável processo–licença. Avaliações, perfil público detalhado e contactos directos: não existem contratos autorizados; não se inventaram rotas, estrelas ou testemunhos. Links de newsletter/apoio/portal existentes foram preservados.

## Validação

Testes MySQL usam schema mínimo descartável com nomes aleatórios `logigate_testing_v1_*`, criados apenas após confirmar `.env` com APP_ENV=testing, base preparada e host loopback. Não se altera `.env`, nem schemas existentes; transacções revertem fixtures por teste. Emails e HTTP externos bloqueados; discos locais/S3 falsos pelo harness. Bases descartáveis ficam preservadas para inspecção, sem executar DROP. Execute:

```powershell
php tests/run-marketplace-isolated.php
npm.cmd run build
php scripts/pauta-marketplace-preview.php
Invoke-WebRequest -Uri 'https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css' -OutFile 'storage/app/pauta-marketplace-qa/tailwind-2.2.19.css'
php -S 127.0.0.1:8125 -t public scripts/pauta-marketplace-preview.php
# Noutro terminal, com PLAYWRIGHT_MODULE e LANDING_BROWSER disponíveis:
node scripts/verify-pauta-marketplace.cjs
```

Resultados e limitações da revisão final são registados na entrega. Pré-visualização serve apenas HTML e assets; não encaminha pedidos para operações. Browser usa fixtures fictícias e intercepta APIs, sem submeter propostas/contactos. Não equivale a homologação de dados reais ou de volume. Sem componente Livewire nestas duas views, não há ciclo de actualização Livewire acrescentado a testar. Nenhuma integração protegida foi modificada.

Resultados executados: 9 testes / 115 asserções sem falhas; 4 deprecações preexistentes de PDO no PHP 8.5. Build Vite passou; aviso preexistente de Browserslist desactualizado. `git diff --check` e verificações de sintaxe PHP passaram. Browser passou em 320/360/390/768/1440: selecção concreta, regresso com código/ID, taxas existentes, respostas atrasadas, guia lateral/inferior, Escape/foco, movimento reduzido e marketplace sem JS. Regressões de menu, newsletter (somente interceptada), prevenção de envios duplicados, erros de rede e JS bloqueado também passaram. Capturas anónimas inspeccionadas em `storage/app/pauta-marketplace-qa`, relatório `report.json`; regressões em `storage/app/marketplace-qa/browser-results.json`.

Após a migration local, `php tests/run-isolated.php tests/Feature/MarketplacePageTest.php` passou na ligação configurada: 1 teste / 16 asserções, com 33 avisos de depreciação do runtime/framework, sem falhas. Nenhum envio real ocorreu.

Durante QA os CDNs de animação/estilos não carregaram. AOS/partículas passaram a ser opcionais para não bloquear a pesquisa. A captura visual determinística usa cópia exacta de Tailwind 2.2.19 em storage, interceptada apenas no teste; não foi alterada a dependência da página. Fontes e ícones externos podem usar fallback; o botão de fechar tem símbolo textual visível. Capturas desactivam animações para não fotografar o primeiro frame transparente.

Não verificado: histórico de tenants reais, volume real/EXPLAIN, migração noutros ambientes, scheduler em produção, comparação estatística de duração (indisponível), publicação/contactos reais e integrações externas (fora do âmbito).

Ficheiros desta evolução: serviço `app/Application/Marketplace/MarketplaceExperience.php`; modelo `MarketplaceProfile`; dois comandos `MarketplaceProfileCommand`/`MarketplaceRefreshCommand`; `MarketplaceController`; configuração/migration próprias; `routes/web.php`; campo ID na API pautal; duas views e partial `WebSite/partials/marketplace-directory.blade.php`; `marketplace.css/js`, `pauta-marketplace.css/js`, Vite; testes `MarketplaceExperienceTest`, actualização de `MarketplacePageTest`, runner descartável; scripts de migration/preview/QA e este documento. Alterações anteriores de `welcome`, `WelcomeController`, logos e motion permanecem preservadas.
