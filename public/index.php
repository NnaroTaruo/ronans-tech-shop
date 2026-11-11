<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
require dirname(__DIR__) . '/app/catalog.php';
$pageTitle = "Ronan's Tech Shop | Keyboards, mice & accessories";
$catalogError = false;
try {
    $categories = categories();
    $featured = list_products(featured: true);
} catch (Throwable) {
    http_response_code(503);
    $catalogError = true;
    $categories = $featured = [];
}
require dirname(__DIR__) . '/app/views/header.php';
?>
<main id="main" class="mx-auto max-w-6xl px-5 sm:px-8">
    <section class="grid gap-8 py-12 sm:py-16 lg:grid-cols-2 lg:items-center" aria-labelledby="page-heading">
        <div>
            <h1 id="page-heading" class="text-4xl font-semibold tracking-tight sm:text-5xl">Good gear.<br>Your kind of desk.</h1>
            <p class="mt-6 max-w-lg text-lg leading-relaxed">Find your next keyboard, a comfortable mouse, and the little things that keep your desk in order.</p>
            <a class="button mt-7 inline-flex" href="/products.php">Shop all products</a>
        </div>
        <img class="w-full" src="/assets/images/keyboard.svg" alt="Illustration of a compact keyboard from the fictional demo catalog" width="640" height="480" fetchpriority="high">
    </section>
    <section class="bg-ink p-6 text-white sm:p-8" aria-labelledby="banner-heading">
        <h2 id="banner-heading" class="text-2xl font-semibold">Make room for better gear.</h2>
        <p class="mt-3 max-w-prose">Explore keyboards, mice, and desk accessories. Prices are in Philippine pesos. This is a fictional store; no real payments are collected.</p>
    </section>
    <?php if ($catalogError): ?>
        <section class="notice mt-10" aria-labelledby="error-heading"><h2 id="error-heading" class="text-2xl font-semibold">Catalog temporarily unavailable</h2><p class="mt-3">We couldn't load the products. Please refresh and try again shortly. Your saved cart is unchanged.</p><a class="text-link mt-4 inline-block" href="/">Try again</a></section>
    <?php else: ?>
        <section class="mt-12" aria-labelledby="categories-heading">
            <h2 id="categories-heading" class="text-2xl font-semibold">Shop by category</h2>
            <div class="mt-5 flex flex-wrap gap-3">
                <?php foreach ($categories as $category): ?><a class="button button-secondary" href="/products.php?category=<?= (int) $category['id'] ?>"><?= e($category['name']) ?></a><?php endforeach; ?>
            </div>
            <?php if (!$categories): ?><p class="mt-4">No categories are available yet. Check back soon.</p><?php endif; ?>
        </section>
        <section class="mt-12" aria-labelledby="featured-heading">
            <div class="flex flex-wrap items-center justify-between gap-4"><h2 id="featured-heading" class="text-2xl font-semibold">Featured gear</h2><a class="text-link" href="/products.php">View all products</a></div>
            <?php if ($featured): ?><div class="product-grid mt-6"><?php foreach ($featured as $product) { require dirname(__DIR__) . '/app/views/product-card.php'; } ?></div>
            <?php else: ?><p class="mt-5">No featured products yet. <a class="text-link" href="/products.php">Browse the catalog</a>.</p><?php endif; ?>
        </section>
    <?php endif; ?>
</main>
<?php require dirname(__DIR__) . '/app/views/footer.php'; ?>
