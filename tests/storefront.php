<?php
declare(strict_types=1);
require __DIR__ . '/foundation.php';
require dirname(__DIR__) . '/app/catalog.php';

$pdo = db();
$all = $pdo->query('SELECT * FROM products')->fetchAll();
$byName = array_column($all, null, 'name');
$clips = $byName['Desk Cable Clips'];
$cloth = $byName['Microfiber Screen Cloth'];
$inactive = $byName['Archive 60 Keyboard'];
$zero = $byName['Drift Silent Mouse'];
$mice = list_products('mOuSe', (int) $zero['category_id'], 'price_asc');
$prices = array_column($mice, 'price_centavos');
$sorted = $prices;
sort($sorted, SORT_NUMERIC);
check(count($mice) === 4 && $prices === $sorted, 'combined case-insensitive search, category and ascending price');
$descending = array_column(list_products('MOUSE', (int) $zero['category_id'], 'price_desc'), 'price_centavos');
check($descending === array_reverse($sorted), 'combined descending price sort');
check(list_products('mouse', (int) $inactive['category_id']) === [] && list_products('%') === [], 'no-match state and literal wildcard search');
check(active_product((int) $inactive['id']) === null && active_product(4294967295) === null, 'inactive and missing direct product reads blocked');
check(active_product((int) $zero['id'])['stock'] === 0, 'active zero-stock product remains visible');
$related = related_products(active_product((int) $clips['id']));
check(count($related) === 3 && !in_array((int) $clips['id'], array_column($related, 'id'), true)
    && count(array_filter($related, fn ($row) => $row['category_id'] !== (int) $clips['category_id'] || !$row['is_active'])) === 0, 'three related active same-category products exclude current product');
$cart = [['id' => (int) $clips['id'], 'quantity' => 2, 'price_centavos' => 1], ['id' => (int) $cloth['id'], 'quantity' => 1]];
$quote = quote_cart($cart);
check($quote['valid'] && $quote['total_centavos'] === 32550 && $quote['subtotal_centavos'] === 32550, 'authoritative cart totals ignore a forged client price');
$duplicate = quote_cart([['id' => (int) $clips['id'], 'quantity' => 1], ['id' => (int) $clips['id'], 'quantity' => 1]]);
check(count($duplicate['items']) === 1 && $duplicate['items'][0]['quantity'] === 2, 'server merges duplicate cart IDs');
foreach ([0, -1, 1.5, '2', true, 4294967296] as $quantity) {
    try {
        quote_cart([['id' => (int) $clips['id'], 'quantity' => $quantity]]);
        check(false, 'reject invalid server quantity');
    } catch (InvalidArgumentException) {}
}
echo "PASS: invalid server quantities rejected\n";
foreach ([$inactive, $zero, ['id' => 4294967295]] as $unavailable) {
    $quote = quote_cart([['id' => (int) $unavailable['id'], 'quantity' => 1]]);
    check(!$quote['valid'] && $quote['total_centavos'] === null && $quote['items'][0]['issue'] !== '', 'unavailable cart item blocks total');
}
$excess = quote_cart([['id' => (int) $clips['id'], 'quantity' => (int) $clips['stock'] + 1]]);
check(!$excess['valid'] && $excess['items'][0]['quantity'] === (int) $clips['stock'] + 1, 'excess quantity rejected without silently changing the cart');
check(!quote_cart([])['valid'] && quote_cart([])['total_centavos'] === 0, 'empty cart is not eligible to proceed');
try {
    $pdo->beginTransaction();
    $pdo->prepare('UPDATE products SET price_centavos = price_centavos + 100 WHERE id = ?')->execute([$clips['id']]);
    check(quote_cart($cart)['total_centavos'] === 32750, 'cart reads changed database price');
    $pdo->prepare('UPDATE products SET is_active = 0 WHERE id = ?')->execute([$clips['id']]);
    check(!quote_cart($cart)['valid'], 'cart reads changed active status');
} finally {
    $pdo->rollBack();
}
check(quote_cart($cart)['total_centavos'] === 32550, 'temporary test edits rolled back');
echo "Storefront server checks complete. No orders were placed or stock reserved.\n";
