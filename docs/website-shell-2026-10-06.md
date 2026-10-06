# Menu e rodapé comuns — 6 de Outubro de 2026

As views `welcome.blade.php`, `WebSite/marketplace.blade.php` e `WebSite/consultar_pauta.blade.php` incluem agora `WebSite.partials.menu_website` e `WebSite.partials.footer_website`.

O menu conserva os destinos existentes: Início, Consultar Tarifas, Marketplace, Licenciamento, Portal do cliente, Registar/Acesso para visitantes e Dashboard para utilizadores autenticados. Estado activo por nome de rota, ícones SVG locais equivalentes aos conceitos usados na consulta (sem carregar Font Awesome), navegação móvel com Escape/retorno de foco, fecho ao clicar fora e links acessíveis sem JavaScript. Ícones em `resources/views/WebSite/partials/website-icon.blade.php`.

Rodapé comum com marca, ligações, planos/plataforma, contactos já existentes e newsletter na rota existente. Não se inventaram destinos legais para links sem implementação. Os contactos reais só são substituídos por exemplos nas capturas anónimas. Newsletter mantém envio POST/CSRF e melhora respostas sem duplicar o handler da landing. Nenhum email foi enviado nos testes.

Estilos e comportamento comuns em `public/css/website/website-shell.css` e `public/js/website/website-shell.js`; handlers antigos de menu retirados de `resources/js/landing.js`, `resources/js/marketplace.js` e `public/js/website/pauta-consulta.js`. Conteúdos, pesquisa, regras e módulos operacionais preservados. Alterações anteriores da view da pauta foram mantidas. O ficheiro existente `pauta-core (1).js` foi copiado para o nome `pauta-core.js` efectivamente solicitado pela view, sem modificar a lógica nem remover o original.

## Validação

- `npm.cmd run build`: passou; aviso existente de Browserslist desactualizado.
- `php tests/run-isolated.php tests/Feature/LandingPageTest.php`: 3 testes / 22 asserções passaram; 33 deprecações do ambiente PHP 8.5.
- `node --check` dos scripts públicos revistos: passou.
- `node scripts/verify-website-shell.cjs`: passou com fixtures anónimas nas três páginas em 320/390/768/1440 px; links idênticos, estado activo, menu/Escape/foco, sem overflow, sem erros JavaScript, menu acessível sem JS. Capturas de menu/rodapé em `storage/app/website-shell-qa`; imagens de desktop, móvel e rodapés foram inspeccionadas. O QA bloqueia integrações externas e não submete formulários.
- `node tests/Frontend/pauta-consulta.cjs`: os 12 primeiros cenários passaram; a suite pára no cenário seguinte porque exige `pautaMarketplaceContext`, ausente na view actual do marketplace antes desta tarefa. Os restantes cenários não foram executados. O teste do menu antigo foi retirado deste harness e substituído pelo QA comum acima. Não se alterou o fluxo do marketplace para satisfazer um contrato antigo deste teste.
- `git diff --check`: passou.

Preview sem base de dados: `php scripts/website-shell-preview.php`; servidor `php -S 127.0.0.1:8127 -t public scripts/website-shell-preview.php`. QA usa `PLAYWRIGHT_MODULE` e `LANDING_BROWSER` do runtime local, como nos outros scripts de revisão do projecto. Sem deploy/push/merge; nenhuma alteração de permissões ou publicação de perfis.
