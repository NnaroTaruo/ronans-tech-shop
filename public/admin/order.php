<?php
declare(strict_types=1);
require dirname(__DIR__,2).'/app/admin.php';
$admin=require_admin(); $adminSection='orders'; $errors=[];
try {
    $id=admin_id($_GET['id']??null);
    $query=db()->prepare('SELECT * FROM orders WHERE id=?'); $query->execute([$id]); $order=$query->fetch();
    if(!$order) admin_abort(404,'Order not found.');
    if($_SERVER['REQUEST_METHOD']==='POST') {
        admin_post_guard();
        try {
            $expected=admin_text($_POST['expected_status']??'',20); $next=admin_text($_POST['next_status']??'',20);
            if($next==='Cancelled'&&($_POST['confirm_cancel']??'')!=='yes') throw new InvalidArgumentException('Confirm cancellation and stock restoration.');
            change_order_status(db(),$id,$expected,$next);
            admin_flash($next==='Cancelled'?'Order cancelled. Stock was restored once.':'Order status updated to '.$next.'.','/admin/order.php?id='.$id);
        } catch(InvalidArgumentException $error) { http_response_code(409); $errors['_form']=$error->getMessage(); }
        catch(Throwable $error) { http_response_code(503); $errors['_form']='Status was not changed. Stock and status were rolled back together. Refresh and try again.'; }
        $query->execute([$id]); $order=$query->fetch();
    } elseif($_SERVER['REQUEST_METHOD']!=='GET') admin_abort(405,'Use the status form.');
    $query=db()->prepare('SELECT * FROM order_items WHERE order_id=? ORDER BY product_id'); $query->execute([$id]); $items=$query->fetchAll();
} catch(InvalidArgumentException $error) { admin_abort(400,$error->getMessage()); }
catch(Throwable $error) { admin_abort(503,'The order could not load. Check the database and refresh.'); }
$pageTitle='Order details';
require dirname(__DIR__,2).'/app/views/admin-header.php';
?>
<div class="admin-title"><h1>Order details</h1><a class="nav-link text-link" href="/admin/orders.php">Back to orders</a></div>
<p class="mt-3"><span class="block break-all"><?= e($order['order_number']) ?></span><span class="block"><?= e(admin_date($order['created_at'])) ?></span></p>
<?php admin_errors($errors); ?>
<section class="admin-panel mt-6"><h2>Status: <?= e($order['status']) ?></h2>
<?php $allowed=ORDER_TRANSITIONS[$order['status']]; if($allowed): ?>
<form method="post" class="admin-status-form mt-5"><?php admin_csrf(); ?><input type="hidden" name="expected_status" value="<?= e($order['status']) ?>">
<div><label for="next_status" class="block mb-2">Next status</label><select class="field" id="next_status" name="next_status"><?php foreach($allowed as $next): ?><option><?= e($next) ?></option><?php endforeach; ?></select></div>
<?php if(in_array('Cancelled',$allowed,true)): ?><label class="admin-check"><input type="checkbox" name="confirm_cancel" value="yes"> I confirm cancellation if selected, and restoration of this order's stock.</label><?php endif; ?>
<button type="submit" class="button inline-flex">Update status</button>
</form>
<?php else: ?><p class="mt-3">This order is final. No further status changes are allowed.</p><?php endif; ?>
<?php if($order['stock_restored_at']!==null): ?><p class="mt-3">Stock restored: <?= e(admin_date($order['stock_restored_at'])) ?></p><?php endif; ?>
</section>
<div class="admin-columns mt-6"><section class="admin-panel"><h2>Customer and delivery</h2><dl class="admin-details mt-4"><dt>Name</dt><dd><?= e($order['customer_name']) ?></dd><dt>Email</dt><dd><?= e($order['customer_email']) ?></dd><dt>Contact</dt><dd><?= e($order['customer_contact']) ?></dd><dt>Address</dt><dd class="whitespace-pre-wrap"><?= e($order['delivery_address']) ?></dd></dl></section>
<section class="admin-panel"><h2>Payment and notes</h2><dl class="admin-details mt-4"><dt>Payment selection</dt><dd><?= e(PAYMENT_METHODS[$order['payment_method']]) ?> (demo only)</dd><dt>Order notes</dt><dd class="whitespace-pre-wrap"><?= e($order['notes']??'No notes.') ?></dd></dl></section></div>
<section class="mt-8"><h2>Saved items</h2><table class="admin-table mt-4"><caption class="sr-only">Product names and prices saved at checkout</caption><thead><tr><th>Product snapshot</th><th class="num">Quantity</th><th class="num">Unit price</th><th class="num">Line total</th></tr></thead><tbody>
<?php foreach($items as $item): ?><tr><td data-label="Product"><?= e($item['product_name']) ?></td><td data-label="Quantity" class="num"><?= (int)$item['quantity'] ?></td><td data-label="Unit price" class="num"><?= money((int)$item['unit_price_centavos']) ?></td><td data-label="Line total" class="num"><?= money((int)$item['unit_price_centavos']*(int)$item['quantity']) ?></td></tr><?php endforeach; ?></tbody></table>
<dl class="admin-totals mt-5"><div><dt>Subtotal</dt><dd><?= money((int)$order['subtotal_centavos']) ?></dd></div><div class="font-semibold"><dt>Total</dt><dd><?= money((int)$order['total_centavos']) ?></dd></div></dl></section>
<?php require dirname(__DIR__,2).'/app/views/admin-footer.php'; ?>
