<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';

$sessionDirectory = dirname(__DIR__) . '/tmp/sessions';
if (!is_dir($sessionDirectory)) {
    mkdir($sessionDirectory, 0700, true);
}
session_save_path($sessionDirectory);
session_start();

function check(bool $condition, string $label): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: $label\n");
        exit(1);
    }
    echo "PASS: $label\n";
}

check(money(2 * price_to_centavos('120.00') + price_to_centavos('85.50')) === 'PHP 325.50', 'assessment money example');
check(price_to_centavos('85.5') === 8550 && price_to_centavos('0.01') === 1, 'decimal digits preserve centavos');
check(money(123456789) === 'PHP 1,234,567.89', 'peso grouping and two decimal places');
foreach (['1.005', '-1', '1e3', '', '1,200', '10000000', 'NaN'] as $invalid) {
    try {
        price_to_centavos($invalid);
        check(false, 'reject invalid money input');
    } catch (InvalidArgumentException) {
        // Expected: never silently round or reinterpret invalid input.
    }
}
echo "PASS: invalid money strings rejected\n";
check(e('<script>"&') === '&lt;script&gt;&quot;&amp;', 'HTML is escaped');
$token = csrf_token();
check(strlen($token) === 64 && csrf_token() === $token && valid_csrf_token($token), 'stable session CSRF token');
check(!valid_csrf_token('wrong') && !valid_csrf_token(null) && !valid_csrf_token([]), 'invalid CSRF tokens rejected');
session_destroy();
echo "Foundation checks finished. This does not verify cart, checkout, or admin features.\n";
