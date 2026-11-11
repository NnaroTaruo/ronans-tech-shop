<?php
declare(strict_types=1);
require __DIR__ . '/foundation.php';

try {
    $pdo = db();
    check((int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn() >= 10, 'at least ten seed products');
    check((int) $pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn() >= 3, 'at least three categories');
    check((int) $pdo->query('SELECT COUNT(*) FROM products WHERE is_active = 0')->fetchColumn() > 0, 'inactive example exists');
    check((int) $pdo->query('SELECT COUNT(*) FROM products WHERE is_active = 1 AND stock = 0')->fetchColumn() > 0, 'active zero-stock example exists');
    $engines = $pdo->query("SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN ('categories', 'products', 'orders', 'order_items', 'admins')")->fetchAll(PDO::FETCH_COLUMN);
    check(count($engines) === 5 && count(array_filter($engines, fn ($engine) => $engine !== 'InnoDB')) === 0, 'all five tables use InnoDB');
    foreach ($pdo->query('SELECT image_path FROM products') as $product) {
        check(is_file(dirname(__DIR__) . '/public' . $product['image_path']), 'bundled seed image exists');
    }
    $hash = $pdo->query("SELECT password_hash FROM admins WHERE email = 'admin@ronan-tech.test'")->fetchColumn();
    check(is_string($hash) && password_verify('DemoOnly!2026', $hash), 'demo password is stored as a verifiable hash');

    $pdo->beginTransaction();
    $category = $pdo->query('SELECT id, name FROM categories ORDER BY id LIMIT 1')->fetch();
    $duplicateBlocked = false;
    try {
        $pdo->prepare('INSERT INTO categories (name) VALUES (?)')->execute([strtolower($category['name'])]);
    } catch (PDOException) {
        $duplicateBlocked = true;
    }
    check($duplicateBlocked, 'case-insensitive category duplicate blocked');
    $deleteBlocked = false;
    try {
        $pdo->prepare('DELETE FROM categories WHERE id = ?')->execute([$category['id']]);
    } catch (PDOException) {
        $deleteBlocked = true;
    }
    check($deleteBlocked, 'assigned category cannot be deleted');
    $negativeBlocked = false;
    try {
        $pdo->exec('UPDATE products SET stock = -1 WHERE id = (SELECT id FROM (SELECT MIN(id) AS id FROM products) AS first_product)');
    } catch (PDOException) {
        $negativeBlocked = true;
    }
    check($negativeBlocked, 'negative stock rejected by database');
    $pdo->rollBack();
    echo "Database checks finished; test mutations rolled back. Application acceptance checks are still pending.\n";
} catch (Throwable $error) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, "Database checks failed. Check setup and review the failing check above.\n");
    exit(1);
}
