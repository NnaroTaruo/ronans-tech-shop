<?php
declare(strict_types=1);
require dirname(__DIR__, 2) . '/app/admin.php';
$admin = require_admin(); $adminSection = 'index'; $pageTitle = 'Dashboard';
try {
    $metrics = admin_metrics(db());
    $recent = db()->query('SELECT * FROM orders ORDER BY created_at DESC,id DESC LIMIT 5')->fetchAll();
    $low = db()->query('SELECT id,name,stock FROM products WHERE is_active=1 AND stock<=5 ORDER BY stock,name LIMIT 5')->fetchAll();
} catch (Throwable $error) { admin_abort(503, 'Dashboard totals could not load. Check the database and refresh.'); }
require dirname(__DIR__, 2) . '/app/views/admin-header.php';
?>
<div class="admin-title"><h1>Dashboard</h1><a class="button inline-flex" href="/admin/orders.php?status=Pending">Review pending orders</a></div>
<p class="mt-3">All-time demo activity. Sales count Completed orders only.</p>
<dl class="admin-metrics mt-6">
<?php foreach (['products'=>'Total Products','orders'=>'Total Orders','pending'=>'Pending Orders','completed'=>'Completed Orders','customers'=>'Total Customers','sales'=>'Total Sales'] as $key=>$label): ?>
<div><dt><?= e($label) ?></dt><dd class="tabular-nums" data-metric="<?= $key ?>"><?= $key === 'sales' ? money((int)$metrics[$key]) : (int)$metrics[$key] ?></dd></div>
<?php endforeach; ?>
</dl>
<div class="admin-columns mt-8">
<section class="admin-panel"><div class="admin-title"><h2>Recent orders</h2><a class="nav-link text-link" href="/admin/orders.php">View all</a></div>
<?php if (!$recent): ?><p class="mt-5">No orders yet. A saved checkout will appear here.</p><?php endif; ?>
<?php foreach ($recent as $order): ?><div class="admin-preview"><div><a class="text-link break-all" href="/admin/order.php?id=<?= (int)$order['id'] ?>"><?= e($order['order_number']) ?></a><p><?= e($order['customer_name']) ?> · <?= e($order['status']) ?></p></div><p class="tabular-nums"><?= money((int)$order['total_centavos']) ?></p></div><?php endforeach; ?>
</section>
<section class="admin-panel"><h2>Low stock</h2><p class="mt-3 text-sm">Active products with 5 units or fewer.</p>
<?php if (!$low): ?><p class="mt-5">No active products need stock attention.</p><?php endif; ?>
<?php foreach ($low as $product): ?><div class="admin-preview"><a class="text-link" href="/admin/product.php?id=<?= (int)$product['id'] ?>"><?= e($product['name']) ?></a><span class="tabular-nums"><?= (int)$product['stock'] ?> left</span></div><?php endforeach; ?>
</section></div>
<?php require dirname(__DIR__, 2) . '/app/views/admin-footer.php'; ?>
