const fs = require('node:fs');
const path = require('node:path');
const assert = require('node:assert/strict');
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright');

(async () => {
    const browser = await chromium.launch({ headless: true, executablePath: process.env.LANDING_BROWSER });
    const directory = 'storage/app/dashboard-visual'; fs.mkdirSync(directory, { recursive: true });
    const results = []; const errors = [];
    try {
        for (const kind of ['processes', 'licenses']) {
            for (const width of [1440, 390]) {
                const context = await browser.newContext({ viewport: { width, height: 1000 }, reducedMotion: 'reduce' });
                await context.route('**/*', route => {
                    const url = new URL(route.request().url()); const pathname = url.pathname;
                    let file;
                    if (/dashboard-(Processos|Licenciamento)/.test(pathname)) file = `storage/app/dashboard-preview-${kind}${url.searchParams.get('period') === 'custom' ? '-filtered' : ''}.html`;
                    else if (pathname.startsWith('/build/') || pathname.startsWith('/plugins/chart.js/')) file = 'public' + pathname;
                    else if (pathname === '/livewire/livewire.js') file = 'vendor/livewire/livewire/dist/livewire.js';
                    else if (pathname.endsWith('/all.min.css')) file = 'public/plugins/fontawesome-free/css/all.min.css';
                    else if (pathname.includes('/webfonts/')) file = 'public/plugins/fontawesome-free/webfonts/' + path.basename(pathname);
                    if (!file || !fs.existsSync(file)) return route.abort();
                    return route.fulfill({ body: fs.readFileSync(file), contentType: { '.html': 'text/html', '.js': 'application/javascript', '.css': 'text/css', '.woff2': 'font/woff2', '.ttf': 'font/ttf' }[path.extname(file)] || 'application/octet-stream' });
                });
                const page = await context.newPage(); page.on('pageerror', error => errors.push(`${kind}/${width}: ${error.message}`));
                await page.goto(`http://127.0.0.1:8130/dashboard-${kind === 'processes' ? 'Processos' : 'Licenciamento'}`);
                await page.waitForFunction(() => window.Chart && Object.keys(window.Chart.instances).length > 0);
                const charts = await page.locator('[data-operational-chart]').count();
                assert.equal(await page.evaluate(() => Object.keys(Chart.instances).length), charts);
                assert.equal(await page.locator('[data-chart-error]:visible').count(), 0);
                assert.equal(await page.locator('[aria-labelledby="executive-title"] article').count(), 8);
                assert.equal(await page.evaluate(() => document.documentElement.scrollWidth > innerWidth), false, `${kind} overflow at ${width}`);
                await page.screenshot({ path: `${directory}/${kind}-${width}.png` });
                await page.locator('#detail-title').scrollIntoViewIfNeeded();
                await page.screenshot({ path: `${directory}/${kind}-${width}-detail.png` });
                await page.locator('summary').filter({ hasText: 'Ver dados em tabela' }).first().click();
                assert.equal(await page.locator('details[open] table').count(), 1);
                await page.evaluate(() => { document.dispatchEvent(new Event('livewire:navigated')); document.dispatchEvent(new Event('livewire:navigated')); });
                assert.equal(await page.evaluate(() => Object.keys(Chart.instances).length), charts, 'Repeated navigation duplicates charts');
                await page.locator('select[name="period"]').selectOption('custom');
                await page.locator('input[name="start"]').fill('2025-01-01');
                await page.locator('input[name="end"]').fill('2025-02-28');
                await page.getByRole('button', { name: 'Aplicar filtros', exact: true }).click();
                await page.waitForURL('**/*period=custom*');
                await page.waitForFunction(() => window.Chart && Object.keys(Chart.instances).length > 0);
                assert.equal(await page.locator('[aria-labelledby="executive-title"] article').first().locator('p').first().innerText(), '0');
                assert.equal(await page.evaluate(() => Object.keys(Chart.instances).length), await page.locator('[data-operational-chart]').count());
                assert.equal(await page.evaluate(() => document.documentElement.scrollWidth > innerWidth), false);
                results.push({ kind, width, charts, filteredCharts: await page.locator('[data-operational-chart]').count(), filters: 'custom interval, updated zero-count cohort', overflow: false });
                await context.close();
            }
        }
        assert.deepEqual(errors, []);
        fs.writeFileSync(`${directory}/verification.json`, JSON.stringify({ results, errors, scope: 'Actual Blade HTML rendered with authenticated disposable fixtures; asset loading, filtering navigation and chart lifecycle verified without external network.' }, null, 2));
        console.log(JSON.stringify({ results, errors }, null, 2));
    } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exitCode = 1; });
