<?php declare(strict_types=1); ?>
<!doctype html>
<html lang="en-PH">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Ronan's Tech Shop: a fictional electronics store built for a web development internship assessment.">
    <title><?= e($pageTitle ?? "Ronan's Tech Shop") ?></title>
    <link rel="icon" href="/assets/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="/assets/css/app.css">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <script type="module" src="/assets/js/cart.js"></script>
</head>
<body class="min-h-screen bg-ground text-ink antialiased">
<a class="skip-link" href="#main">Skip to content</a>
<div class="bg-ink px-5 py-2 text-center text-sm text-white">Fictional store · Internship assessment · No real payments</div>
<header class="bg-white">
    <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-5 px-5 py-6 sm:px-8">
        <a class="brand flex items-center gap-3 font-semibold" href="/" aria-label="Ronan's Tech Shop home">
            <svg aria-hidden="true" class="h-9 w-9 text-accent" viewBox="0 0 36 36" fill="none"><rect x="3" y="8" width="30" height="21" rx="3" stroke="currentColor" stroke-width="2.5"/><path d="M8 14h3m5 0h3m5 0h3M8 20h3m5 0h3m5 0h3M12 25h12" stroke="currentColor" stroke-width="2.5"/></svg>
            <span>Ronan's <span class="block text-sm font-normal">Tech Shop</span></span>
        </a>
        <nav class="flex flex-wrap gap-x-6 gap-y-2 text-base" aria-label="Main navigation">
            <a class="nav-link" href="/">Home</a>
            <a class="nav-link" href="/products.php">Products</a>
            <a class="nav-link" href="/cart.php">Cart <span class="ml-1" data-cart-count>(0)</span></a>
            <a class="nav-link" href="/#contact">Contact</a>
        </nav>
    </div>
</header>
<div id="cart-feedback" class="mx-auto hidden max-w-6xl px-5 pt-5 sm:px-8" role="status" aria-live="polite" tabindex="-1"></div>
<noscript><p class="mx-auto max-w-6xl px-5 py-4">You can browse products without JavaScript. Enable JavaScript to add products, manage your cart, and check out.</p></noscript>
