import assert from 'node:assert/strict';
import { CART_KEY, normalizeCart, readCart, writeCart, addItem, formatMoney } from '../public/assets/js/cart.js';

const saved = new Map();
const storage = { getItem: key => saved.get(key) ?? null, setItem: (key, value) => saved.set(key, value) };
writeCart(addItem(addItem([], 7, 1), 7, 1), storage);
assert.deepEqual(readCart(storage), [{ id: 7, quantity: 2 }]);
writeCart([{ id: 7, quantity: 2, price: 1, stock: 1000 }], storage);
assert.equal(saved.get(CART_KEY), '[{"id":7,"quantity":2}]');
for (const quantity of [0, -1, 1.5, '2', null, true, 4294967296]) assert.throws(() => normalizeCart([{ id: 7, quantity }]));
assert.throws(() => addItem([{ id: 7, quantity: 3 }], 7, -1));
assert.throws(() => addItem([{ id: 7, quantity: 3 }], 7, '2'));
assert.deepEqual(addItem([{ id: 7, quantity: 1 }, { id: 7, quantity: 1 }], 7, 1), [{ id: 7, quantity: 3 }]);
assert.equal(formatMoney(2 * 12000 + 8550), 'PHP 325.50');
assert.equal(formatMoney(1), 'PHP 0.01');
assert.throws(() => formatMoney(Number.MAX_SAFE_INTEGER + 1));
assert.throws(() => writeCart([], { setItem() { throw new Error('storage blocked'); } }));
const fullCart = Array.from({ length: 100 }, (_, index) => ({ id: index + 1, quantity: 1 }));
assert.equal(addItem(fullCart, 1, 1)[0].quantity, 2);
assert.throws(() => addItem(fullCart, 101, 1));
assert.deepEqual(readCart(storage), [{ id: 7, quantity: 2 }]);
storage.setItem(CART_KEY, 'invalid json');
assert.throws(() => readCart(storage));
assert.equal(saved.get(CART_KEY), 'invalid json');
console.log('PASS: duplicate merge, IDs/quantities-only persistence, invalid quantities, exact money, and storage-error preservation');

const origin = process.env.SHOP_TEST_URL || 'http://127.0.0.1:8000';
const response = await fetch(`${origin}/products.php`);
assert.equal(response.status, 200);
const html = await response.text();
const csrf = html.match(/name="csrf-token" content="([a-f0-9]+)"/)[1];
const cookie = response.headers.getSetCookie().map(value => value.split(';')[0]).join('; ');
const cards = text => [...text.matchAll(/<article class="product-card" data-product-id="(\d+)" data-price-centavos="(\d+)">/g)].map(match => ({ id: Number(match[1]), price: Number(match[2]) }));
assert.equal(cards(html).length, 11);
assert.ok(!html.includes('Archive 60 Keyboard') && html.includes('Drift Silent Mouse'));
const miceCategory = html.match(/<option value="(\d+)"[^>]*>Mice<\/option>/)[1];
for (const sort of ['price_asc', 'price_desc']) {
    const page = await fetch(`${origin}/products.php?q=mOuSe&category=${miceCategory}&sort=${sort}`);
    const found = cards(await page.text());
    assert.equal(found.length, 4);
    const prices = found.map(item => item.price);
    assert.deepEqual(prices, [...prices].sort((a, b) => sort === 'price_asc' ? a - b : b - a));
}
const noMatch = await fetch(`${origin}/products.php?q=not-a-real-product`);
assert.ok((await noMatch.text()).includes('No products found'));
for (const path of ['/products.php?sort=sql', '/products.php?category[]=1', '/products.php?q[]=bad']) {
    assert.equal((await fetch(origin + path)).status, 400);
}
for (const id of ['missing', '-1', '4294967295', '1%20OR%201=1']) assert.equal((await fetch(`${origin}/product.php?id=${id}`)).status, 404);
const getProduct = async name => cards(await (await fetch(`${origin}/products.php?q=${encodeURIComponent(name)}`)).text())[0];
const clips = await getProduct('Desk Cable Clips');
const cloth = await getProduct('Microfiber Screen Cloth');
const zero = await getProduct('Drift Silent Mouse');
const post = (items, token = csrf) => fetch(`${origin}/api/cart.php`, {
    method: 'POST', headers: { Cookie: cookie, 'Content-Type': 'application/json', 'X-CSRF-Token': token }, body: JSON.stringify({ items }),
});
const quote = await (await post([{ id: clips.id, quantity: 2, price_centavos: 1 }, { id: cloth.id, quantity: 1 }])).json();
assert.equal(quote.total_centavos, 32550);
assert.equal(quote.subtotal_centavos, 32550);
assert.equal((await post([{ id: clips.id, quantity: 0 }])).status, 422);
assert.equal((await post([], 'wrong')).status, 419);
assert.equal((await fetch(`${origin}/api/cart.php`)).status, 405);
const unavailable = await (await post([{ id: zero.id, quantity: 1 }])).json();
assert.equal(unavailable.valid, false);
assert.equal(unavailable.total_centavos, null);
assert.ok(unavailable.items[0].issue.includes('Out of stock'));
const empty = await (await post([])).json();
assert.equal(empty.valid, false);
assert.equal(empty.total_centavos, 0);
console.log('PASS: HTTP listing/filter/sorts, empty and invalid states, API CSRF/method/quantity guards, authoritative totals and zero-stock rejection');
console.log('Storage round trips above use a test storage object; actual browser refresh and interaction still need browser verification.');
