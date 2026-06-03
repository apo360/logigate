# TODO - Auditoria & Correção DDD (Módulo Processo)

## Step 1: Auditoria completa
- [ ] Mapear todas as referências a `ProcessoService` (legacy vs DDD)
- [ ] Mapear todos os usos de `CriarProcessoAction`, `CriarProcessoDTO` e contrato `toArray/fromArray`

## Step 2: Alinhar dependências/namespace
- [x] Atualizar `App\Http\Controllers\ProcessoController` para usar a camada DDD correta (sem mudar regra de negócio)
- [ ] Garantir que bindings/aliases no container suportam a resolução correta

## Step 3: Corrigir contrato DTO/Action
- [x] Corrigir `CriarProcessoAction` para não reconverter indevidamente via `toArray()/fromArray()`

## Step 4: Validação
- [x] `php -l` nos ficheiros alterados
- [x] `composer dump-autoload`
- [x] `php artisan optimize:clear`
- [x] Rodar testes/smoke check (phpunit/route:list)

