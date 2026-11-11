<?php
declare(strict_types=1);
require dirname(__DIR__,2).'/app/admin.php';
$admin=require_admin(); $adminSection='customers'; $pageTitle='Customers';
try { $customers=admin_customers(db()); }
catch(Throwable $error) { admin_abort(503,'Customer summaries could not load. Check the database and refresh.'); }
require dirname(__DIR__,2).'/app/views/admin-header.php';
?>
<h1>Customers</h1><p class="mt-3">Read-only checkout customers, grouped by normalized email. Name and contact come from their latest submitted order. Spending includes Completed orders only.</p>
<p class="my-5"><?= count($customers) ?> <?= count($customers) === 1 ? 'customer' : 'customers' ?>.</p>
<table class="admin-table"><caption class="sr-only">Customer summaries derived from saved orders</caption><thead><tr><th>Latest name</th><th>Normalized email</th><th>Latest contact</th><th class="num">Orders (all statuses)</th><th class="num">Completed spending</th></tr></thead><tbody>
<?php foreach($customers as $customer): ?><tr><td data-label="Latest name"><?= e($customer['customer_name']) ?></td><td data-label="Email"><?= e($customer['email']) ?></td><td data-label="Latest contact"><?= e($customer['customer_contact']) ?></td><td data-label="Orders" class="num"><?= (int)$customer['order_count'] ?></td><td data-label="Completed spending" class="num"><?= money((int)$customer['spending']) ?></td></tr><?php endforeach; ?>
<?php if(!$customers): ?><tr><td colspan="5">No customers yet. Saved checkouts create these summaries.</td></tr><?php endif; ?></tbody></table>
<?php require dirname(__DIR__,2).'/app/views/admin-footer.php'; ?>
