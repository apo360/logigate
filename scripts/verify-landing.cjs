const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const fs = require('node:fs');
const path = require('node:path');
const assert = require('node:assert/strict');

(async () => {
    const output = path.resolve(process.env.LANDING_QA_DIR || 'storage/app/landing-qa');
    fs.mkdirSync(output, { recursive: true });
    const browser = await chromium.launch({ headless: true, ...(process.env.LANDING_BROWSER ? { executablePath: process.env.LANDING_BROWSER } : {}) });
    const context = await browser.newContext({ reducedMotion: 'reduce' });
    const page = await context.newPage();
    const errors = [], failures = [], checks = [], badAssets = [];
    page.on('pageerror', error => errors.push(error.message));
    page.on('response', response => {
        if (/\/(build|dist)\//.test(response.url()) && response.status() >= 400) badAssets.push(response.url());
    });
    page.on('requestfailed', request => failures.push({ url: request.url(), reason: request.failure()?.errorText }));
    await context.route('**/*', async route => {
        if (!route.request().url().startsWith('http://127.0.0.1:8123/')) return route.abort();
        return route.continue();
    });
    for (const width of [320, 360, 390, 768, 1440]) {
        await page.setViewportSize({ width, height: 1000 });
        await page.goto('http://127.0.0.1:8123/');
        await page.waitForFunction(() => document.documentElement.classList.contains('js'));
        await page.evaluate(() => window.scrollTo({ top: document.body.scrollHeight, behavior: 'instant' }));
        await page.waitForFunction(() => [...document.images].every(img => img.complete && img.naturalWidth > 0));
        await page.evaluate(() => window.scrollTo({ top: 0, behavior: 'instant' }));
        const layout = await page.evaluate(() => ({
            viewport: innerWidth, content: document.documentElement.scrollWidth,
            broken: [...document.images].filter(img => !img.complete || img.naturalWidth === 0).map(img => img.src),
            image: document.querySelector('.hero-art img').currentSrc,
        }));
        assert.ok(layout.content <= width, `Overflow at ${width}: ${layout.content}`);
        assert.deepEqual(layout.broken, []);
        assert.match(layout.image, width < 900 ? /port-mobile/ : /port-desktop/);
        await page.screenshot({ path: path.join(output, `landing-${width}.png`), fullPage: true });
        if (width === 390 || width === 1440) await page.screenshot({ path: path.join(output, `hero-${width}.png`) });
        checks.push({ width, ...layout });
    }
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto('http://127.0.0.1:8123/');
    const menuButton = page.locator('.menu-toggle');
    await menuButton.click();
    assert.equal(await menuButton.getAttribute('aria-expanded'), 'true');
    assert.equal(await page.evaluate(() => document.activeElement.textContent.trim()), 'Plataforma');
    await page.keyboard.press('Escape');
    assert.equal(await menuButton.getAttribute('aria-expanded'), 'false');
    assert.equal(await page.evaluate(() => document.activeElement.className), 'menu-toggle');
    await menuButton.click();
    await page.locator('#mobile-menu a[href="#plataforma"]').click();
    assert.equal(await menuButton.getAttribute('aria-expanded'), 'false');
    await page.getByRole('tab', { name: 'Licenciamentos', exact: true }).click();
    assert.equal(await page.locator('#demo-licenciamentos').isVisible(), true);
    assert.equal(await page.locator('#demo-processos').isVisible(), false);
    await page.keyboard.press('ArrowRight');
    assert.equal(await page.locator('#tab-clientes').getAttribute('aria-selected'), 'true');
    await page.keyboard.press('Home');
    assert.equal(await page.locator('#tab-processos').getAttribute('aria-selected'), 'true');
    await page.getByText('Semestral', { exact: true }).click();
    assert.equal(await page.locator('.plan').nth(1).locator('.billing-cycle').inputValue(), 'semestral');
    assert.match(await page.locator('.plan').nth(1).locator('.plan-price').textContent(), /75[\s.,]?003/);
    assert.equal(await page.locator('.plan').nth(2).locator('button[type=submit]').isDisabled(), true);
    let destination;
    await page.route('**/register?**', async route => {
        destination = new URL(route.request().url());
        await route.fulfill({ status: 200, contentType: 'text/html', body: 'Intercepted registration navigation; no registration performed.' });
    });
    await page.locator('.plan').nth(1).locator('button[type=submit]').click();
    await page.waitForURL('**/register?**');
    assert.equal(destination.searchParams.get('plano'), '902');
    assert.equal(destination.searchParams.get('modalidade'), 'semestral');
    await page.goto('http://127.0.0.1:8123/');
    await page.getByText('Anual', { exact: true }).click();
    assert.equal(await page.locator('.plan').nth(1).locator('.billing-cycle').inputValue(), 'annual');
    await page.locator('.faq-list summary').first().click();
    assert.equal(await page.locator('.faq-list details').first().getAttribute('open'), '');
    const form = page.locator('#contact-form');
    await form.locator('[name=nome]').fill('Pessoa de teste');
    await form.locator('[name=email]').fill('teste@example.test');
    await form.locator('[name=telefone]').fill('900000000');
    await form.locator('[name=assunto]').selectOption('demonstracao');
    await form.locator('[name=mensagem]').fill('Envio interceptado para validação local.');
    await page.route('**/api/v1/contact/send', route => route.fulfill({ status: 422, contentType: 'application/json', body: JSON.stringify({ success: false, errors: { email: ['Email de teste rejeitado.'] } }) }));
    await form.getByRole('button', { name: 'Enviar pedido' }).click();
    await page.waitForFunction(() => document.getElementById('contact-feedback').dataset.state === 'error');
    assert.equal(await form.locator('[name=email]').getAttribute('aria-invalid'), 'true');
    assert.equal(await form.locator('[name=nome]').inputValue(), 'Pessoa de teste');
    await page.unroute('**/api/v1/contact/send');
    await page.route('**/api/v1/contact/send', async route => {
        await new Promise(resolve => setTimeout(resolve, 300));
        await route.fulfill({ status: 200, contentType: 'application/json', body: '{"success":true}' });
    });
    await form.getByRole('button', { name: 'Enviar pedido' }).click();
    await page.waitForFunction(() => document.getElementById('contact-form').getAttribute('aria-busy') === 'true');
    await page.waitForFunction(() => document.getElementById('contact-feedback').dataset.state === 'success');
    assert.equal(await form.locator('[name=nome]').inputValue(), '');
    await page.unroute('**/api/v1/contact/send');
    await page.route('**/api/v1/contact/send', route => route.abort());
    await form.locator('[name=nome]').fill('Pessoa de teste');
    await form.locator('[name=email]').fill('teste@example.test');
    await form.locator('[name=telefone]').fill('900000000');
    await form.locator('[name=assunto]').selectOption('demonstracao');
    await form.locator('[name=mensagem]').fill('Falha de rede interceptada.');
    await form.getByRole('button', { name: 'Enviar pedido' }).click();
    await page.waitForFunction(() => document.getElementById('contact-feedback').dataset.state === 'error');
    assert.match(await page.locator('#contact-feedback').textContent(), /confirmar o envio/);
    assert.equal(await form.getByRole('button', { name: 'Enviar pedido' }).isEnabled(), true);
    await page.route('**/api/v1/newsletter/subscribe', route => route.fulfill({ status: 200, contentType: 'application/json', body: '{"success":true}' }));
    await page.locator('#newsletter-email').fill('teste@example.test');
    await page.getByRole('button', { name: 'Subscrever newsletter' }).click();
    await page.waitForFunction(() => document.getElementById('newsletter-feedback').dataset.state === 'success');
    await page.unroute('**/api/v1/newsletter/subscribe');
    await page.route('**/api/v1/newsletter/subscribe', route => route.fulfill({ status: 422, contentType: 'application/json', body: JSON.stringify({ success: false, errors: { email: ['Email de teste já registado.'] } }) }));
    await page.locator('#newsletter-email').fill('teste@example.test');
    await page.getByRole('button', { name: 'Subscrever newsletter' }).click();
    await page.waitForFunction(() => document.getElementById('newsletter-feedback').dataset.state === 'error');
    assert.equal(await page.locator('#newsletter-email').getAttribute('aria-invalid'), 'true');
    const login = await page.locator('#mobile-menu a').filter({ hasText: 'Entrar na aplicação' }).getAttribute('href');
    const portal = await page.locator('#mobile-menu .portal-link').getAttribute('href');
    assert.notEqual(login, portal);
    await page.emulateMedia({ reducedMotion: 'reduce' });
    assert.equal(await page.evaluate(() => getComputedStyle(document.documentElement).scrollBehavior), 'auto');
    await page.goto('http://127.0.0.1:8123/empty');
    assert.equal(await page.getByText('Planos indisponíveis de momento').isVisible(), true);
    const plain = await browser.newContext({ javaScriptEnabled: false, viewport: { width: 320, height: 844 } });
    await plain.route('**/*', route => route.request().url().startsWith('http://127.0.0.1:8123/') ? route.continue() : route.abort());
    const plainPage = await plain.newPage();
    await plainPage.goto('http://127.0.0.1:8123/');
    assert.equal(await plainPage.locator('#mobile-menu').isVisible(), true);
    assert.equal(await plainPage.locator('#demo-processos').isVisible(), true);
    assert.equal(await plainPage.locator('#demo-licenciamentos').isVisible(), true);
    await plainPage.locator('.faq-list summary').first().click();
    assert.equal(await plainPage.locator('.faq-list details').first().getAttribute('open'), '');
    assert.ok(await plainPage.evaluate(() => document.documentElement.scrollWidth <= innerWidth));
    await plain.close();
    if (fs.existsSync(path.join(output, 'live.html'))) {
        await page.setViewportSize({ width: 1440, height: 1000 });
        await page.goto('http://127.0.0.1:8123/live');
        await page.evaluate(() => window.scrollTo({ top: document.body.scrollHeight, behavior: 'instant' }));
        await page.waitForFunction(() => [...document.images].every(img => img.complete && img.naturalWidth > 0));
        assert.ok(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth));
        await page.screenshot({ path: path.join(output, 'landing-local-plans-1440.png'), fullPage: true });
        await page.locator('#planos').screenshot({ path: path.join(output, 'plans-local-1440.png') });
        checks.push({ actualLocalPlans: await page.locator('.plan').count(), width: 1440 });
    }
    assert.deepEqual(errors, []);
    assert.deepEqual(badAssets, []);
    const localFailures = failures.filter(item => item.url.startsWith('http://127.0.0.1:8123/') && item.reason !== 'net::ERR_ABORTED');
    assert.deepEqual(localFailures.map(item => item.url), ['http://127.0.0.1:8123/api/v1/contact/send'], `Unexpected local network failure: ${JSON.stringify(localFailures)}`);
    const blockedExternalRequests = [...new Set(failures.filter(item => !item.url.startsWith('http://127.0.0.1:8123/')).map(item => new URL(item.url).origin))];
    fs.writeFileSync(path.join(output, 'browser-results.json'), JSON.stringify({ checks, pageErrors: errors, badAssets, intentionalNetworkFailure: localFailures, blockedExternalRequests, interactionChecks: 'Menu, focus, Escape, tabs, keyboard, billing, intercepted registration URL, FAQ, contact validation/loading/success/network error, newsletter success/validation, separate logins, reduced motion, empty plans, no JavaScript: passed' }, null, 2));
    await browser.close();
    console.log('Landing browser checks passed; screenshots and report in storage/app/landing-qa.');
})().catch(error => { console.error(error); process.exit(1); });

