<?php
declare(strict_types=1);
require dirname(__DIR__,2).'/app/admin.php';
$admin=require_admin(); $adminSection='categories'; $pageTitle='Categories';
try { $rows=db()->query('SELECT c.id,c.name,COUNT(p.id) AS product_count FROM categories c LEFT JOIN products p ON p.category_id=c.id GROUP BY c.id,c.name ORDER BY c.name')->fetchAll(); }
catch(Throwable $error) { admin_abort(503,'Categories could not load. Check the database and refresh.'); }
require dirname(__DIR__,2).'/app/views/admin-header.php';
?>
<div class="admin-title"><h1>Categories</h1><a class="button inline-flex" href="/admin/category.php">Add category</a></div>
<p class="mt-3">Categories with assigned products cannot be deleted.</p>
<table class="admin-table mt-6"><caption class="sr-only">Catalog categories</caption><thead><tr><th>Category</th><th class="num">Products (all statuses)</th><th>Action</th></tr></thead><tbody>
<?php foreach($rows as $row): ?><tr><td data-label="Category"><?= e($row['name']) ?></td><td class="num" data-label="Products"><?= (int)$row['product_count'] ?></td><td data-label="Action"><a class="nav-link text-link" href="/admin/category.php?id=<?= (int)$row['id'] ?>">Edit<span class="sr-only"> <?= e($row['name']) ?></span></a></td></tr><?php endforeach; ?>
<?php if(!$rows): ?><tr><td colspan="3">No categories yet. Add one before creating products.</td></tr><?php endif; ?></tbody></table>
<?php require dirname(__DIR__,2).'/app/views/admin-footer.php'; ?>
