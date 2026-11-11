<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require dirname(__DIR__) . '/app/bootstrap.php';

$reset = in_array('--reset-demo-data', $argv, true);
$allowed = ['--reset-demo-data', '--confirm=ronan_tech_shop_dev'];
foreach (array_slice($argv, 1) as $argument) {
    if (!in_array($argument, $allowed, true)) {
        fwrite(STDERR, "Unknown argument. See README.md for setup/reset commands.\n");
        exit(1);
    }
}

try {
    // A reset is deliberately limited to this one development database.
    if ($reset && (database_config()['name'] !== 'ronan_tech_shop_dev'
        || !in_array('--confirm=ronan_tech_shop_dev', $argv, true))) {
        throw new RuntimeException('Reset requires the ronan_tech_shop_dev database and --confirm=ronan_tech_shop_dev.');
    }
    $pdo = db();
    // MySQL schema changes commit implicitly: create tables BEFORE the seed transaction.
    $pdo->exec(file_get_contents(dirname(__DIR__) . '/database/schema.sql'));
    $tables = ['order_items', 'orders', 'products', 'categories', 'admins'];
    $hasData = false;
    foreach ($tables as $table) {
        $hasData = $hasData || (int) $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn() > 0;
    }
    if ($hasData && !$reset) {
        echo "Existing data preserved. No seed rows were changed. Use the documented reset only if you want to erase this shop's demo data.\n";
        exit(0);
    }

    // A transaction saves all these rows together, or rolls them all back on failure.
    $pdo->beginTransaction();
    if ($reset) {
        foreach ($tables as $table) {
            $pdo->exec("DELETE FROM $table");
        }
    }
    $categoryIds = [];
    $insertCategory = $pdo->prepare('INSERT INTO categories (name) VALUES (?)');
    foreach (['Keyboards', 'Mice', 'Accessories'] as $name) {
        $insertCategory->execute([$name]);
        $categoryIds[$name] = (int) $pdo->lastInsertId();
    }
    $insertProduct = $pdo->prepare('INSERT INTO products (category_id, name, price_centavos, stock, is_active, is_featured, description, image_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    foreach (require dirname(__DIR__) . '/database/seed.php' as $row) {
        [$category, $name, $price, $stock, $active, $featured, $description, $image] = $row;
        $insertProduct->execute([$categoryIds[$category], $name, price_to_centavos($price), $stock, $active, $featured, $description, '/assets/images/' . $image]);
    }
    $pdo->prepare('INSERT INTO admins (email, password_hash) VALUES (?, ?)')
        ->execute(['admin@ronan-tech.test', password_hash('DemoOnly!2026', PASSWORD_DEFAULT)]);
    $pdo->commit();
    echo "Seed saved: 3 categories, 12 fictional products, 1 demo admin, 0 orders.\n";
    echo "Login will be built in Stage 4. See README.md for demo-only credentials.\n";
} catch (Throwable $error) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // Do not print a DSN, password, customer data, or a raw database exception.
    fwrite(STDERR, 'Setup failed. ' . ($error instanceof PDOException
        ? 'Check that MariaDB is running, the database exists, and config/local.php is correct.'
        : $error->getMessage()) . "\n");
    exit(1);
}
