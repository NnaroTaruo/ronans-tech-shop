<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';

$pageTitle = "Setup | Ronan's Tech Shop";
$databaseReady = false;
$setupMessage = 'Copy config/config.example.php to config/local.php, then follow the database setup in README.md.';
$productCount = 0;
$categoryCount = 0;
try {
    $pdo = db();
    $productCount = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
    $categoryCount = (int) $pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn();
    $databaseReady = $productCount >= 10 && $categoryCount >= 3;
    $setupMessage = $databaseReady
        ? 'The shared database is connected and the sample catalog is seeded.'
        : 'The database is connected. Run php scripts/setup-database.php to seed the sample catalog.';
} catch (Throwable $error) {
    // A friendly setup state is safer than showing database internals in the browser.
    if (is_file(dirname(__DIR__) . '/config/local.php')) {
        $setupMessage = 'Database setup needs attention. Start MariaDB, check config/local.php, then run php scripts/setup-database.php.';
    }
}
require dirname(__DIR__) . '/app/views/header.php';
?>
<main id="main" class="mx-auto max-w-6xl px-5 sm:px-8">
    <section class="grid gap-8 py-12 sm:py-16 lg:grid-cols-[1.2fr_1fr] lg:items-center" aria-labelledby="page-heading">
        <div>
            <h1 id="page-heading" class="max-w-lg text-4xl font-semibold tracking-tight sm:text-5xl">Good gear.<br>A fresh start.</h1>
            <p class="mt-7 max-w-lg text-lg leading-relaxed">Keyboards, mice, and everyday desk accessories. We're building Ronan's Tech Shop, one working stage at a time.</p>
            <a class="button mt-7 inline-flex" href="#setup">Check the setup <span aria-hidden="true">→</span></a>
        </div>
        <aside class="bg-surface p-7 sm:p-9" aria-labelledby="foundation-heading">
            <h2 id="foundation-heading" class="text-2xl font-semibold">The foundation is here.</h2>
            <p class="mt-4 leading-relaxed">This first stage connects PHP to the database and gives every future page the same navigation, spacing, and footer.</p>
            <p class="mt-5 leading-relaxed">Shopping, checkout, and admin screens will arrive in the next stages.</p>
        </aside>
    </section>
    <section id="setup" class="bg-white p-6 sm:p-9" aria-labelledby="setup-heading">
        <div class="grid gap-6 md:grid-cols-[1.3fr_1fr] md:items-center">
            <div>
                <h2 id="setup-heading" class="text-2xl font-semibold"><?= $databaseReady ? 'Database connected' : 'Finish local setup' ?></h2>
                <p class="mt-3 max-w-xl leading-relaxed"><?= e($setupMessage) ?></p>
            </div>
            <?php if ($databaseReady): ?>
                <dl class="flex gap-12"><div><dt>Seeded products</dt><dd class="mt-2 text-3xl font-semibold"><?= $productCount ?></dd></div><div><dt>Categories</dt><dd class="mt-2 text-3xl font-semibold"><?= $categoryCount ?></dd></div></dl>
            <?php else: ?>
                <p class="bg-surface p-5 leading-relaxed">Open <code>README.md</code> in VS Code for the Windows setup commands. Refresh this page after seeding.</p>
            <?php endif; ?>
        </div>
    </section>
    <section id="plan" class="pt-12" aria-labelledby="plan-heading">
        <h2 id="plan-heading" class="text-2xl font-semibold">Build plan</h2>
        <ol class="mt-5 grid list-inside list-decimal gap-x-8 gap-y-4 sm:grid-cols-2">
            <li>Setup, schema, seeds, shared layout</li>
            <li>Browse products and build a cart</li>
            <li>Checkout and save orders safely</li>
            <li>Admin login and store management</li>
            <li>Test, document, and prepare the demo</li>
        </ol>
        <p class="mt-7 max-w-prose leading-relaxed">Stage 1 learning check: open the README, run the foundation checks, and trace this page through the shared PHP files.</p>
    </section>
</main>
<?php require dirname(__DIR__) . '/app/views/footer.php'; ?>
