const fs = require('fs');
const assert = require('node:assert/strict');
const { chromium } = require(process.env.PLAYWRIGHT_MODULE);
(async () => {
 const browser = await chromium.launch({headless:true, executablePath:process.env.LANDING_BROWSER});
 const context = await browser.newContext({reducedMotion:'reduce'});
 await context.route('**/*', route => {
  const url = new URL(route.request().url());
  if (url.hostname !== '127.0.0.1') return route.abort();
  if (url.pathname.includes('/api/') || url.pathname.includes('/mercado/guia')) return route.fulfill({contentType:'application/json',body:JSON.stringify({success:true,data:[]})});
  if (route.request().method() !== 'GET') throw Error('Unexpected submission');
  return route.continue();
 });
 const page = await context.newPage(); const errors=[];page.on('pageerror',error=>errors.push(error.message));
 let expectedLinks;const layouts=[];
 for(const [name,path,active] of [['home','/','Início'],['marketplace','/mercado','Marketplace'],['pauta','/consultar-pauta-aduaneira','Consultar Tarifas']]) {
  for(const width of [320,390,768,1440]) {
   await page.setViewportSize({width,height:1000});await page.goto('http://127.0.0.1:8127'+path);
   await page.waitForSelector('[data-website-header][data-enhanced]');
   const links=await page.locator('#website-navigation a').evaluateAll(items=>items.map(a=>({label:a.textContent.trim(),href:a.getAttribute('href')})));
   if(expectedLinks)assert.deepEqual(links,expectedLinks);else expectedLinks=links;
   assert.equal(await page.locator('#website-navigation [aria-current=page]').textContent().then(x=>x.trim()),active);
   assert.equal(await page.locator('header.website-header').count(),1);assert.equal(await page.locator('footer.website-footer').count(),1);
   assert.ok(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),`${name} overflow ${width}`);
   if(width<1280){const button=page.locator('.website-menu-toggle');await button.click();assert.equal(await button.getAttribute('aria-expanded'),'true');await page.keyboard.press('Escape');assert.equal(await button.getAttribute('aria-expanded'),'false');assert.equal(await button.evaluate(el=>el===document.activeElement),true);await button.click();}
   await page.screenshot({path:`storage/app/website-shell-qa/${name}-menu-${width}.png`});
   if(width<1280)await page.keyboard.press('Escape');
   await page.locator('footer.website-footer').scrollIntoViewIfNeeded();await page.screenshot({path:`storage/app/website-shell-qa/${name}-footer-${width}.png`});
   layouts.push({name,width});
  }
 }
 const withoutJS=await browser.newContext({javaScriptEnabled:false});const staticPage=await withoutJS.newPage();await staticPage.setViewportSize({width:320,height:1000});await staticPage.goto('http://127.0.0.1:8127/mercado');assert.ok(await staticPage.getByRole('navigation',{name:'Navegação principal',exact:true}).isVisible());await withoutJS.close();
 fs.writeFileSync('storage/app/website-shell-qa/report.json',JSON.stringify({layouts,errors,links:expectedLinks,noJS:true},null,2));
 assert.deepEqual(errors, []);
 await browser.close();console.log('Shared navigation/footer checks passed.');
})().catch(error=>{console.error(error);process.exit(1)});
