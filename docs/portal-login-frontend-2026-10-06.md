# Portal do cliente — front-end de acesso

Preparada `resources/views/WebSite/ClienteAppPage/portal_login.blade.php` com a identidade do LogiGate, menu/rodapé partilhados, apresentação em duas colunas no desktop e formulário abaixo da introdução no móvel. A imagem portuária e os ícones são assets locais existentes; não foram acrescentadas bibliotecas ou serviços.

Assets novos: `public/css/website/portal-login.css` e `public/js/website/portal-login.js`. Estilos próprios da página; JS apenas mostra/oculta a palavra-passe. O partial `menu_website.blade.php` indica Portal do cliente como activo nas rotas de acesso/recuperação.

Contrato preservado: POST para `cliente.portal.login.submit`, CSRF, campos `login` e `password`, erros Laravel, old('login'), mensagens de sessão. O controlador existente aceita username/email/phone e já suporta `remember`; a interface oferece essa opção. Nunca repõe a palavra-passe. Labels, autocomplete e mensagens associadas por aria-describedby/aria-invalid; resumo de erro e foco visível. Formulário nativo funciona sem JavaScript.

A recuperação existente usa a mesma view e fornece uma variável `status`, agora apresentada além de session('status'). A informação explica que se deve contactar o despachante; não anuncia redefinição automática, cadastro ou envio de email inexistentes. Nenhum controlador, guard, permissão ou rota foi alterado.

Validação: PHP/JS syntax e `git diff --check` passaram. `node scripts/verify-portal-login.cjs` passou em 320/360/390/768/1440 px: sem overflow, assets locais sem falhas e sem erros JavaScript; teclado, mostrar/ocultar, POST/CSRF, required/autocomplete, estado de erro e retenção de identificador, ausência de palavra-passe reposta, informação da recuperação e fallback sem JS. Capturas anónimas foram inspeccionadas em desktop/móvel/tablet; relatório e imagens em `storage/app/portal-login-qa`.

O QA usa `scripts/portal-login-preview.php` para renderizar fixtures sem base de dados, com emails/HTTP externos bloqueados. Serve apenas HTML/assets, não executa autenticação nem qualquer POST. Não foram submetidas credenciais reais, newsletter ou integrações. Não foi homologado login real ou envio de mensagens. Os assets novos são públicos directos, não requerem compilação Vite.

Comandos Windows:

```powershell
php scripts/portal-login-preview.php
php -S 127.0.0.1:8128 -t public scripts/portal-login-preview.php
# Noutro terminal:
$env:PLAYWRIGHT_MODULE='C:\Users\USER\.cache\codex-runtimes\codex-primary-runtime\dependencies\node\node_modules\playwright'
$env:LANDING_BROWSER='C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe'
node scripts/verify-portal-login.cjs
```

Sem deploy, push ou merge. Alterações locais anteriores preservadas.
