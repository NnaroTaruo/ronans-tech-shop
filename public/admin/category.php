<?php
declare(strict_types=1);
require dirname(__DIR__,2).'/app/admin.php';
$admin=require_admin(); $adminSection='categories'; $id=null; $category=null; $errors=[];
try {
    if(isset($_GET['id'])) { $id=admin_id($_GET['id']); $query=db()->prepare('SELECT * FROM categories WHERE id=?'); $query->execute([$id]); $category=$query->fetch(); if(!$category) admin_abort(404,'Category not found.'); }
} catch(InvalidArgumentException $error) { admin_abort(400,$error->getMessage()); }
catch(Throwable $error) { admin_abort(503,'The category could not load. Check the database and refresh.'); }
$delete=($_GET['delete']??'')==='yes'; if($delete&&!$category) admin_abort(400,'Choose a category before deleting.');
$values=['name'=>$category['name']??''];
if($_SERVER['REQUEST_METHOD']==='POST') {
    admin_post_guard();
    try {
        if($delete) {
            if(($_POST['confirm']??'')!=='yes') throw new InvalidArgumentException('Confirm deletion using this form.');
            $query=db()->prepare('DELETE FROM categories WHERE id=?'); $query->execute([$id]);
            if($query->rowCount()!==1) throw new InvalidArgumentException('Category no longer exists. Refresh the list.');
            admin_flash('Category deleted.','/admin/categories.php');
        }
        $values['name']=is_string($_POST['name']??null)?$_POST['name']:'';
        try { $name=admin_text($_POST['name']??'',100); }
        catch(InvalidArgumentException $error) { $errors['name']=$error->getMessage(); throw $error; }
        if($id) { $query=db()->prepare('UPDATE categories SET name=? WHERE id=?'); $query->execute([$name,$id]); }
        else { $query=db()->prepare('INSERT INTO categories (name) VALUES (?)'); $query->execute([$name]); }
        admin_flash('Category saved.','/admin/categories.php');
    } catch(InvalidArgumentException $error) { http_response_code(422); if(!$errors) $errors['_form']=$error->getMessage(); }
    catch(PDOException $error) {
        $code=(int)($error->errorInfo[1]??0);
        http_response_code(in_array($code,[1062,1451],true)?409:503);
        if($code===1062) $errors['name']='That category already exists. Names are case-insensitive.';
        else $errors['_form']=$code===1451?'This category has assigned products, including inactive products. Move or delete them first.':'Category was not saved. Check the database and try again.';
    }
} elseif($_SERVER['REQUEST_METHOD']!=='GET') admin_abort(405,'Use the category form.');
$pageTitle=$delete?'Delete category':($id?'Edit category':'Add category');
require dirname(__DIR__,2).'/app/views/admin-header.php';
?>
<div class="admin-title"><h1><?= e($pageTitle) ?></h1><a class="nav-link text-link" href="/admin/categories.php">Back to categories</a></div>
<?php admin_errors($errors); ?>
<form method="post" class="admin-form admin-login mt-6" novalidate><?php admin_csrf(); ?>
<?php if($delete): ?><p>Delete <strong><?= e($category['name']) ?></strong> permanently? Any assigned product blocks deletion.</p><input type="hidden" name="confirm" value="yes"><button type="submit" class="button inline-flex">Delete category</button>
<?php else: admin_field('name','Category name',$values,$errors,'text','1 to 100 characters; category names must be unique.'); ?><div class="flex flex-wrap items-center gap-6"><button class="button inline-flex" type="submit">Save category</button><?php if($id): ?><a class="nav-link text-link text-danger" href="/admin/category.php?id=<?= $id ?>&amp;delete=yes">Delete category…</a><?php endif; ?></div><?php endif; ?>
</form>
<?php require dirname(__DIR__,2).'/app/views/admin-footer.php'; ?>
