const fs = require('node:fs');
const path = require('node:path');
const assert = require('node:assert/strict');
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright');

(async () => {
    const browser = await chromium.launch({ headless: true, executablePath: process.env.LANDING_BROWSER });
    try {
        const context = await browser.newContext();
        await context.route('**/*', route => {
            const url = new URL(route.request().url());
            const pathname = url.pathname;
            let file;
            if (/^\/preview-[12]$/.test(pathname)) file = 'storage/app/navigation-rollout' + pathname + '.html';
            else if (pathname === '/livewire/livewire.js') file = 'vendor/livewire/livewire/dist/livewire.js';
            else if (pathname.startsWith('/build/')) file = 'public' + pathname;
            else if (url.hostname === 'cdnjs.cloudflare.com' && pathname.endsWith('/all.min.css')) file = 'public/plugins/fontawesome-free/css/all.min.css';
            else if (pathname.includes('/webfonts/')) file = 'public/plugins/fontawesome-free/webfonts/' + path.basename(pathname);
            if (!file || !fs.existsSync(file)) return route.abort();
            return route.fulfill({ body: fs.readFileSync(file), contentType: { '.html': 'text/html', '.js': 'application/javascript', '.css': 'text/css', '.woff2': 'font/woff2', '.ttf': 'font/ttf' }[path.extname(file)] || 'application/octet-stream' });
        });
        const errors = [];
        const results = [];
        for (const width of [1440, 768, 390]) {
            const page = await context.newPage();
            page.on('pageerror', error => errors.push(error.message));
            await page.setViewportSize({ width, height: 1000 });
            await page.goto('http://127.0.0.1:8130/preview-1');
            await page.waitForFunction(() => window.Alpine);
            const buttons = page.locator('aside button');
            for (let i = 0; i < await buttons.count(); i++) {
                await buttons.nth(i).click();
            }
            await page.waitForTimeout(300);
            const pauta = page.getByRole('link', { name: /Consultar Pauta/ });
            await pauta.click();
            assert.match(await page.locator('#destination').innerText(), /pauta-aduaneira\/consultar/);
            assert.equal(await page.evaluate(() => document.documentElement.scrollWidth > innerWidth), false);
            assert.equal(await page.locator('aside a[href="#"]').count(), 0);
            const groups = await page.locator('aside h2').allTextContents();
            assert.ok(groups.includes('Operação aduaneira'));
            assert.ok(groups.includes('Clientes e gestão'));
            assert.ok(groups.includes('Financeiro'));
            await page.screenshot({ path: `storage/app/navigation-rollout/menu-${width}.png`, fullPage: true });
            results.push({ width, groups, links: await page.locator('aside a:visible').count() });
            await page.close();
        }
        assert.deepEqual(errors, []);
        fs.writeFileSync('storage/app/navigation-rollout/browser-checks.json', JSON.stringify({ results, errors, scope: 'Real Blade preview with effective local access; destinations intercepted' }, null, 2));
        console.log(JSON.stringify({ results, errors }));
    } finally {
        await browser.close();
    }
})().catch(error => { console.error(error); process.exitCode = 1; });
