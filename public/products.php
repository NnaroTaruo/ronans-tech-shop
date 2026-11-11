<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
require dirname(__DIR__) . '/app/catalog.php';
$pageTitle = "Products | Ronan's Tech Shop";
$errors = [];
$search = is_string($_GET['q'] ?? '') ? trim($_GET['q'] ?? '') : '';
$sort = is_string($_GET['sort'] ?? 'name') ? ($_GET['sort'] ?? 'name') : '';
$rawCategory = $_GET['category'] ?? '';
$categoryId = $rawCategory === '' ? null : filter_var($rawCategory, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 4294967295]]);
if (!is_string($_GET['q'] ?? '') || mb_strlen($search) > 120) {
    $errors[] = 'Use a product search of 120 characters or fewer.';
}
if (!in_array($sort, ['name', 'price_asc', 'price_desc'], true)) {
    $errors[] = 'Choose one of the available sorting options.';
    $sort = 'name';
}
$catalogError = false;
$categories = $products = [];
try {
    $categories = categories();
    if ($categoryId === false || ($categoryId !== null && !in_array($categoryId, array_map('intval', array_column($categories, 'id')), true))) {
        $errors[] = 'Choose an existing category, or select all categories.';
        $categoryId = null;
    }
    if (!$errors) {
        $products = list_products($search, $categoryId, $sort);
    } else {
        http_response_code(400);
    }
} catch (Throwable) {
    http_response_code(503);
    $catalogError = true;
}
require dirname(__DIR__) . '/app/views/header.php';
?>
<main id="main" class="mx-auto max-w-6xl px-5 pt-10 sm:px-8">
    <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl">Find your desk essentials.</h1>
    <p class="mt-4 max-w-prose">Browse our fictional catalog. Search, category, and price order work together.</p>
    <form class="filters mt-7" action="/products.php" method="get" role="search">
        <div><label for="search">Product name</label><input class="field" id="search" type="search" name="q" maxlength="120" value="<?= e(mb_substr($search, 0, 120)) ?>" placeholder="Search keyboards, mice, and more"></div>
        <div><label for="category">Category</label><select class="field" id="category" name="category"><option value="">All categories</option><?php foreach ($categories as $category): ?><option value="<?= (int) $category['id'] ?>" <?= $categoryId === (int) $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option><?php endforeach; ?></select></div>
        <div><label for="sort">Sort by</label><select class="field" id="sort" name="sort"><option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Name: A to Z</option><option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: low to high</option><option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: high to low</option></select></div>
        <button class="button" type="submit">Apply</button>
    </form>
    <?php if ($errors): ?><div class="notice mt-6" role="alert"><ul class="list-inside list-disc"><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul><a class="text-link mt-3 inline-block" href="/products.php">Reset filters</a></div>
    <?php elseif ($catalogError): ?><div class="notice mt-6" role="alert"><p>We couldn't load the catalog. Please try again shortly.</p><a class="text-link mt-3 inline-block" href="/products.php">Try again</a></div>
    <?php elseif (!$products): ?><section class="notice mt-8" aria-labelledby="empty-heading"><h2 id="empty-heading" class="text-2xl font-semibold">No products found</h2><p class="mt-3">Try a different product name or category.</p><a class="button mt-5 inline-flex" href="/products.php">Clear filters</a></section>
    <?php else: ?><p class="mt-6" id="result-count"><?= count($products) ?> <?= count($products) === 1 ? 'product' : 'products' ?> found</p><div class="product-grid mt-5"><?php foreach ($products as $product) { require dirname(__DIR__) . '/app/views/product-card.php'; } ?></div><?php endif; ?>
</main>
<?php require dirname(__DIR__) . '/app/views/footer.php'; ?>
