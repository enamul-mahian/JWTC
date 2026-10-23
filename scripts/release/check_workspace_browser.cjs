const {chromium}=require('playwright');
const http=require('node:http'),fs=require('node:fs'),path=require('node:path'),assert=require('node:assert/strict');
const out=process.env.JWTC_UI_FIXTURES,root=path.resolve('public');
const routes={'/job_seeker/view-jobseeker-profile':'profile','/job_seeker/private-documents':'documents','/job_seeker/interviews':'interviews'};
const headers=JSON.parse(fs.readFileSync(path.join(out,'headers.json'),'utf8'));
const server=http.createServer((req,res)=>{
 const name=new URL(req.url,'http://127.0.0.1').pathname;
 if(routes[name]){
  res.setHeader('Content-Type','text/html; charset=utf-8');
  for(const [key,value]of Object.entries(headers[name]||{}))res.setHeader(key,value);
  res.end(fs.readFileSync(path.join(out,routes[name]+'.html')));return;
 }
 const file=path.resolve(root,'.'+decodeURIComponent(name));
 if(!file.startsWith(root+path.sep)||!fs.existsSync(file)||!fs.statSync(file).isFile()){res.writeHead(404);res.end();return;}
 res.setHeader('Content-Type',name.endsWith('.css')?'text/css':name.endsWith('.js')?'text/javascript':'application/octet-stream');
 res.end(fs.readFileSync(file));
});
(async()=>{
 await new Promise(r=>server.listen(8765,'127.0.0.1',r));
 const browser=await chromium.launch({headless:true});
 try{
  for(const width of [1440,390]){
   const context=await browser.newContext({viewport:{width,height:1100},reducedMotion:'reduce'});
   const page=await context.newPage();
   for(const [url,name]of Object.entries(routes)){
    const violations=[];
    page.on('console',m=>{if(/Content Security Policy|violates.*directive|Refused to load/i.test(m.text()))violations.push(m.text());});
    await page.goto('http://127.0.0.1:8765'+url,{waitUntil:'networkidle'});
    assert.deepEqual(violations,[],name+' CSP must permit all workspace assets');
    assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),true,name+' must not overflow');
    assert.equal(await page.locator('.jg-brand').evaluate(x=>getComputedStyle(x).textDecorationLine),'none');
    assert.equal(await page.locator('.jg-profile-nav').evaluate(x=>getComputedStyle(x).backgroundColor),'rgb(13, 37, 73)');
    assert.equal(await page.locator('.jg-account-menu').evaluate(x=>x.open),width>850);
    if(width>850)assert.equal(await page.locator('.jg-profile-nav [aria-current="page"]').count(),1);
    if(width<850){await page.locator('.jg-account-menu > summary').click();assert.equal(await page.locator('.jg-account-menu').evaluate(x=>x.open),true);}
    if(name==='profile'){assert.equal(await page.getByRole('heading',{name:'My Profile',exact:true}).count(),1);assert.equal(await page.locator('.jg-cv-contact').innerText().then(s=>(s.match(/arif@example.com/g)||[]).length),1);}
    await page.screenshot({path:path.join(out,name+'-'+width+'.png'),fullPage:true});
    console.log('BROWSER_PASS',name,width,'CSP, theme, navigation, overflow');
   }
   await context.close();
  }
 }finally{await browser.close();server.close();}
})().catch(e=>{console.error(e);server.close();process.exitCode=1;});
