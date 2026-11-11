<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
require dirname(__DIR__) . '/app/checkout.php';
$number = is_string($_GET['order'] ?? null) ? $_GET['order'] : '';
$key = $_SESSION['order_access'][$number] ?? null;
$order = null;
$loadError = false;
try {
    $order = $key ? saved_order(db(), $key) : null;
    if (!$order || $order['order_number'] !== $number) {
        $order = null;
        http_response_code(404);
    }
} catch (Throwable) {
    $loadError = true;
    http_response_code(503);
}
$pageTitle = ($order ? 'Order saved' : 'Order unavailable') . " | Ronan's Tech Shop";
require dirname(__DIR__) . '/app/views/header.php';
?>
<main id="main" class="mx-auto max-w-6xl px-5 pt-10 sm:px-8">
    <?php if (!$order): ?>
        <section class="notice"><h1 class="text-3xl font-semibold"><?= $loadError ? 'Order temporarily unavailable' : 'Order unavailable' ?></h1><p class="mt-4 max-w-prose"><?= $loadError ? 'We could not load your saved order. Refresh and try again shortly.' : 'Open the confirmation in the browser session used to place the order. An order number alone does not grant access.' ?></p><a class="button mt-5 inline-flex" href="/cart.php">Return to cart</a></section>
    <?php else: ?>
        <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl">Your demo order is saved.</h1>
        <p class="mt-4 max-w-prose">Thank you, <?= e($order['customer_name']) ?>. No real payment has been collected. Keep your order number for this demo.</p>
        <div id="cart-clear-warning" class="notice mt-6 hidden" role="alert">Your order is saved, but browser storage prevented clearing its cart items. Check your cart before placing another order.</div>
        <section class="mt-8 grid gap-8 lg:grid-cols-2" aria-labelledby="saved-heading">
            <div class="min-w-0 bg-white p-6 sm:p-8"><h2 id="saved-heading" class="text-2xl font-semibold">Order details</h2><dl class="mt-6 space-y-4"><div><dt class="font-semibold">Order number</dt><dd class="mt-1 break-words" id="saved-order-number"><?= e($number) ?></dd></div><div><dt class="font-semibold">Status</dt><dd class="mt-1"><?= e($order['status']) ?></dd></div><div><dt class="font-semibold">Date</dt><dd class="mt-1"><?= e((new DateTimeImmutable($order['created_at'], new DateTimeZone('UTC')))->setTimezone(new DateTimeZone('Asia/Manila'))->format('M j, Y g:i A')) ?> (Philippine time)</dd></div><div><dt class="font-semibold">Payment method</dt><dd class="mt-1"><?= e(PAYMENT_METHODS[$order['payment_method']]) ?></dd></div></dl></div>
            <div class="min-w-0 bg-white p-6 sm:p-8"><h2 class="text-2xl font-semibold">Customer and delivery</h2><p class="mt-6 break-words"><?= e($order['customer_name']) ?><br><?= e($order['customer_email']) ?><br><?= e($order['customer_contact']) ?></p><p class="mt-5 break-words"><?= nl2br(e($order['delivery_address'])) ?></p><p class="mt-5 font-semibold">Order notes</p><p class="mt-2 break-words"><?= $order['notes'] !== null && $order['notes'] !== '' ? nl2br(e($order['notes'])) : 'None' ?></p><p class="mt-5 text-sm">Fictional customer details submitted for this demo.</p></div>
        </section>
        <section class="mt-8 bg-white p-6 sm:p-8" aria-labelledby="items-heading"><h2 id="items-heading" class="text-2xl font-semibold">Saved items</h2><ul class="mt-6 space-y-5"><?php foreach ($order['items'] as $item): ?><li class="flex flex-wrap justify-between gap-3"><div class="min-w-0"><p class="break-words font-semibold"><?= e($item['product_name']) ?></p><p class="mt-1 tabular-nums"><?= $item['quantity'] ?> × <?= money($item['unit_price_centavos']) ?></p></div><p class="tabular-nums"><?= money($item['quantity'] * $item['unit_price_centavos']) ?></p></li><?php endforeach; ?></ul><dl class="mt-8 space-y-3 tabular-nums"><div class="flex justify-between gap-4"><dt>Subtotal</dt><dd><?= money((int) $order['subtotal_centavos']) ?></dd></div><div class="flex justify-between gap-4 text-xl font-semibold"><dt>Total</dt><dd><?= money((int) $order['total_centavos']) ?></dd></div></dl></section>
        <a class="button mt-8 inline-flex" href="/products.php">Continue shopping</a>
        <script type="application/json" id="saved-order-data"><?= json_encode(['checkout_key' => $key, 'items' => array_map(fn ($item) => ['id' => $item['id'], 'quantity' => $item['quantity']], $order['items'])], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
        <script type="module" src="/assets/js/checkout.js"></script>
    <?php endif; ?>
</main>
<?php require dirname(__DIR__) . '/app/views/footer.php'; ?>
