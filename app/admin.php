<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/catalog.php';
require_once __DIR__ . '/checkout.php';

const ORDER_TRANSITIONS = [
    'Pending' => ['Confirmed', 'Cancelled'], 'Confirmed' => ['Preparing', 'Cancelled'],
    'Preparing' => ['Shipped'], 'Shipped' => ['Completed'], 'Completed' => [], 'Cancelled' => [],
];

function admin_redirect(string $path): never
{
    header('Location: ' . $path, true, 303);
    exit;
}

function admin_user(): ?array
{
    if (!isset($_SESSION['admin_id'])) return null;
    $query = db()->prepare('SELECT id, email FROM admins WHERE id = ?');
    $query->execute([$_SESSION['admin_id']]);
    return $query->fetch() ?: null;
}

function require_admin(): array
{
    try { $admin = admin_user(); }
    catch (Throwable $error) { admin_abort(503, 'Admin access is unavailable. Check the database and try again.'); }
    if (!$admin) {
        unset($_SESSION['admin_id']);
        admin_redirect('/admin/login.php');
    }
    return $admin;
}

function admin_abort(int $status, string $message): never
{
    http_response_code($status);
    $pageTitle = 'Admin request';
    require __DIR__ . '/views/admin-header.php';
    echo '<h1>Admin request</h1><p class="notice mt-6">' . e($message) . '</p><p class="mt-6"><a class="text-link" href="/admin/">Return to dashboard</a></p>';
    require __DIR__ . '/views/admin-footer.php';
    exit;
}

function admin_post_guard(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') admin_abort(405, 'Use the form to submit this request.');
    if (!valid_csrf_token($_POST['csrf_token'] ?? null)) admin_abort(419, 'This form expired. Reload the page and try again.');
}

function admin_flash(string $message, string $path): never
{
    $_SESSION['admin_flash'] = $message;
    admin_redirect($path);
}

function admin_id(mixed $value): int
{
    if (!is_string($value) || !preg_match('/\A[1-9][0-9]{0,9}\z/', $value) || (int) $value > 4294967295) {
        throw new InvalidArgumentException('Choose a valid record.');
    }
    return (int) $value;
}

function admin_text(mixed $raw, int $limit, bool $required = true): string
{
    if (!is_string($raw) || !mb_check_encoding($raw, 'UTF-8')) throw new InvalidArgumentException('Enter valid text.');
    $value = preg_replace('/\A[\s\p{Z}]+|[\s\p{Z}]+\z/u', '', $raw);
    if (($required && $value === '') || mb_strlen($value) > $limit || str_contains($value, "\0")) {
        throw new InvalidArgumentException('Enter ' . ($required ? '1 to ' : 'at most ') . $limit . ' characters.');
    }
    return $value;
}

function admin_field(string $name, string $label, array $values, array $errors, string $type = 'text', string $help = ''): void
{
    $invalid = isset($errors[$name]);
    echo '<div><label for="' . e($name) . '">' . e($label) . '</label>';
    $attributes = ' id="' . e($name) . '" name="' . e($name) . '" class="field" aria-invalid="' . ($invalid ? 'true' : 'false') . '"' . (($invalid || $help !== '') ? ' aria-describedby="' . e($name) . '-help"' : '');
    if ($type === 'textarea') echo '<textarea' . $attributes . ' rows="5">' . e((string) ($values[$name] ?? '')) . '</textarea>';
    else echo '<input' . $attributes . ' type="' . e($type) . '" value="' . ($type === 'password' ? '' : e((string) ($values[$name] ?? ''))) . '">';
    if ($invalid || $help !== '') echo '<p id="' . e($name) . '-help" class="mt-2 text-sm' . ($invalid ? ' text-danger' : '') . '">' . e($errors[$name] ?? $help) . '</p>';
    echo '</div>';
}

