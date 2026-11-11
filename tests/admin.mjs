import assert from 'node:assert/strict';
import { spawn, execFileSync } from 'node:child_process';
import { randomBytes } from 'node:crypto';
import { mkdirSync, writeFileSync } from 'node:fs';
import { once } from 'node:events';

// Each run owns only a randomly named database; no development rows are changed.
const database = `ronan_tech_shop_test_${randomBytes(6).toString('hex')}`;
const env = { ...process.env, TECH_SHOP_TEST_DATABASE: database };
const php = process.env.SHOP_PHP || 'php';
const fixture = (action, ...args) => execFileSync(php, ['tests/checkout-fixture.php', action, ...args.map(String)], { env, encoding: 'utf8', windowsHide: true });
const state = () => JSON.parse(fixture('state'));
const servers = [];
let created = false;
const pause = ms => new Promise(resolve => setTimeout(resolve, ms));
const hidden = (html, name) => html.match(new RegExp(`name="${name}" value="([^"]*)"`))?.[1];
const customer = { name: 'First Demo Buyer', email: 'DEMO.BUYER@example.test', contact: '09170000000', address: '123 Demo Lane, fictional', payment_method: 'cod', notes: '0' };

async function startServer(port) {
    const server = spawn(php, ['-S', `127.0.0.1:${port}`, '-t', 'public'], { env, stdio: ['ignore','ignore','pipe'], windowsHide: true });
    servers.push(server); let errors = '';
    server.stderr.on('data', chunk => { errors = (errors + chunk).slice(-6000); });
    const origin = `http://127.0.0.1:${port}`;
    for (let i=0;i<50;i++) {
        if(server.exitCode!==null) throw new Error(errors);
        try { if((await fetch(origin+'/admin/login.php')).ok) return origin; } catch {}
        await pause(100);
    }
    throw new Error('Server could not start: '+errors);
}

function client(origin) {
    const cookies = new Map();
    return {
        cookie: () => [...cookies].map(([k,v])=>`${k}=${v}`).join('; '),
        async request(path, options={}) {
            const response = await fetch(origin+path, { ...options, redirect:'manual', headers:{ Cookie:this.cookie(), ...options.headers } });
            for(const cookie of response.headers.getSetCookie()) { const pair=cookie.split(';')[0]; const at=pair.indexOf('='); cookies.set(pair.slice(0,at),pair.slice(at+1)); }
            return { response, status:response.status, html:await response.text(), location:response.headers.get('location') };
        },
        async post(path, values, token) {
            return this.request(path,{ method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:new URLSearchParams({...values,csrf_token:token}) });
        },
        async form(path) { const result=await this.request(path); assert.equal(result.status,200,result.html); return {...result,token:hidden(result.html,'csrf_token'),revision:hidden(result.html,'revision')}; },
        async login() {
            const page=await this.form('/admin/login.php'); const before=this.cookie();
            const result=await this.post('/admin/login.php',{email:'admin@ronan-tech.test',password:'DemoOnly!2026'},page.token);
            assert.equal(result.status,303,result.html); assert.equal(result.location,'/admin/'); assert.notEqual(this.cookie(),before);
            const dashboard=await this.form('/admin/'); return dashboard.token;
        },
        async checkout(items, details=customer) {
            const page=await this.request('/checkout.php');
            const token=page.html.match(/name="csrf-token" content="([^"]+)"/)[1];
            const post=async body=>{
                const result=await this.request('/api/checkout.php',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':token},body:JSON.stringify(body)});
                return {...result,data:JSON.parse(result.html)};
            };
            const review=await post({action:'review',items}); assert.equal(review.status,200,review.html);
            const saved=await post({action:'save',checkout_key:review.data.checkout_key,items,customer:details}); assert.equal(saved.status,200,saved.html);
            return saved.data;
        },
    };
}

function snapshot(name, html) {
    mkdirSync('tmp/stage4-review',{recursive:true});
    writeFileSync(`tmp/stage4-review/${name}.html`,html.replaceAll('href="/assets/','href="http://127.0.0.1:8000/assets/').replaceAll('src="/assets/','src="http://127.0.0.1:8000/assets/'));
}

