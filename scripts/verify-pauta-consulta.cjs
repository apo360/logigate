const {chromium}=require(process.env.PLAYWRIGHT_MODULE||'playwright');
const assert=require('node:assert/strict');
const fs=require('node:fs');
const output='storage/app/pauta-consulta-qa';
const origin='http://127.0.0.1:8126';
const fixtures=JSON.parse(fs.readFileSync(`${output}/fixtures.json`,'utf8'));
(async()=>{
 const browser=await chromium.launch({headless:true,executablePath:process.env.LANDING_BROWSER});
 const context=await browser.newContext();const page=await context.newPage();const errors=[],badAssets=[],queries=[];
 page.on('pageerror',error=>errors.push(error.message));page.on('response',res=>{if(res.url().includes('/build/')&&res.status()>=400)badAssets.push(res.url());});
 await context.route('**/*',route=>route.request().url().startsWith(origin)?route.continue():route.abort());
 await context.route('https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css',route=>route.fulfill({contentType:'text/css',body:fs.readFileSync('storage/app/pauta-marketplace-qa/tailwind-2.2.19.css')}));
 let delayGuide=false,delaySearch=false;
 const profiles=[{id:1,public_name:'Prestador de demonstração',public_location:'Luanda',operations:18,active_months:7,last_operation:'2026-09-15'},{id:2,public_name:'Especialista de demonstração',public_location:null,operations:null,active_months:null,last_operation:null}];
 await context.route('**/mercado/guia?*',async route=>{
  const params=new URL(route.request().url()).searchParams;const id=Number(params.get('pauta_id'));const item=fixtures.find(item=>item.id===id);
  if(delayGuide&&id===1)await new Promise(resolve=>setTimeout(resolve,500));
  const target=new URL('/mercado',origin);params.forEach((value,key)=>target.searchParams.set(key,value));
  await route.fulfill({contentType:'application/json',body:JSON.stringify({selection:item,profiles:id===1?profiles:[],period:{start:'2025-10-01',end_exclusive:'2026-10-01'},marketplace_url:target.href})}).catch(()=>{});
 });
 await context.route('**/api/v1/pauta**',async route=>{
  assert.equal(route.request().method(),'GET','No real submissions');const url=new URL(route.request().url());const params=url.searchParams;
  if(url.pathname.includes('/detalhes/')){
   const id=Number(url.pathname.split('/').at(-1));const item=fixtures.find(item=>item.id===id);
   await route.fulfill({status:item?200:404,contentType:'application/json',body:JSON.stringify({success:!!item,data:item})});return;
  }
  const q=params.get('q')||params.get('termo')||'';let list=fixtures;
  const type=params.get('tipo')||'auto';const isCode=type==='codigo'||type==='auto'&&/^[0-9.]+$/.test(q);
  if(q)list=fixtures.filter(item=>isCode?item.codigo.replaceAll('.','').includes(q.replaceAll('.','')):item.descricao.toLowerCase().includes(q.toLowerCase()));
  if(url.pathname.includes('/sugestoes')){
   await route.fulfill({contentType:'application/json',body:JSON.stringify({success:true,data:list.slice(0,8)})});return;
  }
  queries.push({q,page:params.get('page')||'1',per_page:params.get('per_page')||'20',tipo:type});
  if(q==='erro'){await route.fulfill({status:503,contentType:'application/json',body:JSON.stringify({success:false,message:'internal diagnostic must not be displayed'})});return;}
  if(q==='lento'&&delaySearch){list=fixtures;await new Promise(resolve=>setTimeout(resolve,500));}
  const size=Number(params.get('per_page')||20),current=Number(params.get('page')||1);
  const data={success:true,data:list.slice((current-1)*size,current*size),meta:{total:list.length,shown:Math.min(size,list.length),per_page:size,current_page:current,last_page:Math.max(1,Math.ceil(list.length/size)),catalogue_empty:q==='vazia'}};
  await route.fulfill({contentType:'application/json',body:JSON.stringify(data)}).catch(()=>{});
 });
 const layouts=[];
 for(const width of [320,360,390,768,1440]){
  await page.setViewportSize({width,height:1000});await page.goto(`${origin}/consultar-pauta-aduaneira`);await page.waitForFunction(()=>document.body.dataset.pautaInitialized==='true');
  assert.equal(await page.locator('#results article').count(),20);assert.equal(await page.locator('#pauta-marketplace-guide').isVisible(),false);
  assert.ok(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),`Page overflow ${width}`);
  await page.screenshot({path:`${output}/consulta-${width}.png`,fullPage:true,animations:'disabled'});
  await page.screenshot({path:`${output}/consulta-top-${width}.png`,animations:'disabled'});
  await page.locator('[data-pauta-select]').first().click();await page.waitForSelector('#pauta-marketplace-guide article');
  assert.ok(await page.locator('#modalContent').textContent().then(text=>text.includes('Não indicado na fonte')&&text.includes('14%')));
  assert.equal(await page.locator('#pauta-marketplace-guide article').count(),2);
  assert.ok(await page.locator('.modal-content').evaluate(el=>el.scrollWidth<=el.clientWidth),`Detail overflow ${width}`);
  const layout=await page.locator('.pauta-detail-grid').evaluate(el=>getComputedStyle(el).display);assert.equal(layout,width>=1000?'grid':'block');
  await page.screenshot({path:`${output}/detalhe-${width}.png`,animations:'disabled'});
  if(width<1000){await page.locator('.modal-content').evaluate(container=>{const guide=document.querySelector('#pauta-marketplace-guide');container.scrollTop+=guide.getBoundingClientRect().top-container.getBoundingClientRect().top-20;});await page.screenshot({path:`${output}/guia-${width}.png`,animations:'disabled'});}
  await page.keyboard.press('Escape');assert.equal(await page.locator('#detailModal').evaluate(el=>el.classList.contains('active')),false);layouts.push({width,layout});
 }
 await page.setViewportSize({width:390,height:1000});
 const input=page.locator('#searchInput');await input.fill('Mercadoria');await page.waitForSelector('#suggestions [role=option]');
 await page.keyboard.press('ArrowDown');assert.equal(await input.getAttribute('aria-activedescendant'),'pauta-option-0');
 await page.keyboard.press('Enter');await page.waitForSelector('#pauta-marketplace-guide article');await page.keyboard.press('Escape');
 await input.fill('Mercadoria');await page.getByRole('button',{name:'Pesquisar',exact:true}).click();await page.waitForFunction(()=>document.querySelector('#results .pauta-count')?.textContent.includes('31 resultados'));
 await page.getByRole('link',{name:'Seguinte',exact:true}).click();await page.waitForFunction(()=>document.querySelector('#results article')?.textContent.includes('0203.21.00'));
 assert.equal(new URL(page.url()).searchParams.get('page'),'2');
 await page.locator('#pauta-page-size').selectOption('50');await page.waitForFunction(()=>document.querySelectorAll('#results article').length===31);assert.equal(new URL(page.url()).searchParams.get('page'),'1');
 await input.fill('0203.01');await page.locator('#pauta-search-type').selectOption('codigo');await page.waitForFunction(()=>document.querySelectorAll('#results article').length===1);
 assert.ok(await page.locator('#results').textContent().then(text=>text.includes('0203.01.00')));
 await page.locator('#pauta-clear').click();await page.waitForFunction(()=>document.querySelectorAll('#results article').length===20);assert.equal(await input.inputValue(),'');
 await input.fill('semresultados');await page.getByRole('button',{name:'Pesquisar',exact:true}).click();await page.waitForFunction(()=>document.querySelector('#results').textContent.includes('Não encontrámos mercadorias'));
 assert.equal(await page.locator('#pagination a').count(),0);
 await input.fill('vazia');await page.getByRole('button',{name:'Pesquisar',exact:true}).click();await page.waitForFunction(()=>document.querySelector('#results').textContent.includes('ainda não tem dados'));
 await input.fill('erro');await page.getByRole('button',{name:'Pesquisar',exact:true}).click();await page.waitForFunction(()=>document.querySelector('#results').textContent.includes('Não foi possível'));assert.ok(!(await page.locator('#results').textContent()).includes('internal diagnostic'));
 delaySearch=true;await page.evaluate(()=>{document.querySelector('#searchInput').value='lento';window.searchPauta();setTimeout(()=>{document.querySelector('#searchInput').value='semresultados';window.searchPauta()},40)});
 await page.waitForTimeout(800);assert.ok((await page.locator('#results').textContent()).includes('Não encontrámos mercadorias'));
 delayGuide=true;await page.evaluate(()=>{viewDetails('0203.01.00',1);setTimeout(()=>viewDetails('0203.02.00',2),50)});await page.waitForTimeout(850);
 assert.equal(await page.locator('#pauta-marketplace-guide article').count(),0);assert.equal(await page.locator('#modalTitle').textContent(),'Código: 0203.02.00');
 await page.emulateMedia({reducedMotion:'reduce'});await page.evaluate(()=>viewDetails('0203.01.00',1));await page.waitForSelector('#pauta-marketplace-guide article');assert.equal(await page.locator('.modal-content').evaluate(el=>getComputedStyle(el).animationName),'none');
 const guideLink=new URL(await page.locator('#pauta-marketplace-guide a').getAttribute('href'));assert.equal(guideLink.searchParams.get('codigo'),'0203.01.00');assert.equal(guideLink.searchParams.get('mercadoria_descricao'),'Mercadoria de demonstração 1');
 const fallback=await browser.newContext({javaScriptEnabled:false,reducedMotion:'reduce',viewport:{width:320,height:1000}});const noJs=await fallback.newPage();
 await fallback.route('**/*',route=>route.request().url().startsWith(origin)?route.continue():route.abort());
 await fallback.route('https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css',route=>route.fulfill({contentType:'text/css',body:fs.readFileSync('storage/app/pauta-marketplace-qa/tailwind-2.2.19.css')}));
 await noJs.goto(`${origin}/consultar-pauta-aduaneira`);assert.equal(await noJs.locator('#results article').count(),20);assert.equal(await noJs.locator('#pauta-search-form').getAttribute('method'),'GET');
 await noJs.locator('[data-pauta-select]').first().click();assert.equal(await noJs.locator('#detailModal').isVisible(),true);assert.equal(await noJs.locator('#pauta-marketplace-guide article').count(),2);await noJs.locator('[data-pauta-close]').click();assert.equal(await noJs.locator('#detailModal').isVisible(),false);
 assert.deepEqual(errors,[]);assert.deepEqual(badAssets,[]);
 fs.writeFileSync(`${output}/report.json`,JSON.stringify({layouts,errors,badAssets,queries,checks:['true counts','description/code','keyboard suggestions','selection','pagination reset','clear','missing vs zero','empty/error','stale search and guide','context description','no JS','reduced motion','blocked animation CDN'],fixtures:true},null,2));
 await browser.close();console.log('Pauta consultation browser checks passed; screenshots and report saved.');
})().catch(error=>{console.error(error);process.exit(1)});