function admin_csrf(): void
{
    echo '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function admin_errors(array $errors): void
{
    if ($errors) echo '<p class="notice mt-6 text-danger" role="alert">' . e($errors['_form'] ?? 'Check the marked fields and try again.') . '</p>';
}

function admin_product(PDO $pdo, int $id, bool $lock = false): ?array
{
    $query = $pdo->prepare('SELECT * FROM products WHERE id = ?' . ($lock ? ' FOR UPDATE' : ''));
    $query->execute([$id]);
    return $query->fetch() ?: null;
}

function product_revision(array $product): string
{
    // Include stock and every editable field: checkout or another editor invalidates a stale form.
    $values = array_map(fn ($key) => (string) $product[$key], ['id', 'category_id', 'name', 'description', 'image_path', 'price_centavos', 'stock', 'is_active', 'is_featured', 'updated_at']);
    return hash_hmac('sha256', json_encode($values, JSON_THROW_ON_ERROR), csrf_token());
}

function validate_product(array $input): array
{
    $values = []; $errors = [];
    foreach (['name' => 120, 'description' => 10000, 'image_path' => 255] as $field => $limit) {
        try { $values[$field] = admin_text($input[$field] ?? '', $limit); }
        catch (InvalidArgumentException $error) { $values[$field] = is_string($input[$field] ?? null) ? $input[$field] : ''; $errors[$field] = $error->getMessage(); }
    }
    try { $values['category_id'] = admin_id($input['category_id'] ?? null); }
    catch (InvalidArgumentException $error) { $values['category_id'] = ''; $errors['category_id'] = 'Choose an existing category.'; }
    $values['price'] = is_string($input['price'] ?? null) ? trim($input['price']) : '';
    try {
        $values['price_centavos'] = price_to_centavos($values['price']);
        if ($values['price_centavos'] < 1) throw new InvalidArgumentException('Enter a positive price.');
    } catch (InvalidArgumentException $error) { $errors['price'] = $error->getMessage(); }
    $values['stock'] = is_string($input['stock'] ?? null) ? $input['stock'] : '';
    if (!preg_match('/\A(0|[1-9][0-9]{0,9})\z/', $values['stock']) || (int) $values['stock'] > 4294967295) $errors['stock'] = 'Enter a whole number from 0 to 4294967295.';
    foreach (['is_active', 'is_featured'] as $field) {
        $values[$field] = $input[$field] ?? '';
        if (!is_string($values[$field]) || !in_array($values[$field], ['0', '1'], true)) { $errors[$field] = 'Choose Yes or No.'; $values[$field] = ''; }
    }
    if (!isset($errors['image_path']) && !valid_product_image($values['image_path'])) $errors['image_path'] = 'Use an existing /assets/images/ file or an HTTPS image URL without a username or password.';
    return ['values' => $values, 'errors' => $errors];
}

function save_admin_product(PDO $pdo, ?int $id, array $values, mixed $revision): void
{
    try {
        $pdo->beginTransaction();
        if ($id !== null) {
            $current = admin_product($pdo, $id, true);
            if (!$current) throw new InvalidArgumentException('This product no longer exists.');
            if (!is_string($revision) || !hash_equals(product_revision($current), $revision)) throw new InvalidArgumentException('This product changed after you opened the form. Reload it and review current stock before saving.');
        }
        $query = $pdo->prepare('SELECT id FROM categories WHERE id = ?');
        $query->execute([$values['category_id']]);
        if (!$query->fetch()) throw new InvalidArgumentException('Choose an existing category.');
        $args = array_map(fn ($key) => $values[$key], ['category_id', 'name', 'description', 'image_path', 'price_centavos', 'stock', 'is_active', 'is_featured']);
        if ($id === null) $pdo->prepare('INSERT INTO products (category_id,name,description,image_path,price_centavos,stock,is_active,is_featured) VALUES (?,?,?,?,?,?,?,?)')->execute($args);
        else { $args[] = $id; $pdo->prepare('UPDATE products SET category_id=?,name=?,description=?,image_path=?,price_centavos=?,stock=?,is_active=?,is_featured=? WHERE id=?')->execute($args); }
        $pdo->commit();
    } catch (Throwable $error) { if ($pdo->inTransaction()) $pdo->rollBack(); throw $error; }
}

function change_order_status(PDO $pdo, int $id, string $expected, string $next): void
{
    try {
        $pdo->beginTransaction();
        $query = $pdo->prepare('SELECT status,stock_restored_at FROM orders WHERE id=? FOR UPDATE');
        $query->execute([$id]); $order = $query->fetch();
        if (!$order || $order['status'] !== $expected || !in_array($next, ORDER_TRANSITIONS[$order['status']] ?? [], true)) {
            throw new InvalidArgumentException('The order changed or this transition is not allowed. Reload and review its current status.');
        }
        if ($next === 'Cancelled') {
            if ($order['stock_restored_at'] !== null) throw new InvalidArgumentException('Stock has already been restored.');
            $query = $pdo->prepare('SELECT product_id,quantity FROM order_items WHERE order_id=? ORDER BY product_id');
            $query->execute([$id]); $items = $query->fetchAll();
            // Match checkout's sorted product-lock order. Restore inactive products too.
            $lock = $pdo->prepare('SELECT stock FROM products WHERE id=? FOR UPDATE');
            $restore = $pdo->prepare('UPDATE products SET stock=stock+? WHERE id=? AND stock<=?');
            foreach ($items as $item) {
                $lock->execute([$item['product_id']]);
                if (!$lock->fetch()) throw new RuntimeException('A saved product is missing.');
                $restore->execute([$item['quantity'], $item['product_id'], 4294967295 - (int) $item['quantity']]);
                if ($restore->rowCount() !== 1) throw new RuntimeException('Stock cannot be restored within its supported range.');
            }
            $pdo->prepare("UPDATE orders SET status='Cancelled',stock_restored_at=UTC_TIMESTAMP() WHERE id=?")->execute([$id]);
        } else $pdo->prepare('UPDATE orders SET status=? WHERE id=?')->execute([$next,$id]);
        $pdo->commit();
    } catch (Throwable $error) { if ($pdo->inTransaction()) $pdo->rollBack(); throw $error; }
}

function admin_metrics(PDO $pdo): array
{
    return $pdo->query("SELECT (SELECT COUNT(*) FROM products) AS products,COUNT(*) AS orders,COALESCE(SUM(status='Pending'),0) AS pending,COALESCE(SUM(status='Completed'),0) AS completed,COUNT(DISTINCT LOWER(TRIM(customer_email))) AS customers,COALESCE(SUM(CASE WHEN status='Completed' THEN total_centavos ELSE 0 END),0) AS sales FROM orders")->fetch();
}

function admin_customers(PDO $pdo): array
{
    return $pdo->query("SELECT grouped.email,latest.customer_name,latest.customer_contact,grouped.order_count,grouped.spending FROM (SELECT LOWER(TRIM(customer_email)) AS email,MAX(id) AS latest_id,COUNT(*) AS order_count,SUM(CASE WHEN status='Completed' THEN total_centavos ELSE 0 END) AS spending FROM orders GROUP BY LOWER(TRIM(customer_email))) grouped JOIN orders latest ON latest.id=grouped.latest_id ORDER BY grouped.email")->fetchAll();
}

function admin_date(string $utc): string
{
    return (new DateTimeImmutable($utc, new DateTimeZone('UTC')))->setTimezone(new DateTimeZone('Asia/Manila'))->format('M j, Y g:i A') . ' PHT';
}
