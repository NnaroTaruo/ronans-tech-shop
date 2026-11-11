<?php
declare(strict_types=1);

const PAYMENT_METHODS = ['cod' => 'Cash on Delivery', 'ewallet' => 'E-Wallet', 'bank_transfer' => 'Bank Transfer'];

function checkout_cart(mixed $items): array
{
    $cart = normalize_cart_items($items);
    usort($cart, fn ($a, $b) => $a['id'] <=> $b['id']);
    return $cart;
}

function validate_customer(mixed $input): array
{
    $values = [];
    $errors = [];
    foreach (['name' => 120, 'email' => 254, 'contact' => 40, 'address' => 2000, 'payment_method' => 20, 'notes' => 2000] as $field => $limit) {
        $raw = is_array($input) ? ($input[$field] ?? '') : '';
        $values[$field] = is_string($raw) ? preg_replace('/\A[\s\p{Z}]+|[\s\p{Z}]+\z/u', '', $raw) : '';
        if (!is_string($raw) || mb_strlen($values[$field]) > $limit || str_contains($values[$field], "\0")) {
            $errors[$field] = 'Use text of ' . $limit . ' characters or fewer.';
        } elseif ($field !== 'notes' && $values[$field] === '') {
            $errors[$field] = 'This field is required.';
        }
    }
    $values['email'] = strtolower($values['email']);
    if (!isset($errors['email']) && !filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Enter a valid email address.';
    }
    $digits = preg_replace('/[^0-9]/', '', $values['contact']);
    if (!isset($errors['contact']) && (!preg_match('/\A\+?[0-9() .-]+\z/', $values['contact']) || strlen($digits) < 7 || strlen($digits) > 15)) {
        $errors['contact'] = 'Enter 7 to 15 digits. Spaces, +, parentheses, dots and hyphens are allowed.';
    }
    if (!array_key_exists($values['payment_method'], PAYMENT_METHODS)) {
        $errors['payment_method'] = 'Choose Cash on Delivery, E-Wallet, or Bank Transfer.';
    }
    return ['values' => $values, 'errors' => $errors];
}

function saved_order(PDO $pdo, string $key): ?array
{
    $query = $pdo->prepare('SELECT * FROM orders WHERE idempotency_key = ?');
    $query->execute([$key]);
    $order = $query->fetch();
    if (!$order) {
        return null;
    }
    $query = $pdo->prepare('SELECT product_id AS id, product_name, unit_price_centavos, quantity FROM order_items WHERE order_id = ? ORDER BY product_id');
    $query->execute([$order['id']]);
    $order['items'] = $query->fetchAll();
    foreach ($order['items'] as &$item) {
        foreach (['id', 'unit_price_centavos', 'quantity'] as $field) {
            $item[$field] = (int) $item[$field];
        }
    }
    unset($item);
    return $order;
}

function checkout_changes(array $previous, array $current): array
{
    $old = array_column($previous['items'], null, 'id');
    $changes = [];
    foreach ($current['items'] as $item) {
        $before = $old[$item['id']];
        if ($before['price_centavos'] !== $item['price_centavos']) {
            $changes[] = $item['price_centavos'] === null
                ? $before['name'] . ' is no longer available.'
                : $item['name'] . ': price changed from ' . money($before['price_centavos']) . ' to ' . money($item['price_centavos']) . '.';
        }
        if ($before['stock'] !== $item['stock']) {
            $changes[] = $before['name'] . ': available stock changed from ' . $before['stock'] . ' to ' . $item['stock'] . '.';
        }
        if ($before['name'] !== $item['name'] && $item['price_centavos'] !== null) {
            $changes[] = 'Product name changed from ' . $before['name'] . ' to ' . $item['name'] . '.';
        }
        if ($item['issue'] !== '') {
            $changes[] = $before['name'] . ': ' . $item['issue'];
        }
    }
    return $changes;
}

function place_order(PDO $pdo, array $draft, array $customer): array
{
    // A retry after a lost response must return the committed order, not create a second one.
    if ($existing = saved_order($pdo, $draft['key'])) {
        return ['order' => $existing];
    }
    try {
        $pdo->beginTransaction();
        $locked = [];
        $read = $pdo->prepare('SELECT * FROM products WHERE id = ? FOR UPDATE');
        // Point reads in sorted ID order keep every checkout's product-lock order consistent.
        foreach ($draft['items'] as $item) {
            $read->execute([$item['id']]);
            $product = product_rows($read)[0] ?? null;
            if ($product) {
                $locked[$product['id']] = $product;
            }
        }
        if ($existing = saved_order($pdo, $draft['key'])) {
            $pdo->rollBack();
            return ['order' => $existing];
        }
        $quote = quote_from_products($draft['items'], $locked);
        $changes = checkout_changes($draft['quote'], $quote);
        if ($changes || !$quote['valid']) {
            $pdo->rollBack();
            return ['quote' => $quote, 'changes' => $changes];
        }
        $number = 'RTS-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(8)));
        $pdo->prepare('INSERT INTO orders (order_number, idempotency_key, customer_name, customer_email, customer_contact, delivery_address, payment_method, notes, subtotal_centavos, total_centavos) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')
            ->execute([$number, $draft['key'], $customer['name'], $customer['email'], $customer['contact'], $customer['address'], $customer['payment_method'], $customer['notes'] === '' ? null : $customer['notes'], $quote['total_centavos'], $quote['total_centavos']]);
        $orderId = (int) $pdo->lastInsertId();
        $insertItem = $pdo->prepare('INSERT INTO order_items (order_id, product_id, product_name, unit_price_centavos, quantity) VALUES (?, ?, ?, ?, ?)');
        $reduceStock = $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ? AND is_active = 1 AND stock >= ?');
        foreach ($quote['items'] as $item) {
            $insertItem->execute([$orderId, $item['id'], $item['name'], $item['price_centavos'], $item['quantity']]);
            $reduceStock->execute([$item['quantity'], $item['id'], $item['quantity']]);
            if ($reduceStock->rowCount() !== 1) {
                throw new RuntimeException('Stock changed while saving.');
            }
        }
        $pdo->commit();
        return ['order' => saved_order($pdo, $draft['key'])];
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        if ($error instanceof PDOException && (int) ($error->errorInfo[1] ?? 0) === 1062 && ($existing = saved_order($pdo, $draft['key']))) {
            return ['order' => $existing];
        }
        throw $error;
    }
}
