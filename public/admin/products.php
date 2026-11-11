<?php
declare(strict_types=1);
require dirname(__DIR__, 2) . '/app/admin.php';
$admin = require_admin(); $adminSection = 'products'; $pageTitle = 'Products';
try {
    $search = admin_text($_GET['q'] ?? '',120,false);
    $category = ($_GET['category'] ?? '') === '' ? null : admin_id($_GET['category']);
    $status = $_GET['status'] ?? '';
    $statuses = [''=>'All statuses','active'=>'Active (in stock)','inactive'=>'Inactive','out'=>'Out of Stock'];
    if (!is_string($status) || !isset($statuses[$status])) throw new InvalidArgumentException('Choose a valid displayed status.');
    $sql = PRODUCT_SELECT . ' WHERE 1=1'; $args=[];
    if ($search !== '') { $sql .= " AND p.name LIKE ? ESCAPE '='"; $args[]='%'.strtr($search,['='=>'==','%'=>'=%','_'=>'=_']).'%'; }
    if ($category !== null) { $sql.=' AND p.category_id=?'; $args[]=$category; }
    $sql .= [''=>'','active'=>' AND p.is_active=1 AND p.stock>0','inactive'=>' AND p.is_active=0','out'=>' AND p.is_active=1 AND p.stock=0'][$status];
    $query=db()->prepare($sql.' ORDER BY p.name,p.id'); $query->execute($args); $products=product_rows($query);
    $categories=categories();
} catch (InvalidArgumentException $error) { admin_abort(400,$error->getMessage()); }
catch (Throwable $error) { admin_abort(503,'Products could not load. Check the database and refresh.'); }
require dirname(__DIR__,2).'/app/views/admin-header.php';
?>
<div class="admin-title"><h1>Products</h1><a class="button inline-flex" href="/admin/product.php">Add product</a></div>
<details class="admin-filters mt-6" open><summary>Search and filters</summary><form method="get" class="filters mt-3">
<div><label for="q">Product name</label><input class="field" id="q" name="q" value="<?= e($search) ?>" maxlength="120"></div>
<div><label for="category">Category</label><select class="field" id="category" name="category"><option value="">All categories</option><?php foreach ($categories as $row): ?><option value="<?= (int)$row['id'] ?>" <?= $category===$row['id'] || $category===(int)$row['id'] ? 'selected':'' ?>><?= e($row['name']) ?></option><?php endforeach; ?></select></div>
<div><label for="status">Displayed status</label><select class="field" id="status" name="status"><?php foreach($statuses as $value=>$label): ?><option value="<?= e($value) ?>" <?= $status===$value?'selected':'' ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
<button class="button button-secondary inline-flex" type="submit">Apply</button></form></details>
<p class="my-5"><?= count($products) ?> <?= count($products) === 1 ? 'product matches' : 'products match' ?>. <a class="text-link" href="/admin/products.php">Clear filters</a></p>
<table class="admin-table"><caption class="sr-only">Products matching current filters</caption><thead><tr><th>Product</th><th>Category</th><th class="num">Price</th><th class="num">Stock</th><th>Status</th><th>Action</th></tr></thead><tbody>
<?php foreach($products as $product): ?><tr>
<td data-label="Product"><span class="admin-product-name"><img src="<?= e(product_image($product['image_path'])) ?>" alt="" width="48" height="36"><span><?= e($product['name']) ?></span></span></td>
<td data-label="Category"><?= e($product['category_name']) ?></td><td class="num" data-label="Price"><?= money($product['price_centavos']) ?></td><td class="num" data-label="Stock"><?= $product['stock'] ?></td>
<td data-label="Status"><?= !$product['is_active']?'Inactive':($product['stock']===0?'Out of Stock':'Active') ?></td><td data-label="Action"><a class="nav-link text-link" href="/admin/product.php?id=<?= $product['id'] ?>">Edit<span class="sr-only"> <?= e($product['name']) ?></span></a></td></tr><?php endforeach; ?>
<?php if(!$products): ?><tr><td colspan="6">No products match these filters.</td></tr><?php endif; ?>
</tbody></table>
<?php require dirname(__DIR__,2).'/app/views/admin-footer.php'; ?>
