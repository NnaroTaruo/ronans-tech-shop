import assert from 'node:assert/strict';
import { spawn, execFileSync } from 'node:child_process';
import { randomBytes } from 'node:crypto';
import { mkdirSync, writeFileSync } from 'node:fs';
import { once } from 'node:events';
import { cartAfterPurchase, clearPurchasedCart, PENDING_CHECKOUT_KEY } from '../public/assets/js/checkout.js';
import { CART_KEY } from '../public/assets/js/cart.js';

const database = `ronan_tech_shop_test_${randomBytes(6).toString('hex')}`;
const env = { ...process.env, TECH_SHOP_TEST_DATABASE: database };
const php = process.env.SHOP_PHP || 'php';
const fixture = (action, ...args) => execFileSync(php, ['tests/checkout-fixture.php', action, ...args.map(String)], { env, encoding: 'utf8', windowsHide: true });
const state = () => JSON.parse(fixture('state'));
const servers = [];
let created = false;
const customer = { name: 'Demo <b>Buyer</b>', email: '  DEMO.BUYER@example.test  ', contact: '+63 (917) 000-0000', address: '123 Demo Lane\nSample City, Philippines (fictional)', payment_method: 'cod', notes: 'Demo order only.' };
const pause = milliseconds => new Promise(resolve => setTimeout(resolve, milliseconds));

async function startServer(port) {
    const process = spawn(php, ['-S', `127.0.0.1:${port}`, '-t', 'public'], { env, stdio: ['ignore', 'ignore', 'pipe'], windowsHide: true });
    servers.push(process);
    let errors = '';
    process.stderr.on('data', chunk => { errors = (errors + chunk).slice(-4000); });
    const origin = `http://127.0.0.1:${port}`;
    for (let attempt = 0; attempt < 40; attempt++) {
        if (process.exitCode !== null) throw new Error(`Test server could not start on ${port}: ${errors}`);
        try { if ((await fetch(`${origin}/checkout.php`)).ok) return origin; } catch {}
        await pause(100);
    }
    throw new Error(`Test server did not become ready on ${port}: ${errors}`);
}

async function client(origin) {
    const response = await fetch(`${origin}/checkout.php`);
    assert.equal(response.status, 200);
    const html = await response.text();
    const token = html.match(/name="csrf-token" content="([a-f0-9]+)"/)[1];
    const cookie = response.headers.getSetCookie().map(value => value.split(';')[0]).join('; ');
    return {
        origin, cookie, html,
        async post(body, csrf = token) {
            const response = await fetch(`${origin}/api/checkout.php`, { method: 'POST', headers: { Cookie: cookie, 'Content-Type': 'application/json', 'X-CSRF-Token': csrf }, body: JSON.stringify(body) });
            return { status: response.status, data: await response.json() };
        },
        get: path => fetch(origin + path, { headers: { Cookie: cookie } }),
    };
}

