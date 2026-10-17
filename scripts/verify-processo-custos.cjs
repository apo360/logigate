const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const http = require('node:http');
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright');

(async () => {
    const root = 'storage/app/processo-custos-qa';
    const publicRoot = path.resolve('public');
    const fixtures = Object.fromEntries(['controlo', 'simulador'].map(name => [name, fs.readFileSync(`${root}/${name}.html`, 'utf8')]));
    const livewire = fs.readFileSync('vendor/livewire/livewire/dist/livewire.js');
    let origin;
    const server = http.createServer((request, response) => {
        const pathname = new URL(request.url, origin).pathname;
        if (pathname === '/livewire/livewire.js') {
            response.setHeader('Content-Type', 'application/javascript'); return response.end(livewire);
        }
        if (pathname.startsWith('/build/') || pathname.startsWith('/dist/')) {
            const file = path.resolve(publicRoot, '.' + pathname);
            if (!file.startsWith(publicRoot + path.sep) || !fs.existsSync(file)) { response.writeHead(404); return response.end(); }
            response.setHeader('Content-Type', file.endsWith('.css') ? 'text/css' : file.endsWith('.js') ? 'application/javascript' : 'image/png');
            return response.end(fs.readFileSync(file));
        }
        response.setHeader('Content-Type', 'text/html; charset=utf-8');
        response.end(fixtures[pathname.includes('simulador-pauta') ? 'simulador' : 'controlo'].replaceAll('http://localhost', origin));
    });
    await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
    origin = `http://127.0.0.1:${server.address().port}`;
    const browser = await chromium.launch({ headless: true, executablePath: process.env.LANDING_BROWSER });
    const results = [];
    try {
        for (const width of [320, 390, 768, 1440]) {
            const context = await browser.newContext({ reducedMotion: 'reduce', viewport: { width, height: 960 } });
            const errors = [];
            let release;
            let gate = Promise.resolve();
            await context.route('**/*', async route => {
                const url = new URL(route.request().url());
                if (url.hostname !== '127.0.0.1') return route.fulfill({ contentType: url.pathname.endsWith('.css') ? 'text/css' : 'application/javascript', body: '' });
                if (url.pathname !== '/livewire/update') {
                    assert.equal(route.request().method(), 'GET'); return route.continue();
                }
                const body = route.request().postDataJSON();
                await gate;
                // Browser checks request wiring/loading; PHP feature tests verify real persistence and validation.
                await route.fulfill({ contentType: 'application/json', body: JSON.stringify({ assets: {}, components: body.components.map(component => ({ snapshot: component.snapshot, effects: { returns: component.calls.map(() => null) } })) }) });
            });
            const page = await context.newPage();
            page.on('pageerror', e => errors.push(e.message));
            await page.goto(origin + '/processo/tarifas');
            await page.waitForFunction(() => window.Livewire && window.Alpine);
            assert.equal(await page.getByRole('heading', { name: 'Controlo de Custos e Tributos' }).count(), 1);
            await page.screenshot({ path: `${root}/painel-${width}.png` });
            const form = page.locator('form[wire\\:submit="gravar"]');
            await form.locator('[wire\\:model="registo.valor"]').fill('125.50');
            await form.locator('[wire\\:model="registo.referencia"]').fill('REC-BROWSER');
            await form.locator('[wire\\:model="registo.descricao"]').fill('Conferencia documental no navegador');
            gate = new Promise(resolve => { release = resolve; });
            const sent = page.waitForRequest(req => req.url().endsWith('/livewire/update') && req.postDataJSON().components.some(c => c.calls.some(call => call.method === 'gravar')));
            await form.getByRole('button', { name: 'Gravar registo' }).click();
            const component = (await sent).postDataJSON().components.find(c => c.calls.some(call => call.method === 'gravar'));
            assert.equal(JSON.parse(component.snapshot).memo.name, 'processo.controlo-custos');
            assert.equal(Number(component.updates['registo.valor']), 125.5);
            assert.equal(component.updates['registo.referencia'], 'REC-BROWSER');
            await page.waitForFunction(() => document.querySelector('form[wire\\:submit="gravar"] button[type="submit"]').disabled);
            const received = page.waitForResponse(res => res.url().endsWith('/livewire/update'));
            release(); await received;
            try { await page.waitForFunction(() => !document.querySelector('form[wire\\:submit="gravar"] button[type="submit"]').disabled, null, { timeout: 3000 }); }
            catch (error) {
                console.error(JSON.stringify({ errors, form: await form.evaluate(el => el.outerHTML) }));
                throw error;
            }
            gate = Promise.resolve();
            for (const summary of ['Operações comparáveis da empresa', 'Versões de simulação', 'Critérios de conferência da empresa', 'Histórico de conferência']) {
                const item = page.locator('details').filter({ has: page.locator('summary').filter({ hasText: summary }) }).last();
                await item.evaluate(el => { el.open = true; });
            }
            const fits = await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth + 1);
            if (!fits) {
                console.error(JSON.stringify(await page.evaluate(() => ({ inner: innerWidth, doc: document.documentElement.scrollWidth, body: document.body.scrollWidth, wide: [...document.querySelectorAll('main,section,details,pre,div')].filter(el => el.scrollWidth > el.clientWidth + 1 && getComputedStyle(el).overflowX === 'visible').map(el => ({ tag: el.tagName, cls: el.className, client: el.clientWidth, scroll: el.scrollWidth })) }))));
                console.error(JSON.stringify(await page.evaluate(() => [...document.querySelectorAll('body *')].filter(el => el.getBoundingClientRect().right > innerWidth + 1 && el.getBoundingClientRect().width > 0).slice(0, 100).map(el => ({ tag: el.tagName, cls: el.className, overflow: getComputedStyle(el).overflowX, right: el.getBoundingClientRect().right, width: el.getBoundingClientRect().width })))));
                await page.screenshot({ path: `${root}/overflow-${width}.png` });
            }
            assert.ok(fits, `Root overflow at ${width}`);
            assert.ok(await page.evaluate(() => [...document.querySelectorAll('main')].every(el => el.scrollWidth <= el.clientWidth + 1)), `Main overflow at ${width}`);
            await page.screenshot({ path: `${root}/controlo-${width}.png`, fullPage: true });
            await page.getByRole('link', { name: 'Abrir simulador', exact: true }).click();
            await page.waitForURL('**/simulador-pauta');
            const saved = page.waitForRequest(req => req.url().endsWith('/livewire/update') && req.postDataJSON().components.some(c => c.calls.some(call => call.method === 'guardarVersao')));
            await page.getByRole('button', { name: 'Guardar versão da simulação' }).click(); await saved;
            await page.getByRole('link', { name: 'Conferir custos e tributos' }).click();
            await page.waitForURL('**/processo/tarifas?processo=*');
            assert.deepEqual(errors, []);
            results.push({ width, financialRequest: true, loadingRecovered: true, simulatorNavigationAndSave: true, overflow: false, javascriptErrors: 0 });
            await context.close();
        }
        fs.writeFileSync(`${root}/browser-results.json`, JSON.stringify(results, null, 2));
        console.log(JSON.stringify(results, null, 2));
    } finally { await browser.close(); await new Promise(resolve => server.close(resolve)); }
})().catch(error => { console.error(error); process.exitCode = 1; });
