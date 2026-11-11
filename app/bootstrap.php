<?php
declare(strict_types=1);

date_default_timezone_set('Asia/Manila');
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/database.php';

if (PHP_SAPI !== 'cli' && session_status() !== PHP_SESSION_ACTIVE) {
    // A session is server-side storage linked to a browser by a cookie.
    $sessionDirectory = dirname(__DIR__) . '/tmp/sessions';
    if (!is_dir($sessionDirectory) && !mkdir($sessionDirectory, 0700, true) && !is_dir($sessionDirectory)) {
        throw new RuntimeException('Could not create the local session directory.');
    }
    ini_set('session.use_strict_mode', '1');
    session_save_path($sessionDirectory);
    session_name('ronan_tech_shop');
    session_set_cookie_params([
        'httponly' => true,
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
        'path' => '/',
    ]);
    session_start();
    header('Content-Type: text/html; charset=UTF-8');
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: no-store');
}
