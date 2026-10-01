<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/public/admin-login.html');
}

$email = clean($_POST['email'] ?? '');
$pass  = $_POST['password'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
$stmt->execute([$email]);
$admin = $stmt->fetch();

if (!$admin || !password_verify($pass, $admin['password_hash'])) {
    // Same generic message whether the email or password was wrong (avoid leaking which one).
    redirect('/public/admin-login.html?error=' . urlencode('Invalid admin credentials.'));
}

login_session('admin', (int) $admin['id'], $admin['name']);
redirect('/admin/dashboard.php');
