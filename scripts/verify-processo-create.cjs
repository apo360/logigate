const assert = require('node:assert/strict');
const fs = require('node:fs');
const http = require('node:http');
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright');

(async () => {
    const html = fs.readFileSync('storage/app/processo-create-qa/form.html', 'utf8');
    const javascript = fs.readFileSync('vendor/livewire/livewire/dist/livewire.js');
    const server = http.createServer((request, response) => {
        if (request.url === '/livewire.js') {
            response.setHeader('Content-Type', 'application/javascript');
            response.end(javascript);
            return;
        }
        response.setHeader('Content-Type', 'text/html; charset=utf-8');
        response.end(`<!doctype html><html><head><meta name="csrf-token" content="fixture"></head><body>${html}<script src="/livewire.js" data-csrf="fixture" data-update-uri="/livewire/update"></script></body></html>`);
    });
    await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
    const browser = await chromium.launch({ headless: true, executablePath: process.env.LANDING_BROWSER });
    const results = [];
    try {
        for (const scenario of ['click', 'enter-with-decimal']) {
            const context = await browser.newContext();
            const errors = [];
            const submissions = [];
            let release;
            const responseGate = new Promise(resolve => { release = resolve; });
            await context.route('**/*', async route => {
                const url = new URL(route.request().url());
                if (url.hostname !== '127.0.0.1') return route.fulfill({ contentType: 'application/javascript', body: '' });
                if (url.pathname !== '/livewire/update') return route.continue();
                const body = route.request().postDataJSON();
                submissions.push(body);
                await responseGate;
                await route.fulfill({
                    contentType: 'application/json',
                    body: JSON.stringify({ assets: {}, components: body.components.map(component => ({ snapshot: component.snapshot, effects: {} })) }),
                });
            });
            const page = await context.newPage();
            page.on('pageerror', error => errors.push(error.message));
            page.on('console', message => { if (message.type() === 'error') errors.push(message.text()); });
            await page.goto(`http://127.0.0.1:${server.address().port}/`);
            await page.waitForFunction(() => window.Livewire && window.Alpine);
            const reference = page.locator('input[wire\\:model="RefCliente"]');
            const weight = page.locator('input[wire\\:model="peso_bruto"]');
            const button = page.locator('#form-processo button[type="submit"]');
            await reference.fill('REF-BROWSER');
            await weight.fill(scenario === 'click' ? '1.25' : '1.234');
            const requestSent = page.waitForRequest(request => request.url().endsWith('/livewire/update'));
            if (scenario === 'click') await button.click();
            else await reference.press('Enter');
            await requestSent;
            await page.waitForFunction(() => document.querySelector('#form-processo button[type="submit"]').disabled);
            assert.equal(submissions.length, 1);
            const component = submissions[0].components[0];
            assert.deepEqual(component.calls.map(call => call.method), ['save']);
            assert.equal(component.updates.RefCliente, 'REF-BROWSER');
            assert.equal(Number(component.updates.peso_bruto), scenario === 'click' ? 1.25 : 1.234);
            assert.equal(await button.isDisabled(), true);
            assert.equal(await button.innerText(), 'A gravar...');
            const responseReceived = page.waitForResponse(response => response.url().endsWith('/livewire/update'), { timeout: 5000 });
            release();
            await responseReceived;
            await page.waitForFunction(() => !document.querySelector('#form-processo button[type="submit"]').disabled, null, { timeout: 5000 }).catch(async error => {
                console.error('Browser errors:', errors);
                throw error;
            });
            assert.deepEqual(errors, []);
            results.push({ scenario, action: 'save', requests: submissions.length, pendingFieldsSent: true, loadingState: true, javascriptErrors: errors.length });
            await context.close();
        }
        fs.writeFileSync('storage/app/processo-create-qa/browser-results.json', JSON.stringify(results, null, 2));
        console.log(JSON.stringify(results, null, 2));
    } finally {
        await browser.close();
        await new Promise(resolve => server.close(resolve));
    }
})().catch(error => { console.error(error); process.exitCode = 1; });
