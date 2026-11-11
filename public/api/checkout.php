<?php
declare(strict_types=1);
require dirname(__DIR__, 2) . '/app/bootstrap.php';
require dirname(__DIR__, 2) . '/app/catalog.php';
require dirname(__DIR__, 2) . '/app/checkout.php';
header('Content-Type: application/json; charset=UTF-8');

function checkout_response(array $data, int $status = 200): never
{
    http_response_code($status);
    // Keep the session locked until the reviewed state or committed-order access is persisted.
    session_write_close();
    echo json_encode($data, JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function checkout_success(array $order): never
{
    $_SESSION['order_access'][$order['order_number']] = $order['idempotency_key'];
    if (($_SESSION['checkout']['key'] ?? null) === $order['idempotency_key']) {
        $_SESSION['checkout']['completed'] = true;
    }
    checkout_response([
        'saved' => true, 'order_number' => $order['order_number'], 'checkout_key' => $order['idempotency_key'],
        'items' => array_map(fn ($item) => ['id' => $item['id'], 'quantity' => $item['quantity']], $order['items']),
        'confirmation_url' => '/confirmation.php?order=' . rawurlencode($order['order_number']),
    ]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    checkout_response(['error' => 'Use POST for checkout.'], 405);
}
if (!valid_csrf_token($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
    checkout_response(['error' => 'Your session changed. Refresh this page and review again. Your cart is unchanged.'], 419);
}
try {
    $raw = file_get_contents('php://input', false, null, 0, 40001);
    if (strlen($raw) > 40000) {
        checkout_response(['error' => 'The checkout request is too large. Shorten the form and try again.'], 413);
    }
    $request = json_decode($raw, true, 32, JSON_THROW_ON_ERROR);
    if (!is_array($request) || !in_array($request['action'] ?? null, ['review', 'save'], true)) {
        checkout_response(['error' => 'Choose a valid checkout action.'], 400);
    }
    $key = $request['action'] === 'save' ? ($request['checkout_key'] ?? null) : ($request['retry_key'] ?? null);
    if ($key !== null) {
        if (!is_string($key) || (!hash_equals($_SESSION['checkout']['key'] ?? '', $key) && !in_array($key, $_SESSION['order_access'] ?? [], true))) {
            checkout_response(['error' => 'This checkout session expired. Review your cart again to continue.', 'needs_review' => true], 419);
        }
        if ($existing = saved_order(db(), $key)) {
            checkout_success($existing);
        }
    }
    $cart = checkout_cart($request['items'] ?? null);
    if (!$cart) {
        checkout_response(['error' => 'Your cart is empty. Add a product before checkout.'], 422);
    }
    if ($request['action'] === 'review') {
        // Recover a committed save whose response was lost before starting another checkout.
        $previous = $_SESSION['checkout'] ?? null;
        if ($previous && !($previous['completed'] ?? false) && ($existing = saved_order(db(), $previous['key']))) {
            checkout_success($existing);
        }
        $quote = quote_cart($cart);
        $sameCart = $previous && !($previous['completed'] ?? false) && $previous['items'] === $cart;
        $key = $sameCart ? $previous['key'] : bin2hex(random_bytes(32));
        $_SESSION['checkout'] = ['key' => $key, 'items' => $cart, 'quote' => $quote, 'reviewed' => $quote['valid'], 'completed' => false];
        checkout_response(['quote' => $quote, 'checkout_key' => $key], $quote['valid'] ? 200 : 422);
    }
    $draft = $_SESSION['checkout'] ?? null;
    if (!$key || !$draft || !hash_equals($draft['key'], $key) || $draft['items'] !== $cart || !$draft['reviewed']) {
        checkout_response(['error' => 'Review the current cart before placing this order.', 'needs_review' => true], 409);
    }
    $customer = validate_customer($request['customer'] ?? null);
    if ($customer['errors']) {
        checkout_response(['error' => 'Please correct the highlighted customer details.', 'errors' => $customer['errors']], 422);
    }
    $result = place_order(db(), $draft, $customer['values']);
    if (isset($result['order'])) {
        checkout_success($result['order']);
    }
    $_SESSION['checkout']['reviewed'] = false;
    checkout_response(['error' => 'Products changed since your review. Check the changes, then review again before placing the order.', 'needs_review' => true] + $result, 409);
} catch (JsonException | InvalidArgumentException) {
    checkout_response(['error' => 'Use valid product IDs and positive whole-number quantities. Your cart is unchanged.'], 422);
} catch (Throwable) {
    checkout_response(['error' => 'We could not finish saving your order. Your cart is unchanged. Retry this checkout to check for an existing save before trying again.'], 503);
}
