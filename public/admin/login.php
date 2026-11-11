<?php
declare(strict_types=1);
require dirname(__DIR__, 2) . '/app/admin.php';
$errors = []; $values = ['email' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_post_guard();
    try {
        $values['email'] = strtolower(admin_text($_POST['email'] ?? '', 254));
        $password = $_POST['password'] ?? null;
        if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL) || !is_string($password) || strlen($password) > 1024) throw new InvalidArgumentException('Email or password is incorrect.');
        $query = db()->prepare('SELECT * FROM admins WHERE email=?');
        $query->execute([$values['email']]); $record = $query->fetch();
        // Always verify a hash, including unknown emails.
        $hash = $record['password_hash'] ?? '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.';
        if (!password_verify($password, $hash) || !$record) throw new InvalidArgumentException('Email or password is incorrect.');
        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int) $record['id'];
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        admin_redirect('/admin/');
    } catch (InvalidArgumentException $error) { http_response_code(422); $errors['_form'] = $error->getMessage(); }
    catch (Throwable $error) { http_response_code(503); $errors['_form'] = 'Login is unavailable. Check the database and try again.'; }
} elseif ($_SERVER['REQUEST_METHOD'] !== 'GET') admin_abort(405, 'Use the login form.');
$pageTitle = 'Admin login'; $loginSurface = true;
require dirname(__DIR__, 2) . '/app/views/admin-header.php';
?>
<div class="admin-login"><h1>Admin login</h1><p class="mt-3">Manage the fictional shop's catalog and orders.</p>
<?php admin_errors($errors); ?>
<form method="post" class="admin-form mt-6" novalidate>
<?php admin_csrf(); admin_field('email','Email',$values,$errors,'email'); admin_field('password','Password',[],$errors,'password'); ?>
<button class="button inline-flex" type="submit">Log in</button>
</form></div>
<?php require dirname(__DIR__, 2) . '/app/views/admin-footer.php'; ?>