try {
    fixture('create');
    created = true;
    execFileSync(php, ['scripts/setup-database.php'], { env, encoding: 'utf8', windowsHide: true });
    const origins = [await startServer(18031), await startServer(18032)];
    const initial = state();
    const find = name => initial.products.find(product => product.name === name);
    const clips = find('Desk Cable Clips');
    const cloth = find('Microfiber Screen Cloth');
    const mouse = find('Glide Wireless Mouse');
    const inactive = find('Archive 60 Keyboard');
    const zero = find('Drift Silent Mouse');
    const items = [{ id: Number(clips.id), quantity: 2 }, { id: Number(cloth.id), quantity: 1 }];
    const buyer = await client(origins[0]);
    assert.equal((await buyer.post({ action: 'review', items: [] })).status, 422);
    assert.equal((await buyer.post({ action: 'review', items }, 'wrong')).status, 419);
    assert.equal((await buyer.get('/api/checkout.php')).status, 405);
    for (const quantity of [0, -1, 1.5, '2', true, 4294967296]) assert.equal((await buyer.post({ action: 'review', items: [{ id: Number(clips.id), quantity }] })).status, 422);
    for (const product of [inactive, zero, { id: 4294967295 }]) {
        const invalid = await buyer.post({ action: 'review', items: [{ id: Number(product.id), quantity: 1 }] });
        assert.equal(invalid.status, 422);
        assert.equal(invalid.data.quote.valid, false);
    }
    const review = await buyer.post({ action: 'review', items });
    assert.equal(review.status, 200);
    assert.equal(review.data.quote.total_centavos, 32550);
    const body = { action: 'save', checkout_key: review.data.checkout_key, items, customer, total_centavos: 1 };
    for (const [field, value] of [['name', ' \t\n'], ['name', '\u00a0'], ['email', 'invalid'], ['contact', '---'], ['address', ' '], ['payment_method', 'crypto'], ['notes', ['invalid']]]) {
        const invalid = await buyer.post({ ...body, customer: { ...customer, [field]: value } });
        assert.equal(invalid.status, 422);
        assert.ok(invalid.data.errors[field]);
    }
    assert.equal(state().orders.length, 0);
    const changedCart = await buyer.post({ ...body, items: [{ id: Number(clips.id), quantity: 1 }] });
    assert.equal(changedCart.status, 409);
    const saved = await buyer.post(body);
    assert.equal(saved.status, 200);
    assert.equal(saved.data.saved, true);
    const after = state();
    assert.equal(after.orders.length, 1);
    assert.equal(Number(after.orders[0].total_centavos), 32550);
    assert.equal(after.orders[0].customer_email, 'demo.buyer@example.test');
    assert.equal(after.orders[0].status, 'Pending');
    assert.equal(Number(after.products.find(product => product.id === clips.id).stock), Number(clips.stock) - 2);
    assert.equal(Number(after.products.find(product => product.id === cloth.id).stock), Number(cloth.stock) - 1);
    const duplicates = await Promise.all([buyer.post(body), buyer.post(body)]);
    assert.ok(duplicates.every(result => result.status === 200 && result.data.order_number === saved.data.order_number));
    assert.equal(state().orders.length, 1);
    assert.deepEqual(state().products, after.products);
    const stranger = await client(origins[1]);
    assert.equal((await stranger.post(body)).status, 419);
    assert.equal((await stranger.get(saved.data.confirmation_url)).status, 404);
    const confirmation = await buyer.get(saved.data.confirmation_url);
    assert.equal(confirmation.status, 200);
    const confirmationHtml = await confirmation.text();
    assert.ok(confirmationHtml.includes('PHP 325.50') && confirmationHtml.includes('Pending') && confirmationHtml.includes('&lt;b&gt;Buyer&lt;/b&gt;') && !confirmationHtml.includes('<b>Buyer</b>'));
    fixture('name', clips.id, 'Edited demo product');
    fixture('price_centavos', clips.id, 13000);
    const oldOrder = await (await buyer.get(saved.data.confirmation_url)).text();
    assert.ok(oldOrder.includes('Desk Cable Clips') && oldOrder.includes('PHP 120.00') && !oldOrder.includes('Edited demo product'));
    fixture('name', clips.id, clips.name);
    fixture('price_centavos', clips.id, 12000);
    console.log('PASS: field/quantity/CSRF guards, exact save/stock/snapshots, normalized email, escaped private confirmation, repeated submission');

    const priceBuyer = await client(origins[0]);
    const one = [{ id: Number(clips.id), quantity: 1 }];
    const priceReview = await priceBuyer.post({ action: 'review', items: one });
    const priceBody = { action: 'save', checkout_key: priceReview.data.checkout_key, items: one, customer: { ...customer, notes: '0', payment_method: 'bank_transfer' } };
    fixture('price_centavos', clips.id, 12100);
    const changed = await priceBuyer.post(priceBody);
    assert.equal(changed.status, 409);
    assert.ok(changed.data.needs_review && changed.data.changes.some(text => text.includes('PHP 121.00')));
    assert.equal((await priceBuyer.post(priceBody)).status, 409);
    assert.equal(state().orders.length, 1);
    const reviewedAgain = await priceBuyer.post({ action: 'review', items: one });
    assert.equal(reviewedAgain.data.checkout_key, priceBody.checkout_key);
    assert.equal((await priceBuyer.post(priceBody)).status, 200);
    assert.equal(state().orders[1].notes, '0');
    fixture('price_centavos', clips.id, 12000);
    const stockBuyer = await client(origins[0]);
    const stockReview = await stockBuyer.post({ action: 'review', items: one });
    fixture('stock', clips.id, 36);
    assert.equal((await stockBuyer.post({ ...priceBody, checkout_key: stockReview.data.checkout_key })).status, 409);
    const activeBuyer = await client(origins[0]);
    const clothCart = [{ id: Number(cloth.id), quantity: 1 }];
    const activeReview = await activeBuyer.post({ action: 'review', items: clothCart });
    fixture('is_active', cloth.id, 0);
    const activeChanged = await activeBuyer.post({ action: 'save', checkout_key: activeReview.data.checkout_key, items: clothCart, customer });
    assert.equal(activeChanged.status, 409);
    assert.equal(activeChanged.data.quote.valid, false);
    fixture('is_active', cloth.id, 1);
    console.log('PASS: changed price, stock and active status block saving; explicit review required before retry');

    const retryBuyer = await client(origins[0]);
    const retryItems = [{ id: Number(clips.id), quantity: 1 }, { id: Number(cloth.id), quantity: 1 }];
    const retryReview = await retryBuyer.post({ action: 'review', items: retryItems });
    const retryBody = { action: 'save', checkout_key: retryReview.data.checkout_key, items: retryItems, customer: { ...customer, payment_method: 'ewallet' } };
    const beforeFailure = state();
    fixture('fail-item', cloth.id);
    const failed = await retryBuyer.post(retryBody);
    assert.equal(failed.status, 503);
    assert.ok(!failed.data.saved);
    assert.deepEqual(state(), beforeFailure);
    fixture('allow-items');
    const recovered = await retryBuyer.post(retryBody);
    assert.equal(recovered.status, 200);
    const recoveredReview = await retryBuyer.post({ action: 'review', retry_key: retryBody.checkout_key, items: retryItems });
    assert.equal(recoveredReview.data.order_number, recovered.data.order_number);
    assert.equal(state().orders.length, beforeFailure.orders.length + 1);
    console.log('PASS: failure after a first line rolls back order/items/stock; same-key retry and lost-response recovery save once');

    fixture('stock', mouse.id, 1);
    fixture('delay');
    const raceA = await client(origins[0]);
    const raceB = await client(origins[1]);
    const lastUnit = [{ id: Number(mouse.id), quantity: 1 }];
    const [reviewA, reviewB] = await Promise.all([raceA.post({ action: 'review', items: lastUnit }), raceB.post({ action: 'review', items: lastUnit })]);
    const [resultA, resultB] = await Promise.all([raceA.post({ action: 'save', checkout_key: reviewA.data.checkout_key, items: lastUnit, customer }), raceB.post({ action: 'save', checkout_key: reviewB.data.checkout_key, items: lastUnit, customer })]);
    assert.deepEqual([resultA.status, resultB.status].sort(), [200, 409]);
    fixture('no-delay');
    const final = state();
    assert.equal(Number(final.products.find(product => product.id === mouse.id).stock), 0);
    assert.equal(final.orders.length, beforeFailure.orders.length + 2);
    assert.equal(new Set(final.orders.map(order => order.order_number)).size, final.orders.length);
    console.log('PASS: two PHP server processes compete for one unit; exactly one order saves and stock ends at zero');

    const originalCart = [{ id: 1, quantity: 3 }, { id: 2, quantity: 1 }];
    assert.deepEqual(cartAfterPurchase(originalCart, [{ id: 1, quantity: 2 }]), [{ id: 1, quantity: 1 }, { id: 2, quantity: 1 }]);
    assert.deepEqual(cartAfterPurchase(items, items), []);
    assert.throws(() => cartAfterPurchase(items, [{ id: 1, quantity: -1 }]));
    const cartData = new Map([[CART_KEY, JSON.stringify(originalCart)]]);
    const pendingData = new Map([[PENDING_CHECKOUT_KEY, JSON.stringify({ key: 'a'.repeat(64), items: [{ id: 1, quantity: 2 }] })]]);
    const cartStorage = { getItem: key => cartData.get(key) ?? null, setItem: (key, value) => cartData.set(key, value) };
    const pendingStorage = { getItem: key => pendingData.get(key) ?? null, setItem: (key, value) => pendingData.set(key, value), removeItem() { throw new Error('receipt removal blocked'); } };
    const cleanup = { checkout_key: 'a'.repeat(64), items: [{ id: 1, quantity: 2 }] };
    assert.equal(clearPurchasedCart(cleanup, cartStorage, pendingStorage), true);
    const clearedOnce = cartData.get(CART_KEY);
    assert.deepEqual(JSON.parse(clearedOnce), [{ id: 1, quantity: 1 }, { id: 2, quantity: 1 }]);
    assert.equal(clearPurchasedCart(cleanup, cartStorage, pendingStorage), true);
    assert.equal(cartData.get(CART_KEY), clearedOnce);
    pendingData.set(PENDING_CHECKOUT_KEY, JSON.stringify({ key: 'b'.repeat(64), items: [{ id: 1, quantity: 1 }] }));
    const blockedCart = { getItem: key => cartData.get(key), setItem() { throw new Error('cart write blocked'); } };
    assert.equal(clearPurchasedCart({ checkout_key: 'b'.repeat(64), items: [{ id: 1, quantity: 1 }] }, blockedCart, pendingStorage), false);
    assert.equal(cartData.get(CART_KEY), clearedOnce);
    assert.equal(JSON.parse(pendingData.get(PENDING_CHECKOUT_KEY)).cleared, false);
    mkdirSync('tmp/stage3-review', { recursive: true });
    writeFileSync('tmp/stage3-review/confirmation.html', confirmationHtml.replaceAll('href="/assets/', 'href="http://127.0.0.1:8000/assets/').replaceAll('src="/assets/', 'src="http://127.0.0.1:8000/assets/'));
    console.log('PASS: cart cleanup preserves additions, blocks repeated subtraction after receipt-removal failure, and preserves cart on write failure; real browser flow remains a manual check');
    console.log(`Checkout tests passed in isolated database ${database}.`);
} finally {
    for (const server of servers) {
        if (server.exitCode === null) {
            const exited = once(server, 'exit');
            server.kill();
            await exited;
        }
    }
    if (created) { fixture('drop'); console.log(`Removed isolated database ${database}.`); }
}
