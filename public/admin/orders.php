<?php
declare(strict_types=1);
require dirname(__DIR__,2).'/app/admin.php';
$admin=require_admin(); $adminSection='orders'; $pageTitle='Orders';
try {
    $status=$_GET['status']??'';
    if(!is_string($status)||($status!==''&&!array_key_exists($status,ORDER_TRANSITIONS))) throw new InvalidArgumentException('Choose a valid order status.');
    $query=db()->prepare('SELECT * FROM orders'.($status!==''?' WHERE status=?':'').' ORDER BY created_at DESC,id DESC');
    $query->execute($status!==''?[$status]:[]); $orders=$query->fetchAll();
} catch(InvalidArgumentException $error) { admin_abort(400,$error->getMessage()); }
catch(Throwable $error) { admin_abort(503,'Orders could not load. Check the database and refresh.'); }
require dirname(__DIR__,2).'/app/views/admin-header.php';
?>
<h1>Orders</h1>
<form method="get" class="mt-6 flex flex-wrap items-end gap-4"><div><label for="status" class="block mb-2">Order status</label><select class="field" name="status" id="status"><option value="">All statuses</option><?php foreach(ORDER_TRANSITIONS as $value=>$unused): ?><option <?= $status===$value?'selected':'' ?>><?= e($value) ?></option><?php endforeach; ?></select></div><button type="submit" class="button button-secondary inline-flex">Apply</button></form>
<p class="my-5"><?= count($orders) ?> <?= count($orders) === 1 ? 'order' : 'orders' ?>. Newest first; dates use Philippine time.</p>
<table class="admin-table"><caption class="sr-only">Saved orders</caption><thead><tr><th>Order / customer</th><th>Date and time</th><th class="num">Total</th><th>Payment</th><th>Status</th><th>Action</th></tr></thead><tbody>
<?php foreach($orders as $order): ?><tr><td data-label="Order / customer"><div><span class="block break-all"><?= e($order['order_number']) ?></span><span class="block text-sm"><?= e($order['customer_name']) ?></span></div></td><td data-label="Date"><?= e(admin_date($order['created_at'])) ?></td><td data-label="Total" class="num"><?= money((int)$order['total_centavos']) ?></td><td data-label="Payment"><?= e(PAYMENT_METHODS[$order['payment_method']]) ?></td><td data-label="Status"><?= e($order['status']) ?></td><td data-label="Action"><a class="nav-link text-link" href="/admin/order.php?id=<?= (int)$order['id'] ?>">Details<span class="sr-only"> <?= e($order['order_number']) ?></span></a></td></tr><?php endforeach; ?>
<?php if(!$orders): ?><tr><td colspan="6">No orders match this status. Saved checkouts appear here.</td></tr><?php endif; ?></tbody></table>
<?php require dirname(__DIR__,2).'/app/views/admin-footer.php'; ?>
