<?php
require __DIR__ . '/config.php';
require __DIR__ . '/includes/auth.php';

$user = current_user();
if ($user) {
    header('Location: ' . ($user['role'] === 'admin' ? '/admin/index.php' : '/dashboard.php'));
} else {
    header('Location: /login.php');
}
exit;
