<?php
declare(strict_types=1);

const PRODUCT_SELECT = 'SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON c.id = p.category_id';
const MAX_CART_CENTAVOS = 9007199254740991; // Largest exact integer shared by PHP and JavaScript.

function categories(): array
{
    return db()->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
}

function product_rows(PDOStatement $query): array
{
    return array_map(function (array $row): array {
        foreach (['id', 'category_id', 'price_centavos', 'stock', 'is_active', 'is_featured'] as $field) {
            $row[$field] = (int) $row[$field];
        }
        return $row;
    }, $query->fetchAll());
}

function list_products(string $search = '', ?int $category = null, string $sort = 'name', bool $featured = false): array
{
    // Only these SQL fragments may control ordering; never interpolate a request value.
    $sorts = ['name' => 'p.name ASC, p.id ASC', 'price_asc' => 'p.price_centavos ASC, p.name ASC', 'price_desc' => 'p.price_centavos DESC, p.name ASC'];
    $sql = PRODUCT_SELECT . ' WHERE p.is_active = 1';
    $values = [];
    if ($search !== '') {
        $sql .= " AND p.name LIKE ? ESCAPE '='";
        $values[] = '%' . strtr($search, ['=' => '==', '%' => '=%', '_' => '=_']) . '%';
    }
    if ($category !== null) {
        $sql .= ' AND p.category_id = ?';
        $values[] = $category;
    }
    if ($featured) {
        $sql .= ' AND p.is_featured = 1';
    }
    $query = db()->prepare($sql . ' ORDER BY ' . ($sorts[$sort] ?? $sorts['name']) . ($featured ? ' LIMIT 4' : ''));
    $query->execute($values);
    return product_rows($query);
}

function active_product(int $id): ?array
{
    $query = db()->prepare(PRODUCT_SELECT . ' WHERE p.id = ? AND p.is_active = 1');
    $query->execute([$id]);
    return product_rows($query)[0] ?? null;
}

function related_products(array $product): array
{
    $query = db()->prepare(PRODUCT_SELECT . ' WHERE p.category_id = ? AND p.id <> ? AND p.is_active = 1 ORDER BY p.name, p.id LIMIT 3');
    $query->execute([$product['category_id'], $product['id']]);
    return product_rows($query);
}

function valid_product_image(string $path): bool
{
    if (preg_match('~\A/assets/images/[a-zA-Z0-9_-]+\.(svg|png|jpg|jpeg|webp)\z~', $path)) {
        return is_file(dirname(__DIR__) . '/public' . $path);
    }
    $parts = parse_url($path);
    return filter_var($path, FILTER_VALIDATE_URL) !== false && is_array($parts)
        && ($parts['scheme'] ?? '') === 'https' && !isset($parts['user']) && !isset($parts['pass']);
}

function product_image(string $path): string
{
    return valid_product_image($path) ? $path : '/assets/images/accessories.svg';
}

function normalize_cart_items(mixed $items): array
{
    if (!is_array($items) || !array_is_list($items) || count($items) > 100) {
        throw new InvalidArgumentException('A cart must contain a list of at most 100 product entries.');
    }
    $merged = [];
    foreach ($items as $item) {
        if (!is_array($item) || !isset($item['id'], $item['quantity'])
            || !is_int($item['id']) || $item['id'] < 1 || $item['id'] > 4294967295
            || !is_int($item['quantity']) || $item['quantity'] < 1 || $item['quantity'] > 4294967295) {
            throw new InvalidArgumentException('Product IDs and quantities must be positive whole numbers.');
        }
        $id = $item['id'];
        $merged[$id] = ($merged[$id] ?? 0) + $item['quantity'];
        if ($merged[$id] > 4294967295) {
            throw new InvalidArgumentException('The combined quantity is too large.');
        }
    }
    $result = [];
    foreach ($merged as $id => $quantity) {
        $result[] = ['id' => $id, 'quantity' => $quantity];
    }
    return $result;
}

function quote_cart(mixed $submitted): array
{
    $cart = normalize_cart_items($submitted);
    $products = [];
    if ($cart) {
        $ids = array_column($cart, 'id');
        $query = db()->prepare(PRODUCT_SELECT . ' WHERE p.is_active = 1 AND p.id IN (' . implode(',', array_fill(0, count($ids), '?')) . ')');
        $query->execute($ids);
        foreach (product_rows($query) as $product) {
            $products[$product['id']] = $product;
        }
    }
    return quote_from_products($cart, $products);
}

function quote_from_products(array $cart, array $products): array
{
    $rows = [];
    $total = 0;
    $valid = count($cart) > 0;
    foreach ($cart as $item) {
        $product = $products[$item['id']] ?? null;
        if ($product !== null && !(int) $product['is_active']) {
            $product = null;
        }
        $issue = '';
        $lineTotal = null;
        if ($product === null) {
            $issue = 'This product is no longer available. Remove it to continue.';
        } elseif ($product['stock'] === 0) {
            $issue = 'Out of stock. Remove this product or check again later.';
        } elseif ($item['quantity'] > $product['stock']) {
            $issue = 'Only ' . $product['stock'] . ' available. Reduce the quantity.';
        } elseif ($item['quantity'] > intdiv(MAX_CART_CENTAVOS - $total, $product['price_centavos'])) {
            $issue = 'The cart total is too large. Reduce quantities.';
        } else {
            $lineTotal = $product['price_centavos'] * $item['quantity'];
            $total += $lineTotal;
        }
        $valid = $valid && $issue === '';
        $rows[] = [
            'id' => $item['id'], 'quantity' => $item['quantity'],
            'name' => $product['name'] ?? 'Unavailable product',
            'category_name' => $product['category_name'] ?? '',
            'image_path' => $product ? product_image($product['image_path']) : null,
            'price_centavos' => $product['price_centavos'] ?? null,
            'stock' => $product['stock'] ?? 0,
            'line_total_centavos' => $lineTotal, 'issue' => $issue,
        ];
    }
    // An invalid line blocks the full total; do not present a misleading partial subtotal.
    return ['items' => $rows, 'valid' => $valid, 'subtotal_centavos' => $valid || !$cart ? $total : null, 'total_centavos' => $valid || !$cart ? $total : null];
}
