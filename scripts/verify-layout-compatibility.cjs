const assert = require('node:assert/strict');
const fs = require('fs');
const { chromium } = require(process.env.PLAYWRIGHT_MODULE);
(async () => {
 const browser=await chromium.launch({headless:true,executablePath:process.env.LANDING_BROWSER});
 const context=await browser.newContext({reducedMotion:'reduce'});
 await context.route('**/*',route=>{
  const url=new URL(route.request().url());
  if(url.href.startsWith('https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/'))return route.fulfill({contentType:'application/javascript',body:fs.readFileSync('storage/app/layout-compatibility-qa/sortable.min.js')});
  if(url.hostname!=='127.0.0.1')return route.abort();
  assert.equal(route.request().method(),'GET','No real Livewire actions or payments');return route.continue();
 });
 const page=await context.newPage();const errors=[];page.on('pageerror',error=>errors.push(error.message));
 await page.goto('http://127.0.0.1:8129/');
 await page.waitForFunction(()=>window.Sortable&&window.initMenuBuilder&&Sortable.get(document.querySelector('#root-list')));
 for(const width of [768,1440]){
  await page.setViewportSize({width,height:900});
  const heights=await page.locator('.review-topbar [aria-label="Estado da subscrição"]').evaluateAll(elements=>elements.map(el=>el.getBoundingClientRect().height));assert.ok(heights.every(height=>height<=64));
  await page.screenshot({path:`storage/app/layout-compatibility-qa/components-${width}.png`,fullPage:true});
 }
 assert.equal(await page.evaluate(()=>!!Sortable.get(document.querySelector('#root-list'))),true);
 assert.equal(await page.evaluate(()=>[...document.querySelectorAll('.node-children')].every(list=>!!Sortable.get(list))),true);
 // Keep the real DOM/Sortable runtime; intercept only the save boundary.
 await page.evaluate(()=>{
  const root=document.querySelector('#root-list').closest('[wire\\:id]');
  window.reviewSaves=[];
  window.initMenuBuilder({$el:root,$call:async(method,nodes)=>window.reviewSaves.push({method,nodes})});
 });
 const first=page.locator('#root-list > li[data-id="1"]').first();
 const last=page.locator('#root-list > li[data-id="3"] > div').first();
 const from=await last.boundingBox();const to=await first.boundingBox();
 await page.mouse.move(from.x+30,from.y+15);await page.mouse.down();await page.mouse.move(from.x+30,from.y-15,{steps:5});await page.waitForTimeout(150);await page.mouse.move(to.x+8,to.y+2,{steps:25});await page.waitForTimeout(200);await page.mouse.up();
 await page.screenshot({path:'storage/app/layout-compatibility-qa/drag-review.png'});
 await page.waitForFunction(()=>document.querySelector('#root-list > li').dataset.id==='3');
 await page.locator('[data-save-menu-order]').click();
 const saves=await page.evaluate(()=>window.reviewSaves);assert.equal(saves.length,1);assert.equal(saves[0].method,'saveOrder');
 assert.equal(saves[0].nodes[0].id,3);assert.deepEqual(saves[0].nodes.find(node=>node.id===2),{id:2,parent_id:1,order:0});
 // Repeated initialization must replace handlers, not duplicate them.
 await page.evaluate(()=>{const root=document.querySelector('#root-list').closest('[wire\\:id]');window.initMenuBuilder({$el:root,$call:async(method,nodes)=>window.reviewSaves.push({method,nodes})});});
 await page.locator('[data-save-menu-order]').click();assert.equal(await page.evaluate(()=>window.reviewSaves.length),2);
 assert.deepEqual(errors,[]);
 fs.writeFileSync('storage/app/layout-compatibility-qa/report.json',JSON.stringify({errors,checks:['Livewire 3 initialization','root and empty nested Sortable lists','real mouse drag','scoped save serialization','no duplicate handlers','widget fits 64px topbar'],fixtures:true,saveIntercepted:true},null,2));
 await browser.close();console.log('Layout compatibility browser checks passed.');
})().catch(error=>{console.error(error);process.exit(1)});
