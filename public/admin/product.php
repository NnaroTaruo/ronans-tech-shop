<?php
declare(strict_types=1);
require dirname(__DIR__,2).'/app/admin.php';
$admin=require_admin(); $adminSection='products'; $errors=[]; $product=null; $id=null;
try {
    if (isset($_GET['id'])) { $id=admin_id($_GET['id']); $product=admin_product(db(),$id); if(!$product) admin_abort(404,'Product not found.'); }
    $categories=categories();
} catch(InvalidArgumentException $error) { admin_abort(400,$error->getMessage()); }
catch(Throwable $error) { admin_abort(503,'The product form could not load. Check the database and refresh.'); }
$delete=($_GET['delete']??'')==='yes';
if($delete && !$product) admin_abort(400,'Choose a product before deleting.');
$values=$product??['name'=>'','description'=>'','image_path'=>'/assets/images/accessories.svg','price'=>'','stock'=>'0','category_id'=>'','is_active'=>'1','is_featured'=>'0'];
if($product) $values['price']=intdiv((int)$product['price_centavos'],100).'.'.str_pad((string)((int)$product['price_centavos']%100),2,'0',STR_PAD_LEFT);
$revision=$product?product_revision($product):'';
if($_SERVER['REQUEST_METHOD']==='POST') {
    admin_post_guard();
    $revision=is_string($_POST['revision']??null)?$_POST['revision']:'';
    if($delete) {
        try {
            if(($_POST['confirm']??'')!=='yes') throw new InvalidArgumentException('Confirm deletion using this form.');
            db()->beginTransaction(); $current=admin_product(db(),$id,true);
            if(!$current || !hash_equals(product_revision($current),$revision)) throw new InvalidArgumentException('The product changed. Reload before deleting.');
            $query=db()->prepare('SELECT id FROM order_items WHERE product_id=? LIMIT 1'); $query->execute([$id]);
            if($query->fetch()) throw new InvalidArgumentException('This product belongs to saved orders and cannot be deleted. Edit it and set Active to No instead.');
            db()->prepare('DELETE FROM products WHERE id=?')->execute([$id]); db()->commit();
            admin_flash('Product deleted.','/admin/products.php');
        } catch(InvalidArgumentException $error) { if(db()->inTransaction()) db()->rollBack(); http_response_code(409); $errors['_form']=$error->getMessage(); }
        catch(Throwable $error) { if(db()->inTransaction()) db()->rollBack(); http_response_code(503); $errors['_form']='Product was not deleted. Refresh and try again.'; }
    } else {
        $result=validate_product($_POST); $values=$result['values']; $errors=$result['errors'];
        if(!$errors) {
            try { save_admin_product(db(),$id,$values,$revision); admin_flash('Product saved. Customer pages use the new values on refresh.','/admin/products.php'); }
            catch(InvalidArgumentException $error) { $errors['_form']=$error->getMessage(); http_response_code(409); }
            catch(Throwable $error) { $errors['_form']='Product was not saved. Check the category and database, then try again.'; http_response_code(503); }
        } else http_response_code(422);
    }
} elseif($_SERVER['REQUEST_METHOD']!=='GET') admin_abort(405,'Use the product form.');
$pageTitle=$delete?'Delete product':($id?'Edit product':'Add product');
require dirname(__DIR__,2).'/app/views/admin-header.php';
?>
<div class="admin-title"><h1><?= e($pageTitle) ?></h1><a class="nav-link text-link" href="/admin/products.php">Back to products</a></div>
<?php admin_errors($errors); ?>
<?php if($delete): ?>
<div class="admin-login mt-6"><p>Delete <strong><?= e($product['name']) ?></strong> permanently? Products used in orders must stay in the catalog. You can make them inactive.</p>
<form method="post" class="mt-6 flex flex-wrap gap-4"><?php admin_csrf(); ?><input type="hidden" name="revision" value="<?= e($revision) ?>"><input type="hidden" name="confirm" value="yes"><button type="submit" class="button inline-flex">Delete product</button><a class="nav-link text-link" href="/admin/product.php?id=<?= $id ?>">Keep and edit product</a></form></div>
<?php else: ?>
<form method="post" class="admin-form admin-editor mt-6" novalidate>
<?php admin_csrf(); ?><input type="hidden" name="revision" value="<?= e($revision) ?>">
<?php admin_field('name','Product name',$values,$errors); admin_field('description','Description',$values,$errors,'textarea'); ?>
<div><label for="category_id">Category</label><select class="field" name="category_id" id="category_id" aria-invalid="<?= isset($errors['category_id'])?'true':'false' ?>" aria-describedby="category_id-help"><option value="">Choose a category</option><?php foreach($categories as $category): ?><option value="<?= (int)$category['id'] ?>" <?= (string)$values['category_id']===(string)$category['id']?'selected':'' ?>><?= e($category['name']) ?></option><?php endforeach; ?></select><p id="category_id-help" class="mt-2 text-sm <?= isset($errors['category_id'])?'text-danger':'' ?>"><?= e($errors['category_id']??'Create missing categories from Categories.') ?></p></div>
<?php admin_field('image_path','Image path or URL',$values,$errors,'text','Bundled: /assets/images/keyboard.svg, mouse.svg or accessories.svg. Or use an HTTPS image URL. No uploads.'); ?>
<div class="admin-columns"><?php admin_field('price','Price (PHP)',$values,$errors,'text','Positive amount; at most two decimal places, up to 9999999.99.'); admin_field('stock','Stock (units)',$values,$errors,'text','Whole number, 0 or greater.'); ?></div>
<div class="admin-columns"><?php foreach(['is_active'=>'Active','is_featured'=>'Featured on home'] as $field=>$label): ?><div><label for="<?= $field ?>"><?= $label ?></label><select class="field" id="<?= $field ?>" name="<?= $field ?>" aria-invalid="<?= isset($errors[$field])?'true':'false' ?>" aria-describedby="<?= $field ?>-help"><option value="1" <?= (string)$values[$field]==='1'?'selected':'' ?>>Yes</option><option value="0" <?= (string)$values[$field]==='0'?'selected':'' ?>>No</option></select><p id="<?= $field ?>-help" class="mt-2 text-sm <?= isset($errors[$field])?'text-danger':'' ?>"><?= e($errors[$field]??($field==='is_active'?'Active with zero stock displays as Out of Stock.':'Home shows up to four active featured products.')) ?></p></div><?php endforeach; ?></div>
<div class="flex flex-wrap items-center gap-6"><button type="submit" class="button inline-flex">Save product</button><?php if($id): ?><a class="nav-link text-link text-danger" href="/admin/product.php?id=<?= $id ?>&amp;delete=yes">Delete product…</a><a class="nav-link text-link" href="/admin/product.php?id=<?= $id ?>">Reload current values</a><?php endif; ?></div>
</form>
<?php endif; require dirname(__DIR__,2).'/app/views/admin-footer.php'; ?>
