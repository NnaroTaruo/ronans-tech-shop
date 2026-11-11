<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
require dirname(__DIR__) . '/app/catalog.php';
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 4294967295]]);
$product = null;
$related = [];
$catalogError = false;
try {
    $product = $id ? active_product($id) : null;
    if ($product) {
        $related = related_products($product);
    } else {
        http_response_code(404);
    }
} catch (Throwable) {
    http_response_code(503);
    $catalogError = true;
}
$pageTitle = ($product['name'] ?? 'Product unavailable') . " | Ronan's Tech Shop";
require dirname(__DIR__) . '/app/views/header.php';
?>
<main id="main" class="mx-auto max-w-6xl px-5 pt-8 sm:px-8">
    <a class="text-link inline-flex min-h-11 items-center" href="/products.php">Back to all products</a>
    <?php if (!$product || $catalogError): ?>
        <section class="notice mt-6"><h1 class="text-3xl font-semibold"><?= $catalogError ? 'Product temporarily unavailable' : 'Product unavailable' ?></h1><p class="mt-4"><?= $catalogError ? 'We could not load this product. Please try again shortly.' : 'This product could not be found or is no longer available.' ?></p><a class="button mt-6 inline-flex" href="/products.php">Browse products</a></section>
    <?php else: ?>
        <section class="mt-6 grid gap-8 lg:grid-cols-2 lg:items-start">
            <img class="w-full" src="<?= e(product_image($product['image_path'])) ?>" alt="<?= e($product['category_name']) ?> demo illustration" width="640" height="480">
            <div><p><?= e($product['category_name']) ?></p><h1 class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl"><?= e($product['name']) ?></h1><p class="mt-5 text-2xl font-semibold tabular-nums"><?= money($product['price_centavos']) ?></p><p class="mt-5 max-w-prose leading-relaxed"><?= e($product['description']) ?></p><p class="mt-5 font-semibold"><?= $product['stock'] ? $product['stock'] . ' available' : 'Out of Stock' ?></p>
                <form class="add-to-cart-form mt-5 max-w-sm" data-product-name="<?= e($product['name']) ?>">
                    <input type="hidden" name="id" value="<?= $product['id'] ?>">
                    <label for="quantity">Quantity</label><input class="field mt-2" id="quantity" name="quantity" type="number" inputmode="numeric" min="1" max="<?= $product['stock'] ?>" step="1" value="1" required <?= !$product['stock'] ? 'disabled' : '' ?> aria-describedby="quantity-help">
                    <p class="mt-2 text-sm" id="quantity-help"><?= $product['stock'] ? 'Choose a whole number up to the available stock.' : 'This product cannot be added while out of stock.' ?></p>
                    <button class="button mt-5 w-full" type="submit" disabled <?= $product['stock'] > 0 ? 'data-enable-cart' : '' ?>><?= $product['stock'] ? 'Add to cart' : 'Out of Stock' ?></button>
                </form>
            </div>
        </section>
        <?php if ($related): ?><section class="mt-14" aria-labelledby="related-heading"><h2 id="related-heading" class="text-2xl font-semibold">More in <?= e($product['category_name']) ?></h2><div class="product-grid mt-6"><?php foreach ($related as $product) { require dirname(__DIR__) . '/app/views/product-card.php'; } ?></div></section><?php endif; ?>
    <?php endif; ?>
</main>
<?php require dirname(__DIR__) . '/app/views/footer.php'; ?>
