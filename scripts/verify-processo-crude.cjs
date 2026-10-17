const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const http = require('node:http');
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright');

(async () => {
    const artifactRoot = 'storage/app/processo-crude-qa';
    const fixtures = Object.fromEntries(['hub', 'create', 'edit'].map(name => [name, fs.readFileSync(`${artifactRoot}/${name}.html`, 'utf8')]));
    const publicRoot = path.resolve('public');
    const livewire = fs.readFileSync('vendor/livewire/livewire/dist/livewire.js');
    let origin;
    const server = http.createServer((request, response) => {
        const pathname = new URL(request.url, origin).pathname;
        if (pathname === '/livewire/livewire.js') {
            response.setHeader('Content-Type', 'application/javascript');
            return response.end(livewire);
        }
        if (pathname.startsWith('/build/') || pathname.startsWith('/dist/')) {
            const file = path.resolve(publicRoot, '.' + pathname);
            if (!file.startsWith(publicRoot + path.sep) || !fs.existsSync(file)) {
                response.writeHead(404);
                return response.end();
            }
            response.setHeader('Content-Type', file.endsWith('.css') ? 'text/css' : file.endsWith('.js') ? 'application/javascript' : 'image/png');
            return response.end(fs.readFileSync(file));
        }
        const page = pathname.endsWith('/create') ? 'create' : pathname.endsWith('/edit') ? 'edit' : 'hub';
        response.setHeader('Content-Type', 'text/html; charset=utf-8');
        response.end(fixtures[page].replaceAll('http://localhost', origin));
    });
    await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
    origin = `http://127.0.0.1:${server.address().port}`;
    const browser = await chromium.launch({ headless: true, executablePath: process.env.LANDING_BROWSER });
    const results = [];
    try {
        for (const width of [320, 390, 768, 1440]) {
            const context = await browser.newContext({ reducedMotion: 'reduce', viewport: { width, height: 900 } });
            const errors = [];
            const submissions = [];
            let release;
            let gate = Promise.resolve();
            await context.route('**/*', async route => {
                const url = new URL(route.request().url());
                if (url.hostname !== '127.0.0.1') return route.fulfill({ contentType: url.pathname.endsWith('.css') ? 'text/css' : 'application/javascript', body: '' });
                if (url.pathname !== '/livewire/update') {
                    assert.equal(route.request().method(), 'GET', 'No application mutations outside the simulated Livewire boundary');
                    return route.continue();
                }
                const body = route.request().postDataJSON();
                submissions.push(body);
                await gate;
                await route.fulfill({ contentType: 'application/json', body: JSON.stringify({ assets: {}, components: body.components.map(component => ({ snapshot: component.snapshot, effects: { returns: component.calls.map(() => null) } })) }) });
            });
            const page = await context.newPage();
            page.on('pageerror', error => errors.push(error.message));
            await page.goto(origin + '/processos/exportacao');
            await page.getByRole('link', { name: 'Criar exportação CRUD', exact: true }).click();
            await page.waitForURL('**/processos/exportacao-crude/create');
            await page.waitForFunction(() => window.Livewire && window.Alpine);
            for (const action of ['save', 'update']) {
                if (action === 'update') {
                    await page.goto(origin + '/processos/fixture/edit');
                    await page.locator('#processo-tab-info').click();
                }
                const form = page.locator(action === 'save' ? '#form-processo' : '#form-processo-edit');
                await form.locator('#crude-quantidade_barris').fill('100');
                await form.locator('#crude-rsm_num').fill('RSM-BROWSER');
                await form.locator('#crude-guia_exportacao').fill('GUIA-BROWSER');
                gate = new Promise(resolve => { release = resolve; });
                const sent = page.waitForRequest(request => request.url().endsWith('/livewire/update') && request.postDataJSON().components.some(component => component.calls.some(call => call.method === action)));
                await form.locator('button[type="submit"]').first().click();
                const request = await sent;
                const component = request.postDataJSON().components.find(component => component.calls.some(call => call.method === action));
                assert.equal(JSON.parse(component.snapshot).memo.name, action === 'save' ? 'processo.exportacao-crude-create' : 'processo.exportacao-crude-edit');
                assert.equal(component.updates.rsm_num, 'RSM-BROWSER');
                assert.equal(component.updates.guia_exportacao, 'GUIA-BROWSER');
                assert.equal(Number(component.updates.quantidade_barris), 100);
                const selector = action === 'save' ? '#form-processo button[type="submit"]' : '#form-processo-edit button[type="submit"]';
                await page.waitForFunction(selector => document.querySelector(selector).disabled, selector);
                const received = page.waitForResponse(response => response.url().endsWith('/livewire/update'));
                release();
                await received;
                await page.waitForFunction(selector => !document.querySelector(selector).disabled, selector);
                gate = Promise.resolve();
                assert.ok(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth + 1));
                assert.ok(await page.evaluate(() => [...document.querySelectorAll('main')].every(element => element.scrollWidth <= element.clientWidth + 1)), `Overflow in ${action} at ${width}px`);
                const section = page.locator('#crude-fields-title');
                await section.scrollIntoViewIfNeeded();
                await page.screenshot({ path: `${artifactRoot}/${action}-${width}.png` });
                results.push({ width, action, pendingCrudFieldsSent: true, buttonRecovered: true, overflow: false });
            }
            assert.deepEqual(errors, []);
            await context.close();
        }
        fs.writeFileSync(`${artifactRoot}/browser-results.json`, JSON.stringify(results, null, 2));
        console.log(JSON.stringify({ hubNavigation: true, javascriptErrors: 0, checks: results }, null, 2));
    } finally {
        await browser.close();
        await new Promise(resolve => server.close(resolve));
    }
})().catch(error => { console.error(error); process.exitCode = 1; });
