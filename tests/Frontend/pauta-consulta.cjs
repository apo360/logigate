'use strict';
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const { createDOM } = require('./dom-harness.cjs');
const root = path.resolve(__dirname, '../..');
const read = file => fs.readFileSync(path.join(root, file), 'utf8');
const coreSource = read('public/js/website/pauta-core.js');
const coreBox = { module: { exports: {} }, URL, URLSearchParams };
vm.runInNewContext(coreSource, coreBox);
const C = coreBox.module.exports;
let checks = 0;
async function check(name, action) { await action(); checks++; process.stdout.write('PASS ' + name + '\n'); }
const first = { codigo: '0203.11.00', descricao: 'Carne de suíno <script>não executar</script>', impostos: { iva: 0, ieq: 5 }, unidade: 'kg', requisitos: 'Requisito fornecido pela API', observacao: 'Observação da fonte', nivel: 3 };
const second = { codigo: '0203.12.00', descricao: 'Outra mercadoria', impostos: { iva: 14, ieq: 0 } };
const rows = { success: true, data: [first, second], meta: { total: 51, per_page: 50, current_page: 1, last_page: 2 } };
async function main() {
    await check('Contratos existentes da pesquisa por código/descrição', () => {
        assert.equal(C.code('0203.11.00'), '0203.11.00'); assert.equal(C.code('02031100'), '02031100');
        assert.equal(C.code('javascript:alert(1)'), ''); assert.equal(C.code('02..03'), '');
        assert.match(C.searchURL('/api/v1', '0203', 2), /\/pauta\?codigo=0203&page=2/);
        assert.match(C.searchURL('/api/v1', 'máquinas', 2), /\/pauta\/busca\?q=m%C3%A1quinas&tipo=descricao&limit=20/);
        assert.equal(C.searchURL('/api/v1', '', 2), '/api/v1/pauta?page=2');
        assert.throws(() => C.searchURL('/api/v1', '1', 1)); assert.throws(() => C.searchURL('/api/v1', 'x', 1));
    });
    await check('Taxas desconhecidas não são transformadas em zero', () => {
        assert.equal(C.item(first).iva, 0); assert.equal(C.item(second).ieq, 0);
        assert.equal(C.item({ codigo: '01', descricao: 'Capítulo', iva: null }).iva, null);
        assert.equal(C.tax(''), null); assert.equal(C.tax(undefined), null); assert.equal(C.tax('invalid'), null);
    });
    await check('Retorno seguro e contexto conservam código, texto e página', () => {
        const url = C.marketplaceURL('/mercado', first, { q: '0203', page: 2, searched: true });
        const s = C.marketContext(new URL(url, 'https://logigate.test').search);
        assert.equal(s.codigo, first.codigo); assert.equal(s.q, '0203'); assert.equal(s.page, 2);
        assert.equal(C.readState('?pagina=-1&codigo_pautal=bad').selected, '');
        assert.match(C.consultationURL('/consultar-pauta-aduaneira', { q: '0203', page: 2, searched: true, selected: first.codigo }), /codigo_pautal=0203.11.00/);
        assert.equal(C.consultationURL('/consultar-pauta-aduaneira', { q: '', searched: false }, 'bad'), '/consultar-pauta-aduaneira');
    });
    const h = createDOM(read('resources/views/WebSite/consultar_pauta.blade.php'), C);
    Object.assign(h.document.body.dataset, { pautaApiBase: '/api/v1', consultaUrl: '/consultar-pauta-aduaneira', marketplaceUrl: '/mercado', simuladorUrl: '/pauta-aduaneira/simulador' });
    h.run(read('public/js/website/pauta-consulta.js'));
    await check('Estado inicial e estatísticas vêm da API real', async () => {
        assert.match(h.get('guide').innerHTML, /À espera da sua seleção/);
        assert.match(h.get('results').innerHTML, /Comece por uma consulta/);
        await h.respond('/estatisticas', { success: true, data: { total_codigos: 8418, total_capitulos: 97, total_posicoes: 1000, total_subposicoes: 6000 } });
        assert.match(h.get('statistics').textContent, /97 capítulos/);
        assert.equal(h.requests.length, 1);
    });
    async function search(term, payload = rows) {
        h.get('searchInput').value = term; h.get('search-form').fire('submit');
        await h.respond(r => r.url.includes('/pauta?') || r.url.includes('/pauta/busca?'), payload);
    }
    async function select(codigo, payload = first) {
        await h.click(h.button('results', 'detail', codigo));
        await h.respond('/pauta/' + codigo, { success: true, data: payload });
        await h.click(h.button('modalContent', 'select', ''));
    }
    await check('Pesquisa e detalhe preservam taxas e campos condicionais', async () => {
        await search('0203');
        assert.equal(h.get('results-count').textContent, '51 resultados');
        assert.match(h.get('results').innerHTML, /IVA 0%/); assert.match(h.get('results').innerHTML, /&lt;script&gt;/);
        assert.doesNotMatch(h.get('guide').innerHTML, /MERCADORIA SELECIONADA/);
        await h.click(h.button('results', 'detail', first.codigo));
        await h.respond('/pauta/' + first.codigo, { success: true, data: first });
        assert.equal(h.get('detailModal').open, true);
        assert.match(h.get('modalContent').innerHTML, /0%/); assert.match(h.get('modalContent').innerHTML, /5%/);
        assert.match(h.get('modalContent').innerHTML, /Requisito fornecido pela API/);
        assert.doesNotMatch(h.get('modalContent').innerHTML, /<script>/);
        await h.click(h.button('modalContent', 'select', ''));
        assert.match(h.get('guide').innerHTML, /MERCADORIA SELECIONADA/);
        assert.match(h.get('guide').innerHTML, /codigo_pautal=0203.11.00/);
        assert.equal(h.get('detailModal').open, false);
        assert.match(h.get('guide').innerHTML, /Histórico ainda indisponível/);
        assert.doesNotMatch(h.get('guide').innerHTML, /Despachante de exemplo/);
    });
    await check('Nova pesquisa e paginação não mantêm seleções antigas', async () => {
        await h.click(h.button('pagination', 'page', '2'));
        assert.match(h.get('guide').innerHTML, /À espera da sua seleção/);
        await h.respond('page=2', { ...rows, data: [second], meta: { ...rows.meta, current_page: 2 } });
        assert.match(h.location.search, /pagina=2/);
        await select(second.codigo, second);
        assert.match(h.location.search, /codigo_pautal=0203.12.00/);
    });
    await check('Autocomplete mantém código como texto e consulta ao escolher', async () => {
        h.get('searchInput').value = '0203'; h.get('searchInput').fire('input'); await h.tick(300);
        await h.respond('/sugestoes', { success: true, data: [{ codigo: first.codigo, descricao: first.descricao }] });
        assert.equal(h.get('suggestions').hidden, false);
        await h.click(h.button('suggestions', 'search', first.codigo));
        await h.respond('codigo=0203.11.00', { success: true, data: [first], meta: { total: 1, current_page: 1, last_page: 1 } });
        assert.equal(h.get('searchInput').value, first.codigo);
        assert.equal(h.get('suggestions').hidden, true);
    });
    await check('Descrição sem metadados de paginação não inventa páginas ou totais', async () => {
        await search('carne', { success: true, data: [first], meta: { total: 1, termo: 'carne' } });
        assert.equal(h.get('results-count').textContent, '1 resultados apresentados');
        assert.equal(h.get('pagination').innerHTML, '');
    });
    await check('Resultados vazios e erros de ligação têm estados distintos', async () => {
        await search('inexistente', { success: true, data: [], meta: { total: 0 } });
        assert.match(h.get('results').innerHTML, /Nenhum resultado encontrado/);
        h.get('searchInput').value = '0203'; h.get('search-form').fire('submit');
        await h.respond('codigo=0203', {}, 500);
        assert.match(h.get('results').innerHTML, /Não foi possível obter os resultados/);
        assert.equal(h.get('results').getAttribute('aria-busy'), 'false');
    });
    await check('Respostas antigas não substituem pesquisas mais recentes', async () => {
        h.get('searchInput').value = '02'; h.get('search-form').fire('submit');
        h.get('searchInput').value = '87'; h.get('search-form').fire('submit');
        await h.respond('codigo=87', { success: true, data: [{ codigo: '8703.23.00', descricao: 'Veículos', iva: 14 }] });
        await h.respond('codigo=02', rows);
        assert.match(h.get('results').innerHTML, /8703.23.00/);
        assert.doesNotMatch(h.get('results').innerHTML, /0203.11.00/);
    });
    await check('Capítulos e limpar continuam funcionais; menu é validado no QA partilhado', async () => {
        await h.click(h.button('chapter-filters', 'capitulo', '01'));
        await h.respond('codigo=01', { success: true, data: [{ codigo: '0101.21.00', descricao: 'Animais', iva: 0 }] });
        assert.equal(h.get('searchInput').value, '01');
        await h.click(h.get('clear-search'));
        assert.equal(h.get('searchInput').value, ''); assert.match(h.get('results').innerHTML, /Comece por uma consulta/);
    });
    await check('Regresso do marketplace recupera pesquisa, página e seleção verificadas', async () => {
        const restored = createDOM(read('resources/views/WebSite/consultar_pauta.blade.php'), C);
        Object.assign(restored.document.body.dataset, h.document.body.dataset);
        restored.location.search = '?termo=0203&pagina=2&consulta=1&codigo_pautal=0203.12.00';
        restored.run(read('public/js/website/pauta-consulta.js'));
        await restored.respond('codigo=0203&page=2', { ...rows, data: [second], meta: { ...rows.meta, current_page: 2 } });
        await restored.respond('/pauta/0203.12.00', { success: true, data: second });
        assert.equal(restored.get('searchInput').value, '0203');
        assert.match(restored.get('guide').innerHTML, /0203.12.00/); assert.match(restored.location.search, /pagina=2/);
    });
    await check('Marketplace verifica a descrição na pauta, ignorando texto forjado na URL', async () => {
        const market = createDOM(read('resources/views/WebSite/marketplace.blade.php'), C);
        market.location.search = '?codigo_pautal=0203.11.00&mercadoria=FORJADA&pauta_termo=0203&pauta_pagina=2';
        Object.assign(market.get('pautaMarketplaceContext').dataset, { consultaUrl: '/consultar-pauta-aduaneira', pautaApiBase: '/api/v1' });
        market.run(read('public/js/website/pauta-marketplace.js'));
        await market.respond('/pauta/0203.11.00', { success: true, data: first });
        assert.equal(market.get('pautaMarketplaceDescricao').textContent, first.descricao);
        assert.match(market.get('pautaMarketplaceVoltar').href, /pagina=2/);
        assert.equal(market.window.logigatePautaMarketplaceContext.codigo, first.codigo);
        assert.equal(market.get('pautaMarketplaceContext').hidden, false);
    });
    await check('Código inválido e detalhe divergente não geram contexto falso', async () => {
        const market = createDOM(read('resources/views/WebSite/marketplace.blade.php'), C);
        Object.assign(market.get('pautaMarketplaceContext').dataset, { consultaUrl: '/consultar-pauta-aduaneira', pautaApiBase: '/api/v1' });
        market.location.search = '?codigo_pautal=javascript:alert(1)'; market.run(read('public/js/website/pauta-marketplace.js'));
        assert.equal(market.requests.length, 0); assert.equal(market.get('pautaMarketplaceContext').hidden, true);
        market.location.search = '?codigo_pautal=0203.11.00'; market.run(read('public/js/website/pauta-marketplace.js'));
        await market.respond('/pauta/0203.11.00', { success: true, data: second });
        assert.equal(market.window.logigatePautaMarketplaceContext, undefined);
        assert.match(market.get('pautaMarketplaceDescricao').textContent, /Não foi possível confirmar/);
    });
    await check('A consulta só lê dados e não altera APIs, taxas ou subscrições', () => {
        assert.doesNotMatch(read('public/js/website/pauta-consulta.js'), /method:\s*['"]POST|\/contactar|AppyPay|Hongayetu/);
        assert.match(read('public/css/website/pauta-consulta.css'), /max-width:800px[\s\S]*?\.workspace\{grid-template-columns:1fr/);
        const view = read('resources/views/WebSite/consultar_pauta.blade.php');
        assert.doesNotMatch(view, /domain\.js|app\.js|Dados de demonstração|Protótipo privado/);
        assert.match(view, /route\('consultar\.pauta'\)/); assert.match(view, /route\('marketplace'\)/);
    });
    process.stdout.write('\n' + checks + ' verificações aprovadas. Sem banco, migrations ou chamadas reais.\n');
}
main().catch(error => { console.error(error); process.exitCode = 1; });
