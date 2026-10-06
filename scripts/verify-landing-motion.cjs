const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const fs = require('node:fs');
const path = require('node:path');
const assert = require('node:assert/strict');

(async () => {
    const output = path.resolve('storage/app/landing-qa/motion');
    const frames = path.join(output, 'frames');
    fs.mkdirSync(frames, { recursive: true });
    const browser = await chromium.launch({ headless: true, args: ['--disable-background-timer-throttling', '--disable-renderer-backgrounding', '--disable-backgrounding-occluded-windows'], ...(process.env.LANDING_BROWSER ? { executablePath: process.env.LANDING_BROWSER } : {}) });
    const results = [];
    async function context(options = {}) {
        const instance = await browser.newContext({ viewport: { width: 390, height: 844 }, ...options });
        await instance.route('**/*', route => route.request().url().startsWith('http://127.0.0.1:8123/') ? route.continue() : route.abort());
        return instance;
    }
    const animated = await context();
    await animated.addInitScript(() => {
        const NativeObserver = window.IntersectionObserver;
        window.__motionObservers = 0;
        window.IntersectionObserver = class extends NativeObserver {
            constructor(...args) { super(...args); window.__motionObservers++; }
        };
        window.__motionFocusListeners = 0;
        const nativeAdd = EventTarget.prototype.addEventListener;
        EventTarget.prototype.addEventListener = function (name, ...args) {
            if (name === 'focusin' && this instanceof HTMLElement && this.matches('body.lg-landing')) window.__motionFocusListeners++;
            return nativeAdd.call(this, name, ...args);
        };
    });
    const page = await animated.newPage();
    const errors = [];
    page.on('pageerror', error => errors.push(error.message));
    await page.goto('http://127.0.0.1:8123/');
    await page.waitForSelector('body[data-lg-motion-ready]');
    assert.equal(await page.locator('.hero-copy').evaluate(el => getComputedStyle(el).opacity), '1');
    assert.equal(await page.locator('.hero-art').evaluate(el => getComputedStyle(el).opacity), '1');
    assert.equal(await page.locator('.hero-actions').evaluate(el => getComputedStyle(el).opacity), '1');
    assert.ok(await page.locator('[data-lg-pending]').count() > 0);

    // Re-enter the real initializer twice, and simulate events not used by this page.
    const initializer = fs.readFileSync('resources/js/landing-motion.js', 'utf8').replace('export function', 'function');
    await page.addScriptTag({ content: `(() => { ${initializer}; initLandingMotion(); initLandingMotion(); })();` });
    await page.evaluate(() => {
        document.dispatchEvent(new Event('livewire:navigated'));
        document.dispatchEvent(new Event('DOMContentLoaded'));
    });
    assert.equal(await page.evaluate(() => window.__motionObservers), 1);
    assert.equal(await page.evaluate(() => window.__motionFocusListeners), 1);
    results.push('Single observer and focus listener after repeated initialization/events.');
    await page.setViewportSize({ width: 390, height: 1000 });
    await page.waitForFunction(() => getComputedStyle(document.querySelector('.steps > li')).opacity === '0');
    fs.writeFileSync(path.join(output, 'motion-before.json'), JSON.stringify(await page.locator('.steps > li').first().evaluate(el => ({pending:el.hasAttribute('data-lg-pending'),opacity:getComputedStyle(el).opacity,transition:getComputedStyle(el).transition,focused:el.matches(':focus-within'),media:matchMedia('(prefers-reduced-motion: reduce)').matches,ready:document.body.hasAttribute('data-lg-motion-ready')})), null, 2));

    // Capture actual in-browser intermediate styles and frames, not a synthetic animation.
    await page.evaluate(() => {
        window.__motionSamples = [];
        const start = performance.now();
        const block = document.querySelector('.steps > li');
        function sample() {
            const style = getComputedStyle(block);
            window.__motionSamples.push({ time: Math.round(performance.now() - start), opacity: Number(style.opacity), transform: style.transform });
        }
        const interval = setInterval(sample, 20);
        setTimeout(() => clearInterval(interval), 1400);
        const section = document.querySelector('.steps-section');
        window.scrollTo({ top: scrollY + section.getBoundingClientRect().top - 100, behavior: 'instant' });
    });
    for (let i = 0; i < 14; i++) {
        await page.screenshot({ path: path.join(frames, `motion-${String(i).padStart(2, '0')}.png`) });
        await page.waitForTimeout(40);
    }
    await page.waitForTimeout(550);
    const samples = await page.evaluate(() => window.__motionSamples);
    fs.writeFileSync(path.join(output, 'motion-samples.json'), JSON.stringify(samples, null, 2));
    assert.ok(samples.some(sample => sample.opacity > 0 && sample.opacity < 1), 'Real intermediate opacity must be observed');
    assert.equal(await page.locator('.steps > li').first().evaluate(el => getComputedStyle(el).opacity), '1');
    assert.equal(await page.locator('.steps > li').first().getAttribute('data-lg-pending'), null);
    await page.evaluate(() => window.scrollTo({ top: 0, behavior: 'instant' }));
    await page.locator('.steps').scrollIntoViewIfNeeded();
    assert.equal(await page.locator('.steps > li').first().evaluate(el => getComputedStyle(el).opacity), '1');
    results.push('Real opacity/transform interpolation observed; reveal does not repeat.');

    // Programmatic focus models keyboard moving to a not-yet-revealed form control.
    assert.equal(await page.locator('#contact-form').getAttribute('data-lg-pending'), '');
    await page.locator('#nome').focus();
    assert.equal(await page.locator('#contact-form').getAttribute('data-lg-pending'), null);
    assert.equal(await page.locator('#contact-form').evaluate(el => getComputedStyle(el).opacity), '1');
    assert.equal(await page.locator('#nome').evaluate(el => getComputedStyle(el).outlineStyle), 'solid');
    results.push('Focused pending content becomes immediately visible with focus outline.');

    await page.emulateMedia({ reducedMotion: 'reduce' });
    await page.waitForFunction(() => !document.querySelector('[data-lg-pending]'));
    assert.equal(await page.evaluate(() => getComputedStyle(document.documentElement).scrollBehavior), 'auto');
    assert.equal(await page.locator('.hero-copy').evaluate(el => getComputedStyle(el).animationName), 'none');
    await page.emulateMedia({ reducedMotion: 'no-preference' });
    assert.equal(await page.locator('[data-lg-pending]').count(), 0);
    results.push('Changing motion preference reveals all content and never restarts completed entrances.');

    // Layout screenshots with all blocks revealed through actual scrolling.
    for (const width of [320, 360, 390, 768, 1440]) {
        await page.setViewportSize({ width, height: 1000 });
        await page.goto('http://127.0.0.1:8123/');
        await page.waitForSelector('body[data-lg-motion-ready]');
        for (const block of await page.locator('[data-lg-pending]').elementHandles()) {
            await block.evaluate(el => window.scrollTo({ top: scrollY + el.getBoundingClientRect().top - 130, behavior: 'instant' }));
            await page.waitForFunction(el => !el.hasAttribute('data-lg-pending'), block);
        }
        await page.waitForTimeout(800);
        await page.waitForFunction(() => [...document.images].every(img => img.complete && img.naturalWidth > 0));
        assert.equal(await page.locator('[data-lg-pending]').count(), 0);
        assert.ok(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth));
        await page.evaluate(() => window.scrollTo({ top: 0, behavior: 'instant' }));
        await page.screenshot({ path: path.join(output, `motion-layout-${width}.png`), fullPage: true });
    }
    assert.deepEqual(errors, []);

    for (const failure of ['missing-observer', 'constructor-error', 'observe-error', 'callback-error', 'broken-aos', 'blocked-script', 'reduced-motion', 'no-js']) {
        const fallback = await context({ reducedMotion: failure === 'reduced-motion' ? 'reduce' : 'no-preference', javaScriptEnabled: failure !== 'no-js' });
        if (failure === 'blocked-script') await fallback.route('**/build/assets/*.js', route => route.abort());
        await fallback.addInitScript(failure => {
            if (failure === 'missing-observer') window.IntersectionObserver = undefined;
            if (failure === 'constructor-error') window.IntersectionObserver = class { constructor() { throw Error('Simulated initialization failure'); } };
            if (failure === 'observe-error') window.IntersectionObserver = class { observe() { throw Error('Simulated observe failure'); } disconnect() {} unobserve() {} };
            if (failure === 'callback-error') {
                const NativeObserver = window.IntersectionObserver;
                window.IntersectionObserver = class extends NativeObserver { unobserve() { throw Error('Simulated callback failure'); } };
            }
            if (failure === 'broken-aos') window.AOS = { init() { throw Error('Simulated AOS failure'); } };
        }, failure);
        const check = await fallback.newPage();
        const unexpected = [];
        check.on('pageerror', error => unexpected.push(error.message));
        await check.goto('http://127.0.0.1:8123/');
        if (failure === 'callback-error') {
            await check.locator('.demo-frame').scrollIntoViewIfNeeded();
            await check.waitForFunction(() => !document.querySelector('[data-lg-pending]'));
        }
        if (failure === 'broken-aos') {
            // AOS is not used; exercise all actual observer reveals.
            for (const block of await check.locator('[data-lg-pending]').elementHandles()) {
                await block.evaluate(el => window.scrollTo({ top: scrollY + el.getBoundingClientRect().top - 130, behavior: 'instant' }));
                await check.waitForFunction(el => !el.hasAttribute('data-lg-pending'), block);
            }
            await check.waitForTimeout(800);
        }
        assert.equal(await check.locator('[data-lg-pending]').count(), 0, failure);
        assert.equal(await check.locator('#contact-form').evaluate(el => getComputedStyle(el).opacity), '1', failure);
        assert.equal(await check.locator('.hero-copy').evaluate(el => getComputedStyle(el).opacity), '1', failure);
        if (!['no-js', 'blocked-script'].includes(failure)) {
            await check.locator('.menu-toggle').click();
            assert.equal(await check.locator('.menu-toggle').getAttribute('aria-expanded'), 'true', failure);
            await check.keyboard.press('Escape');
            await check.locator('#tab-clientes').click();
            assert.equal(await check.locator('#tab-clientes').getAttribute('aria-selected'), 'true', failure);
        }
        assert.deepEqual(unexpected, [], failure);
        results.push(`${failure}: content visible; no unhandled errors.`);
        await fallback.close();
    }
    fs.writeFileSync(path.join(output, 'motion-results.json'), JSON.stringify({ results, samples, pageErrors: errors, sizes: [320, 360, 390, 768, 1440] }, null, 2));
    await browser.close();
    console.log('Motion and fallback checks passed; real frames, screenshots and report saved in storage/app/landing-qa/motion.');
})().catch(error => { console.error(error); process.exit(1); });
