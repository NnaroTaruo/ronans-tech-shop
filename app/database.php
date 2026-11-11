<?php
declare(strict_types=1);

function database_config(): array
{
    $path = dirname(__DIR__) . '/config/local.php';
    if (!is_file($path)) {
        throw new RuntimeException('Copy config/config.example.php to config/local.php and enter your local database settings.');
    }
    $config = (require $path)['database'];
    // Test servers get a separate, narrowly named database; ordinary requests cannot set this.
    $testDatabase = getenv('TECH_SHOP_TEST_DATABASE');
    if ($testDatabase !== false) {
        if (!preg_match('/\Aronan_tech_shop_test_[a-f0-9]{12}\z/', $testDatabase)) {
            throw new RuntimeException('Invalid isolated test database name.');
        }
        $config['name'] = $testDatabase;
    }
    return $config;
}

function db(): PDO
{
    static $connection = null;
    if ($connection === null) {
        $config = database_config();
        // PDO is PHP's database interface. Prepared statements keep values separate from SQL.
        $connection = new PDO(
            sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
                $config['host'], $config['port'], $config['name']),
            $config['user'],
            $config['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
        // Store database timestamps in UTC; convert for display in later screens.
        $connection->exec("SET time_zone = '+00:00'");
        $connection->exec("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
    }
    return $connection;
}
