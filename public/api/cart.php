<?php
declare(strict_types=1);
require dirname(__DIR__, 2) . '/app/bootstrap.php';
require dirname(__DIR__, 2) . '/app/catalog.php';
header('Content-Type: application/json; charset=UTF-8');

function cart_response(array $data, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($data, JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    cart_response(['error' => 'Use POST to check cart items.'], 405);
}
if (!valid_csrf_token($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
    cart_response(['error' => 'Your session changed. Refresh the page and try again. Your saved cart is unchanged.'], 419);
}
session_write_close();
try {
    $raw = file_get_contents('php://input', false, null, 0, 20001);
    if (strlen($raw) > 20000) {
        cart_response(['error' => 'The cart request is too large. Remove some items and try again.'], 413);
    }
    $request = json_decode($raw, true, 32, JSON_THROW_ON_ERROR);
    cart_response(quote_cart($request['items'] ?? null));
} catch (JsonException | InvalidArgumentException) {
    cart_response(['error' => 'Use valid product IDs and positive whole-number quantities. Your saved cart is unchanged.'], 422);
} catch (Throwable) {
    cart_response(['error' => 'We could not load current prices and stock. Try again shortly. Your saved cart is unchanged.'], 503);
}
