<?php
declare(strict_types=1);

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function price_to_centavos(string $input): int
{
    // Parse the digits directly. A float such as 85.50 must never enter money calculations.
    if (!preg_match('/\A(0|[1-9][0-9]{0,6})(?:\.([0-9]{1,2}))?\z/', trim($input), $parts)) {
        throw new InvalidArgumentException('Use a price from 0 to 9999999.99 with at most two decimal places.');
    }
    return (int) $parts[1] * 100 + (int) str_pad($parts[2] ?? '', 2, '0');
}

function money(int $centavos): string
{
    if ($centavos < 0) {
        throw new InvalidArgumentException('Money cannot be negative.');
    }
    $pesos = preg_replace('/\B(?=(\d{3})+(?!\d))/', ',', (string) intdiv($centavos, 100));
    return 'PHP ' . $pesos . '.' . str_pad((string) ($centavos % 100), 2, '0', STR_PAD_LEFT);
}

function csrf_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        throw new LogicException('Start the session before creating a CSRF token.');
    }
    return $_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));
}

function valid_csrf_token(mixed $submitted): bool
{
    return is_string($submitted) && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $submitted);
}
