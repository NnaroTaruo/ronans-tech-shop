<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';

// This helper can create/drop only a randomly named checkout-test database, never the shop DB.
$config = database_config();
if (PHP_SAPI !== 'cli' || !preg_match('/\Aronan_tech_shop_test_[a-f0-9]{12}\z/', getenv('TECH_SHOP_TEST_DATABASE') ?: '')
    || $config['name'] !== getenv('TECH_SHOP_TEST_DATABASE')) {
    throw new RuntimeException('An isolated test database environment is required.');
}
$action = $argv[1] ?? '';
if ($action === 'create' || $action === 'drop') {
    $server = new PDO(sprintf('mysql:host=%s;port=%d;charset=utf8mb4', $config['host'], $config['port']), $config['user'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $server->exec($action === 'create'
        ? 'CREATE DATABASE `' . $config['name'] . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
        : 'DROP DATABASE `' . $config['name'] . '`');
    echo $config['name'];
    exit;
}
$pdo = db();
if ($action === 'state') {
    echo json_encode([
        'products' => $pdo->query('SELECT id, name, price_centavos, stock, is_active FROM products ORDER BY id')->fetchAll(),
        'orders' => $pdo->query('SELECT id, order_number, customer_name, customer_email, customer_contact, notes, total_centavos, status, stock_restored_at FROM orders ORDER BY id')->fetchAll(),
        'categories' => $pdo->query('SELECT * FROM categories ORDER BY id')->fetchAll(),
        'items' => $pdo->query('SELECT * FROM order_items ORDER BY id')->fetchAll(),
    ], JSON_THROW_ON_ERROR);
} elseif (in_array($action, ['price_centavos', 'stock', 'is_active', 'name'], true)) {
    $id = filter_var($argv[2] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if (!$id) throw new RuntimeException('Expected a product ID.');
    $value = $argv[3] ?? null;
    if ($action !== 'name') {
        $value = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 999999999]]);
        if ($value === false) throw new RuntimeException('Expected a bounded integer.');
    }
    $pdo->prepare("UPDATE products SET $action = ? WHERE id = ?")->execute([$value, $id]);
} elseif ($action === 'fail-item') {
    $id = (int) ($argv[2] ?? 0);
    if ($id < 1) throw new RuntimeException('Expected a product ID.');
    $pdo->exec("CREATE TRIGGER test_fail_item BEFORE INSERT ON order_items FOR EACH ROW BEGIN IF NEW.product_id = $id THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Intentional isolated test failure'; END IF; END");
} elseif ($action === 'allow-items') {
    $pdo->exec('DROP TRIGGER test_fail_item');
} elseif ($action === 'delay') {
    $pdo->exec('CREATE TRIGGER test_slow_order BEFORE INSERT ON orders FOR EACH ROW DO SLEEP(0.5)');
} elseif ($action === 'no-delay') {
    $pdo->exec('DROP TRIGGER test_slow_order');
} elseif ($action === 'cancel-delay') {
    $pdo->exec("CREATE TRIGGER test_slow_cancel BEFORE UPDATE ON orders FOR EACH ROW BEGIN IF NEW.status='Cancelled' THEN DO SLEEP(0.5); END IF; END");
} elseif ($action === 'cancel-no-delay') {
    $pdo->exec('DROP TRIGGER test_slow_cancel');
} elseif ($action === 'restore-fail') {
    $id = (int) ($argv[2] ?? 0);
    if ($id < 1) throw new RuntimeException('Expected a product ID.');
    $pdo->exec("CREATE TRIGGER test_fail_restore BEFORE UPDATE ON products FOR EACH ROW BEGIN IF NEW.id=$id AND NEW.stock>OLD.stock THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Intentional restore failure'; END IF; END");
} elseif ($action === 'restore-allow') {
    $pdo->exec('DROP TRIGGER test_fail_restore');
} elseif ($action === 'summary') {
    require dirname(__DIR__) . '/app/admin.php';
    echo json_encode(['metrics'=>admin_metrics($pdo),'customers'=>admin_customers($pdo)],JSON_THROW_ON_ERROR);
} else {
    throw new RuntimeException('Unknown fixture action.');
}
