<?php
declare(strict_types=1);
require dirname(__DIR__, 2) . '/app/admin.php';
$admin = require_admin();
admin_post_guard();
unset($_SESSION['admin_id']);
session_regenerate_id(true);
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
admin_redirect('/admin/login.php');
