<?php declare(strict_types=1); ?>
<!doctype html>
<html lang="en-PH">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title><?= e($pageTitle ?? 'Admin') ?> | Ronan's Tech Shop</title>
<link rel="icon" href="/assets/favicon.svg" type="image/svg+xml">
<link rel="stylesheet" href="/assets/css/app.css">
<script src="/assets/js/admin.js" defer></script>
</head>
<body class="admin-body bg-ground text-ink antialiased">
<a class="skip-link" href="#main">Skip to content</a>
<div class="admin-shell <?= !empty($loginSurface) ? 'admin-login-shell' : '' ?>">
<header class="admin-sidebar">
    <a class="brand flex items-center gap-3 font-semibold" href="/admin/">
        <svg aria-hidden="true" width="36" height="36" viewBox="0 0 36 36" fill="none" class="text-accent"><rect x="3" y="8" width="30" height="21" rx="3" stroke="currentColor" stroke-width="2.5"/><path d="M8 14h3m5 0h3m5 0h3M8 20h3m5 0h3m5 0h3M12 25h12" stroke="currentColor" stroke-width="2.5"/></svg>
        <span>Ronan's Tech Shop<span class="block text-sm font-normal">Demo administration</span></span>
    </a>
    <?php if (isset($admin)): ?>
    <details class="admin-menu" open><summary>Admin menu</summary>
        <nav aria-label="Admin navigation">
        <?php foreach (['index' => 'Dashboard','products' => 'Products','categories' => 'Categories','orders' => 'Orders','customers' => 'Customers'] as $route => $label): ?>
            <a href="/admin/<?= $route === 'index' ? '' : $route . '.php' ?>" <?= ($adminSection ?? '') === $route ? 'aria-current="page"' : '' ?>><?= e($label) ?></a>
        <?php endforeach; ?>
        </nav>
    </details>
    <div class="admin-account"><p class="text-sm break-all"><?= e($admin['email']) ?></p><form action="/admin/logout.php" method="post"><?php admin_csrf(); ?><button class="nav-link text-link" type="submit">Log out</button></form></div>
    <?php endif; ?>
    <a class="nav-link text-link" href="/">View store</a>
</header>
<main id="main" class="admin-main">
<?php if (isset($_SESSION['admin_flash'])): ?><p class="notice mb-6" role="status"><?= e($_SESSION['admin_flash']) ?></p><?php unset($_SESSION['admin_flash']); endif; ?>
