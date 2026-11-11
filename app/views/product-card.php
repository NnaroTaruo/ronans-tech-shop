<?php declare(strict_types=1); ?>
<article class="product-card" data-product-id="<?= $product['id'] ?>" data-price-centavos="<?= $product['price_centavos'] ?>">
    <a href="/product.php?id=<?= $product['id'] ?>" aria-label="View <?= e($product['name']) ?>">
        <img class="aspect-[4/3] w-full object-cover" src="<?= e(product_image($product['image_path'])) ?>" alt="<?= e($product['category_name']) ?> demo illustration" width="640" height="480" loading="lazy">
    </a>
    <div class="flex flex-1 flex-col p-5">
        <p class="text-sm"><?= e($product['category_name']) ?></p>
        <h3 class="mt-2 text-lg font-semibold"><a class="text-link" href="/product.php?id=<?= $product['id'] ?>"><?= e($product['name']) ?></a></h3>
        <p class="mt-3 text-xl font-semibold tabular-nums"><?= money($product['price_centavos']) ?></p>
        <p class="mt-2 text-sm"><?= $product['stock'] > 0 ? $product['stock'] . ' in stock' : 'Out of Stock' ?></p>
        <div class="mt-auto pt-5">
            <a class="text-link inline-flex min-h-11 items-center" href="/product.php?id=<?= $product['id'] ?>">View details</a>
            <form class="add-to-cart-form mt-2" data-product-name="<?= e($product['name']) ?>">
                <input type="hidden" name="id" value="<?= $product['id'] ?>">
                <input type="hidden" name="quantity" value="1">
                <button class="button w-full" type="submit" disabled <?= $product['stock'] > 0 ? 'data-enable-cart' : '' ?>><?= $product['stock'] > 0 ? 'Add to cart' : 'Out of Stock' ?></button>
            </form>
        </div>
    </div>
</article>