try {
    fixture('create'); created=true;
    execFileSync(php,['scripts/setup-database.php'],{env,encoding:'utf8',windowsHide:true});
    const origins=[await startServer(18041),await startServer(18042)];
    const guest=client(origins[0]); const manager=client(origins[0]); const second=client(origins[1]);
    for(const route of ['/admin/','/admin/products.php','/admin/product.php','/admin/categories.php','/admin/category.php','/admin/orders.php','/admin/order.php?id=1','/admin/customers.php','/admin/logout.php']) {
        const result=await guest.request(route); assert.equal(result.status,303,route); assert.equal(result.location,'/admin/login.php');
        assert.equal((await guest.post(route,{name:'Unauthorized'},'fake')).status,303);
    }
    const login=await manager.form('/admin/login.php');
    assert.equal((await manager.post('/admin/login.php',{email:'admin@ronan-tech.test',password:'bad'},'wrong')).status,419);
    assert.equal((await manager.post('/admin/login.php',{email:'admin@ronan-tech.test',password:'bad'},login.token)).status,422);
    assert.equal((await manager.post('/admin/login.php',{email:'missing@example.test',password:'bad'},login.token)).status,422);
    const token=await manager.login(); const token2=await second.login();
    const cookie=await manager.request('/admin/'); assert.ok(cookie.html.includes('Demo administration'));
    for(const route of ['/admin/product.php','/admin/category.php','/admin/logout.php']) assert.equal((await manager.post(route,{},'wrong')).status,419);
    assert.equal((await manager.request('/admin/logout.php')).status,405);
    console.log('PASS: all protected routes and POSTs reject guests; invalid login/CSRF rejected; login rotates session; logout is POST-only');

    assert.equal((await manager.post('/admin/category.php',{name:'\u00a0'},token)).status,422);
    assert.equal((await manager.post('/admin/category.php',{name:'keyBOARDS'},token)).status,409);
    assert.equal((await manager.post('/admin/category.php',{name:'Demo Storage'},token)).status,303);
    let category=state().categories.find(row=>row.name==='Demo Storage');
    assert.equal((await manager.post(`/admin/category.php?id=${category.id}`,{name:'Demo Cables'},token)).status,303);
    const product={name:'Demo <b>Cable</b>',description:'Fictional USB cable for automated tests.',image_path:'/assets/images/accessories.svg',price:'85.50',stock:'7',category_id:String(category.id),is_active:'1',is_featured:'0'};
    for(const [key,value] of [['name',' '],['description',''],['price','0'],['price','1.001'],['stock','-1'],['stock','1.5'],['stock','4294967296'],['is_active','2'],['image_path','javascript:alert(1)'],['image_path','/assets/images/missing.svg'],['category_id','999999']]) {
        const result=await manager.post('/admin/product.php',{...product,[key]:value},token); assert.ok([409,422].includes(result.status),`${key}: ${result.status} ${result.html}`);
    }
    assert.equal((await manager.post('/admin/product.php',product,token)).status,303);
    let added=state().products.find(row=>row.name===product.name); let id=Number(added.id);
    const details=await guest.request(`/product.php?id=${id}`); assert.equal(details.status,200); assert.ok(details.html.includes('Demo &lt;b&gt;Cable&lt;/b&gt;')&&!details.html.includes('Demo <b>Cable</b>'));
    assert.ok(details.html.includes('PHP 85.50')&&details.html.includes(product.description));
    const blocked=await manager.post(`/admin/category.php?id=${category.id}&delete=yes`,{confirm:'yes'},token); assert.equal(blocked.status,409); assert.ok(blocked.html.includes('assigned products'));
    for(const [status,name] of [['inactive','Archive 60 Keyboard'],['out','Drift Silent Mouse'],['active','Demo &lt;b&gt;Cable&lt;/b&gt;']]) {
        const filtered=await manager.form(`/admin/products.php?status=${status}`); assert.ok(filtered.html.includes(name));
        if(status==='inactive') assert.ok(!filtered.html.includes('Drift Silent Mouse'));
        if(status==='out') assert.ok(!filtered.html.includes('Archive 60 Keyboard'));
    }
    assert.equal((await manager.request('/admin/products.php?status=bad')).status,400);
    let edit=await manager.form(`/admin/product.php?id=${id}`);
    assert.equal((await manager.post(`/admin/product.php?id=${id}`,{...product,is_active:'0',revision:edit.revision},token)).status,303);
    assert.equal((await guest.request(`/product.php?id=${id}`)).status,404);
    edit=await manager.form(`/admin/product.php?id=${id}`);
    assert.equal((await manager.post(`/admin/product.php?id=${id}`,{...product,revision:edit.revision},token)).status,303);
    const stale=await manager.form(`/admin/product.php?id=${id}`);
    await guest.checkout([{id,quantity:1}]);
    assert.equal((await manager.post(`/admin/product.php?id=${id}`,{...product,revision:stale.revision},token)).status,409);
    assert.equal(Number(state().products.find(row=>Number(row.id)===id).stock),6);
    edit=await manager.form(`/admin/product.php?id=${id}`);
    assert.equal((await manager.post(`/admin/product.php?id=${id}`,{...product,name:'Renamed Demo Cable',price:'99.00',stock:'6',revision:edit.revision},token)).status,303);
    assert.ok((await guest.request(`/product.php?id=${id}`)).html.includes('PHP 99.00'));
    const deleteForm=await manager.form(`/admin/product.php?id=${id}&delete=yes`);
    assert.equal((await manager.post(`/admin/product.php?id=${id}&delete=yes`,{confirm:'yes',revision:deleteForm.revision},token)).status,409);
    const order=state().orders[0];
    const orderPage=await manager.form(`/admin/order.php?id=${order.id}`);
    assert.ok(orderPage.html.includes('Demo &lt;b&gt;Cable&lt;/b&gt;')&&orderPage.html.includes('PHP 85.50')&&!orderPage.html.includes('Renamed Demo Cable'));
    assert.ok((await manager.form('/admin/orders.php')).html.includes(order.order_number));
    console.log('PASS: category validation/duplicates/assigned deletion; product CRUD validation/filters/visibility; checkout invalidates stale edit; snapshots and referenced deletion protected');

    // Create and delete a never-ordered product, then its now-empty category.
    assert.equal((await manager.post('/admin/category.php',{name:'Temporary Demo'},token)).status,303);
    const tempCat=state().categories.find(row=>row.name==='Temporary Demo');
    assert.equal((await manager.post('/admin/product.php',{...product,name:'Temporary Product',category_id:String(tempCat.id)},token)).status,303);
    const temporary=state().products.find(row=>row.name==='Temporary Product');
    const del=await manager.form(`/admin/product.php?id=${temporary.id}&delete=yes`);
    assert.ok(state().products.some(row=>row.id===temporary.id)); // GET confirmation never deletes.
    assert.equal((await manager.post(`/admin/product.php?id=${temporary.id}&delete=yes`,{confirm:'yes',revision:del.revision},token)).status,303);
    assert.equal((await manager.post(`/admin/category.php?id=${tempCat.id}&delete=yes`,{confirm:'yes'},token)).status,303);

    const statusPath=`/admin/order.php?id=${order.id}`;
    const update=(expected,next,client=manager,csrf=token,extra={})=>client.post(statusPath,{expected_status:expected,next_status:next,...extra},csrf);
    assert.equal((await update('Pending','Completed')).status,409);
    assert.equal((await update('Pending','Confirmed',manager,'wrong')).status,419);
    const allStatuses=['Pending','Confirmed','Preparing','Shipped','Completed','Cancelled'];
    for(const [from,to] of [['Pending','Confirmed'],['Confirmed','Preparing'],['Preparing','Shipped'],['Shipped','Completed']]) {
        const allowed=from==='Pending'||from==='Confirmed'?[to,'Cancelled']:[to];
        for(const invalid of allStatuses.filter(value=>!allowed.includes(value))) assert.equal((await update(from,invalid)).status,409,`${from} -> ${invalid}`);
        assert.equal((await update(from,to)).status,303);
    }
    for(const next of ['Pending','Confirmed','Cancelled','Completed']) assert.equal((await update('Completed',next,manager,token,{confirm_cancel:'yes'})).status,409);
    await guest.checkout([{id,quantity:1}],{...customer,name:'Latest Demo Buyer',email:' demo.buyer@EXAMPLE.TEST ',contact:'09171111111'});
    const cancel=state().orders.at(-1); const cancelPath=`/admin/order.php?id=${cancel.id}`;
    snapshot('order-pending',(await manager.form(cancelPath)).html);
    assert.equal((await manager.post(cancelPath,{expected_status:'Pending',next_status:'Cancelled'},token)).status,409);
    assert.equal((await manager.post(cancelPath,{expected_status:'Pending',next_status:'Confirmed'},token)).status,303);
    const stockBefore=Number(state().products.find(row=>Number(row.id)===id).stock);
    fixture('cancel-delay');
    const cancelBody={expected_status:'Confirmed',next_status:'Cancelled',confirm_cancel:'yes'};
    const results=await Promise.all([manager.post(cancelPath,cancelBody,token),second.post(cancelPath,cancelBody,token2)]);
    fixture('cancel-no-delay');
    assert.deepEqual(results.map(result=>result.status).sort(),[303,409]);
    assert.equal(Number(state().products.find(row=>Number(row.id)===id).stock),stockBefore+1);
    assert.ok(state().orders.find(row=>row.id===cancel.id).stock_restored_at);
    assert.equal((await manager.post(cancelPath,cancelBody,token)).status,409);
    await manager.form(cancelPath);
    assert.equal(Number(state().products.find(row=>Number(row.id)===id).stock),stockBefore+1);
    assert.equal((await manager.post(cancelPath,{expected_status:'Cancelled',next_status:'Pending'},token)).status,409);
    console.log('PASS: all forward transitions, skipped/backward/final guards; two processes cancel a Confirmed order with one restoration; repeat and refresh do not restore again');

    const initial=state(); const clips=initial.products.find(row=>row.name==='Desk Cable Clips'); const cloth=initial.products.find(row=>row.name==='Microfiber Screen Cloth');
    await guest.checkout([{id:Number(clips.id),quantity:2},{id:Number(cloth.id),quantity:1}],{...customer,email:'other.demo@example.test'});
    const failOrder=state().orders.at(-1); const failPath=`/admin/order.php?id=${failOrder.id}`;
    const beforeFailure=state(); fixture('restore-fail',cloth.id);
    const failure=await manager.post(failPath,{expected_status:'Pending',next_status:'Cancelled',confirm_cancel:'yes'},token);
    assert.equal(failure.status,503); assert.deepEqual(state(),beforeFailure);
    fixture('restore-allow');
    fixture('is_active',cloth.id,0);
    assert.equal((await manager.post(failPath,{expected_status:'Pending',next_status:'Cancelled',confirm_cancel:'yes'},token)).status,303);
    assert.equal(Number(state().products.find(row=>row.id===cloth.id).stock),Number(initial.products.find(row=>row.id===cloth.id).stock));
    fixture('is_active',cloth.id,1);
    const summary=JSON.parse(fixture('summary'));
    assert.deepEqual(Object.fromEntries(Object.entries(summary.metrics).map(([k,v])=>[k,Number(v)])),{products:13,orders:3,pending:0,completed:1,customers:2,sales:8550});
    const repeated=summary.customers.find(row=>row.email==='demo.buyer@example.test');
    assert.equal(repeated.customer_name,'Latest Demo Buyer'); assert.equal(repeated.customer_contact,'09171111111');
    assert.equal(Number(repeated.order_count),2); assert.equal(Number(repeated.spending),8550);
    console.log('PASS: restore failure rolls back all lines/status; inactive product restored; exact dashboard and latest-name normalized-email customer totals');

    snapshot('dashboard',(await manager.form('/admin/')).html);
    snapshot('products',(await manager.form('/admin/products.php')).html);
    snapshot('product-form',(await manager.form(`/admin/product.php?id=${id}`)).html);
    snapshot('categories',(await manager.form('/admin/categories.php')).html);
    snapshot('orders',(await manager.form('/admin/orders.php')).html);
    snapshot('order',(await manager.form(statusPath)).html);
    snapshot('customers',(await manager.form('/admin/customers.php')).html);
    snapshot('login',(await guest.form('/admin/login.php')).html);
    const oldCookie=manager.cookie();
    assert.equal((await manager.post('/admin/logout.php',{},token)).status,303);
    assert.notEqual(manager.cookie(),oldCookie);
    assert.equal((await manager.request('/admin/products.php')).status,303);
    // Restart one app process; a fresh login still sees all persisted rows.
    const exited=once(servers[0],'exit'); servers[0].kill(); await exited;
    const restarted=client(await startServer(18041)); await restarted.login();
    assert.ok((await restarted.form(statusPath)).html.includes('PHP 85.50'));
    assert.equal(state().orders.length,3);
    console.log('PASS: logout removes access; database order snapshots survive PHP restart; server-rendered demo snapshots saved for layout review');
    console.log(`Admin tests passed in isolated database ${database}.`);
} finally {
    for(const server of servers) if(server.exitCode===null && server.signalCode===null) { const exited=once(server,'exit'); server.kill(); await exited; }
    if(created) { fixture('drop'); console.log(`Removed isolated database ${database}.`); }
}
