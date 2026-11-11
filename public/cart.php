<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
$pageTitle = "Your cart | Ronan's Tech Shop";
require dirname(__DIR__) . '/app/views/header.php';
?>
<main id="main" class="mx-auto max-w-6xl px-5 pt-10 sm:px-8" data-cart-page>
    <div class="flex flex-wrap items-center justify-between gap-4"><h1 class="text-3xl font-semibold tracking-tight sm:text-4xl">Your cart</h1><a class="text-link inline-flex min-h-11 items-center" href="/products.php">Continue shopping</a></div>
    <p class="mt-4 max-w-prose">Review quantities and current prices. Products stay in your cart after a refresh; stock is confirmed again when you make a change.</p>
    <p id="cart-state" class="notice mt-6" role="status" aria-live="polite">Loading your cart...</p>
    <div class="mt-8 hidden gap-8 lg:grid-cols-[1.6fr_1fr]" id="cart-content">
        <ul id="cart-items" class="space-y-5" aria-label="Cart products"></ul>
        <aside class="self-start bg-white p-6 sm:p-8" aria-labelledby="summary-heading"><h2 id="summary-heading" class="text-2xl font-semibold">Order summary</h2><dl class="mt-6 space-y-4 tabular-nums"><div class="flex justify-between gap-4"><dt>Subtotal</dt><dd id="cart-subtotal">PHP 0.00</dd></div><div class="flex justify-between gap-4 text-xl font-semibold"><dt>Total</dt><dd id="cart-total">PHP 0.00</dd></div></dl><p class="mt-4 text-sm">No tax, shipping fees, or discounts.</p><button class="button mt-6 w-full" id="cart-checkout" type="button" disabled aria-describedby="checkout-note">Continue to checkout</button><p class="mt-3 text-sm" id="checkout-note">Continue with a valid, nonempty cart. Prices and stock are checked again before saving.</p></aside>
    </div>
    <div class="mt-6 flex flex-wrap gap-4"><button class="button button-secondary hidden" type="button" id="cart-clear">Clear cart</button><button class="button button-secondary hidden" type="button" id="cart-retry">Check prices again</button></div>
    <template id="cart-item-template">
        <li class="cart-row bg-white p-5">
            <img class="cart-thumbnail" src="/assets/images/accessories.svg" width="120" height="90" alt="">
            <div class="min-w-0"><h2 class="text-xl font-semibold" data-item-name></h2><p class="mt-1 text-sm" data-item-category></p><p class="mt-2 tabular-nums" data-item-price></p><p class="mt-2 text-sm" data-item-stock></p><p class="mt-3 font-semibold text-danger" data-item-issue></p>
                <form class="cart-update mt-4 flex flex-wrap items-end gap-3"><div><label>Quantity<input class="field mt-1 w-24" name="quantity" type="number" inputmode="numeric" min="1" step="1" required></label></div><button class="button button-secondary" type="submit">Update</button><button class="text-link min-h-11" type="button" data-item-remove>Remove</button></form>
            </div>
            <p class="font-semibold tabular-nums" data-item-total></p>
        </li>
    </template>
</main>
<?php require dirname(__DIR__) . '/app/views/footer.php'; ?>
