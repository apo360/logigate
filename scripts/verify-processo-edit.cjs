const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const http = require('node:http');
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright');

(async () => {
    const fixture = fs.readFileSync('storage/app/processo-edit-qa/page.html', 'utf8');
    const javascript = fs.readFileSync('vendor/livewire/livewire/dist/livewire.js');
    const publicRoot = path.resolve('public');
    let origin;
    const server = http.createServer((request, response) => {
        const pathname = new URL(request.url, origin).pathname;
        if (pathname === '/livewire/livewire.js') {
            response.setHeader('Content-Type', 'application/javascript');
            return response.end(javascript);
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
        response.setHeader('Content-Type', 'text/html; charset=utf-8');
        response.end(fixture.replaceAll('http://localhost', origin));
    });
    await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
    origin = `http://127.0.0.1:${server.address().port}`;
    const browser = await chromium.launch({ headless: true, executablePath: process.env.LANDING_BROWSER });
    try {
        const context = await browser.newContext({ reducedMotion: 'reduce' });
        const submissions = [];
        let release;
        let responseGate = Promise.resolve();
        await context.route('**/*', async route => {
            const url = new URL(route.request().url());
            if (url.hostname !== '127.0.0.1') return route.fulfill({ contentType: url.pathname.endsWith('.css') ? 'text/css' : 'application/javascript', body: '' });
            if (url.pathname !== '/livewire/update') return route.continue();
            const body = route.request().postDataJSON();
            submissions.push(body);
            await responseGate;
            await route.fulfill({ contentType: 'application/json', body: JSON.stringify({ assets: {}, components: body.components.map(component => {
                const snapshot = JSON.parse(component.snapshot);
                for (const [key, value] of Object.entries(component.updates)) {
                    const parts = key.split('.');
                    if (parts.length === 1) snapshot.data[key] = value;
                    else {
                        let target = snapshot.data[parts.shift()];
                        if (Array.isArray(target) && target[1]?.s) target = target[0];
                        target[parts[0]] = value;
                    }
                }
                return { snapshot: JSON.stringify(snapshot), effects: { returns: component.calls.map(() => null) } };
            }) }) });
        });
        const page = await context.newPage();
        const errors = [];
        page.on('pageerror', error => errors.push(error.message));
        await page.goto(origin + '/preview');
        await page.waitForFunction(() => window.Livewire && window.Alpine);
        await page.locator('#processo-tab-info').click();
        await page.locator('#processo-tab-info').press('ArrowRight');
        assert.equal(await page.locator('#processo-tab-mercadoria').getAttribute('aria-selected'), 'true');
        await page.locator('#processo-tab-mercadoria').press('End');
        assert.equal(await page.locator('#processo-tab-docs').getAttribute('aria-selected'), 'true');
        await page.locator('#processo-tab-docs').press('Home');
        assert.equal(await page.locator('#processo-tab-info').getAttribute('aria-selected'), 'true');
        await page.locator('button[aria-controls="processo-edit-options"]').click();
        await page.locator('#processo-edit-options').waitFor({ state: 'visible' });
        assert.equal(await page.locator('#processo-edit-options').isVisible(), true);
        await page.locator('button[aria-controls="processo-edit-options"]').press('Escape');
        await page.locator('#processo-edit-options').waitFor({ state: 'hidden' });
        assert.equal(await page.locator('#processo-edit-options').isVisible(), false);

        for (const action of ['update', 'save']) {
            const panel = action === 'update' ? 'info' : 'despesas';
            await page.locator(`#processo-tab-${panel}`).click();
            const form = page.locator(action === 'update' ? '#form-processo-edit' : 'form[id^="form-despesas-"]');
            const input = form.locator(action === 'update' ? 'input[wire\\:model="RefCliente"]' : 'input[name="despesa_porto"]');
            await input.fill(action === 'update' ? 'REF-BROWSER-EDIT' : '1.234,56');
            responseGate = new Promise(resolve => { release = resolve; });
            const requestSent = page.waitForRequest(request => request.url().endsWith('/livewire/update') && request.postDataJSON().components.some(component => component.calls.some(call => call.method === action)));
            await form.locator('button[type="submit"]').first().click();
            await requestSent;
            const button = form.locator('button[type="submit"]').first();
            await page.waitForFunction(selector => document.querySelector(selector).disabled, action === 'update' ? '#form-processo-edit button[type="submit"]' : 'form[id^="form-despesas-"] button[type="submit"]');
            assert.equal(await button.isDisabled(), true);
            const submitted = submissions.find(body => body.components.some(component => component.calls.some(call => call.method === action)));
            assert.ok(submitted);
            const pending = submitted.components.find(component => component.calls.some(call => call.method === action));
            assert.equal(pending.updates[action === 'update' ? 'RefCliente' : 'form.porto'], action === 'update' ? 'REF-BROWSER-EDIT' : '1.234,56');
            const responseReceived = page.waitForResponse(response => response.url().endsWith('/livewire/update'));
            release();
            await responseReceived;
            await page.waitForFunction(selector => !document.querySelector(selector).disabled, action === 'update' ? '#form-processo-edit button[type="submit"]' : 'form[id^="form-despesas-"] button[type="submit"]');
            responseGate = Promise.resolve();
        }

        const screens = [];
        for (const width of [320, 390, 768, 1440]) {
            await page.setViewportSize({ width, height: 900 });
            for (const panel of ['info', 'despesas']) {
                await page.locator(`#processo-tab-${panel}`).click();
                await page.locator(`#processo-tab-${panel}`).evaluate(element => new Promise(resolve => setTimeout(resolve, 200)));
                assert.ok(await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth + 1), `Overflow at ${width}px in ${panel}`);
                const overflow = await page.evaluate(() => {
                    const main = document.querySelector('main');
                    if (main.scrollWidth <= main.clientWidth + 1) return null;
                    const boundary = main.getBoundingClientRect();
                    return { width: main.clientWidth, scroll: main.scrollWidth, elements: [...main.querySelectorAll('*')].filter(element => {
                        const rect = element.getBoundingClientRect();
                        return rect.width && rect.right > boundary.right + 1;
                    }).slice(0, 15).map(element => ({ tag: element.tagName, classes: element.className, width: element.getBoundingClientRect().width })) };
                });
                assert.equal(overflow, null, `Main container overflow at ${width}px in ${panel}: ${JSON.stringify(overflow)}`);
                await page.screenshot({ path: `storage/app/processo-edit-qa/${panel}-${width}.png`, fullPage: true });
                screens.push({ panel, width, overflow: false });
            }
        }
        assert.deepEqual(errors, []);
        const result = { actions: ['update', 'save'], keyboardTabs: true, optionsEscape: true, loadingAndRecovery: true, javascriptErrors: errors.length, screens };
        fs.writeFileSync('storage/app/processo-edit-qa/browser-results.json', JSON.stringify(result, null, 2));
        console.log(JSON.stringify(result, null, 2));
        await context.close();
    } finally {
        await browser.close();
        await new Promise(resolve => server.close(resolve));
    }
})().catch(error => { console.error(error); process.exitCode = 1; });
