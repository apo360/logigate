const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const output = 'storage/app/pauta-marketplace-qa';
(async () => {
    const browser = await chromium.launch({headless:true, executablePath:process.env.LANDING_BROWSER});
    const context = await browser.newContext(); const page = await context.newPage();
    const errors = []; page.on('pageerror', error => errors.push(error.message));
    // Exact existing stylesheet captured for deterministic QA when CDN TLS fails.
    await context.route('https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css', route => route.fulfill({contentType:'text/css',body:fs.readFileSync(`${output}/tailwind-2.2.19.css`)}));
    const selection = {id:1,codigo:'0203.11.00',descricao:'Mercadoria fictícia para demonstração'};
    const profiles = [{id:1,public_name:'Prestador de demonstração',public_location:'Luanda',operations:18,active_months:7,last_operation:'2026-09-15'}];
    await context.route('**/api/v1/**', async route => {
        const url = new URL(route.request().url());
        assert.equal(route.request().method(), 'GET', 'No real submissions');
        const detail={...selection,impostos:{iva:14,ieq:0},fonte:{designacao:'Catálogo de demonstração',limitacao:'Versão e vigência não identificadas.'}};
        const data = url.pathname.includes('detalhes') || url.pathname.endsWith('0203.11.00') ? {success:true,data:detail} : {success:true,data:[detail],meta:{total:1,current_page:1,last_page:1,per_page:20,catalogue_empty:false}};
        await route.fulfill({contentType:'application/json',body:JSON.stringify(data)});
    });
    let delay = false;
    await context.route('**/mercado/guia?*', async route => {
        const params = new URL(route.request().url()).searchParams;
        if (delay && params.get('codigo') === '0203.11.00') await new Promise(resolve => setTimeout(resolve, 500));
        const code = params.get('codigo');
        await route.fulfill({contentType:'application/json',body:JSON.stringify({selection:{...selection,codigo:code},profiles:code==='0203.11.00'?profiles:[],period:{start:'2025-10-01',end_exclusive:'2026-10-01'},marketplace_url:`http://127.0.0.1:8125/mercado?pauta_id=1&codigo=${code}`})}).catch(()=>{});
    });
    const layouts = [];
    for (const width of [320,360,390,768,1440]) {
        await page.setViewportSize({width,height:1000});
        await page.goto('http://127.0.0.1:8125/mercado?pauta_id=1&codigo=0203.11.00');
        await page.waitForSelector('.mp-js');
        assert.ok(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth), `Marketplace overflow ${width}`);
        assert.equal(await page.getByText('Especialidade declarada',{exact:false}).count(),1);
        await page.evaluate(()=>scrollTo(0,document.body.scrollHeight));
        await page.waitForFunction(()=>[...document.images].every(image=>image.complete && image.naturalWidth));
        await page.evaluate(()=>scrollTo(0,0));
        await page.screenshot({path:`${output}/marketplace-${width}.png`,fullPage:true,animations:'disabled'});
        await page.goto('http://127.0.0.1:8125/consultar-pauta-aduaneira?codigo=0203.11.00&pauta_id=1');
        try { await page.waitForSelector('#pauta-marketplace-guide article'); }
        catch(error) { console.error('Browser diagnostic', errors, await page.locator('#pauta-marketplace-guide').textContent()); throw error; }
        assert.equal(await page.locator('#modalTitle').textContent(),'Código: 0203.11.00');
        assert.ok(await page.locator('#modalContent').textContent().then(t=>t.includes('14%')), 'Existing rate remains visible');
        assert.ok(await page.locator('.modal-content').evaluate(el=>el.scrollWidth<=el.clientWidth),`Detail overflow ${width}`);
        const columns = await page.locator('.pauta-detail-grid').evaluate(el=>getComputedStyle(el).display);
        assert.equal(columns,width>=1000?'grid':'block');
        await page.screenshot({path:`${output}/pauta-${width}.png`,animations:'disabled'});
        await page.keyboard.press('Tab'); await page.keyboard.press('Escape');
        assert.equal(await page.locator('#detailModal').evaluate(el=>el.classList.contains('active')),false);
        layouts.push({width,columns});
    }
    await page.goto('http://127.0.0.1:8125/consultar-pauta-aduaneira');
    assert.equal(await page.locator('#pauta-marketplace-guide').isVisible(),false,'Guide hidden before concrete selection');
    delay=true;
    await page.evaluate(()=>{viewDetails('0203.11.00');setTimeout(()=>viewDetails('9999'),50)});
    await page.waitForTimeout(800);
    assert.equal(await page.locator('#pauta-marketplace-guide article').count(),0,'Stale profiles removed');
    await page.emulateMedia({reducedMotion:'reduce'});
    await page.evaluate(()=>viewDetails('0203.11.00'));
    await page.waitForSelector('#pauta-marketplace-guide article');
    assert.equal(await page.locator('.modal-content').evaluate(el=>getComputedStyle(el).animationName),'none');
    const noJs = await browser.newContext({javaScriptEnabled:false}); const fallback=await noJs.newPage();
    await fallback.goto('http://127.0.0.1:8125/mercado');
    assert.equal(await fallback.locator('#commodity-search').isVisible(),true);
    assert.equal(await fallback.getByText('Prestador de demonstração',{exact:true}).isVisible(),true);
    assert.deepEqual(errors,[]);
    fs.writeFileSync(`${output}/report.json`,JSON.stringify({layouts,errors,checks:['context return','existing rate','guide only after selection','stale responses','keyboard Escape','reduced motion','no-JS marketplace'],fixtures:true},null,2));
    await browser.close(); console.log('Pauta-marketplace browser checks passed; anonymous screenshots and report saved.');
})().catch(error=>{console.error(error);process.exit(1)});
