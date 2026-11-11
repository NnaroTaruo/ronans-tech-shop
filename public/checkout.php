<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
require dirname(__DIR__) . '/app/checkout.php';
$pageTitle = "Checkout | Ronan's Tech Shop";
require dirname(__DIR__) . '/app/views/header.php';
?>
<main id="main" class="mx-auto max-w-6xl px-5 pt-10 sm:px-8" data-checkout-page>
    <a class="text-link inline-flex min-h-11 items-center" href="/cart.php">Back to cart</a>
    <h1 class="mt-4 text-3xl font-semibold tracking-tight sm:text-4xl">Guest checkout</h1>
    <p class="mt-4 max-w-prose">Review your order and delivery details. Use fictional customer information for this demo. No real payment is collected.</p>
    <div id="checkout-status" class="notice mt-6" role="status" aria-live="polite" tabindex="-1">Loading current prices and stock...</div>
    <ul id="checkout-changes" class="mt-4 list-inside list-disc space-y-2" aria-label="Product changes"></ul>
    <button class="button button-secondary mt-4 hidden" id="checkout-review" type="button">Review current order</button>
    <div class="mt-8 grid gap-8 lg:grid-cols-[1.3fr_1fr]">
        <section class="bg-white p-6 sm:p-8" aria-labelledby="customer-heading">
            <h2 id="customer-heading" class="text-2xl font-semibold">Delivery details</h2>
            <form id="checkout-form" class="mt-6 space-y-5" novalidate>
                <?php foreach (['name' => ['Full name', 'text', 120, 'name'], 'email' => ['Email address', 'email', 254, 'email'], 'contact' => ['Contact number', 'tel', 40, 'tel']] as $field => [$label, $type, $limit, $autocomplete]): ?>
                    <div><label for="checkout-<?= $field ?>"><?= $label ?> <span class="text-sm">(required)</span></label><input class="field mt-2" id="checkout-<?= $field ?>" name="<?= $field ?>" type="<?= $type ?>" maxlength="<?= $limit ?>" autocomplete="<?= $autocomplete ?>" required aria-describedby="<?= $field ?>-error<?= $field === 'contact' ? ' contact-help' : '' ?>"><p id="<?= $field ?>-error" class="mt-2 text-sm text-danger" data-field-error="<?= $field ?>"></p><?php if ($field === 'contact'): ?><p id="contact-help" class="mt-2 text-sm">7 to 15 digits; spaces, +, parentheses, dots and hyphens are welcome.</p><?php endif; ?></div>
                <?php endforeach; ?>
                <div><label for="checkout-address">Delivery address <span class="text-sm">(required)</span></label><textarea class="field mt-2" id="checkout-address" name="address" rows="3" maxlength="2000" autocomplete="street-address" required aria-describedby="address-error"></textarea><p id="address-error" class="mt-2 text-sm text-danger" data-field-error="address"></p></div>
                <div><label for="checkout-payment_method">Payment method <span class="text-sm">(required)</span></label><select class="field mt-2" id="checkout-payment_method" name="payment_method" required aria-describedby="payment_method-error payment-help"><option value="">Choose a payment method</option><?php foreach (PAYMENT_METHODS as $value => $label): ?><option value="<?= $value ?>"><?= $label ?></option><?php endforeach; ?></select><p id="payment_method-error" class="mt-2 text-sm text-danger" data-field-error="payment_method"></p><p id="payment-help" class="mt-2 text-sm">Selection only. Do not send money or enter financial account details.</p></div>
                <div><label for="checkout-notes">Order notes <span class="text-sm">(optional)</span></label><textarea class="field mt-2" id="checkout-notes" name="notes" rows="3" maxlength="2000" aria-describedby="notes-error"></textarea><p id="notes-error" class="mt-2 text-sm text-danger" data-field-error="notes"></p></div>
                <button class="button w-full" id="checkout-save" type="submit" disabled>Place demo order</button>
                <p class="text-sm">Your order is confirmed only after it has been saved successfully.</p>
            </form>
        </section>
        <aside class="self-start bg-white p-6 sm:p-8" aria-labelledby="review-heading"><h2 id="review-heading" class="text-2xl font-semibold">Your order</h2><ul id="checkout-items" class="mt-6 space-y-5" aria-label="Order items"></ul><dl class="mt-7 space-y-4 tabular-nums"><div class="flex justify-between gap-4"><dt>Subtotal</dt><dd id="checkout-subtotal">Checking...</dd></div><div class="flex justify-between gap-4 text-xl font-semibold"><dt>Total</dt><dd id="checkout-total">Checking...</dd></div></dl><p class="mt-4 text-sm">Subtotal equals total. No tax, shipping fees, or discounts.</p></aside>
    </div>
    <template id="checkout-item-template"><li><p class="font-semibold" data-line-name></p><div class="mt-1 flex flex-wrap justify-between gap-2 tabular-nums"><p data-line-details></p><p data-line-total></p></div><p class="mt-2 text-sm" data-line-stock></p><p class="mt-2 text-sm text-danger" data-line-issue></p></li></template>
</main>
<script type="module" src="/assets/js/checkout.js"></script>
<?php require dirname(__DIR__) . '/app/views/footer.php'; ?>
