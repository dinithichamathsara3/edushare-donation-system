<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/public/login.html');
}

$role  = $_POST['role'] ?? 'donor';           // 'donor' or 'institution' (set by the role tabs)
$email = clean($_POST['email'] ?? '');
$pass  = $_POST['password'] ?? '';

if ($role === 'institution') {
    $stmt = $pdo->prepare("SELECT * FROM institutions WHERE official_email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($pass, $user['password_hash'])) {
        redirect('/public/login.html?error=' . urlencode('Invalid email or password.'));
    }
    if ($user['status'] !== 'approved') {
        redirect('/public/login.html?error=' . urlencode('Your institution is not yet approved. Status: ' . $user['status']));
    }
    login_session('institution', (int) $user['id'], $user['institution_name']);
    redirect('/receiver/dashboard.php');
}

// Default: donor login
$stmt = $pdo->prepare("SELECT * FROM donors WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($pass, $user['password_hash'])) {
    redirect('/public/login.html?error=' . urlencode('Invalid email or password.'));
}
if ($user['status'] !== 'active') {
    redirect('/public/login.html?error=' . urlencode('This account has been suspended. Contact support.'));
}
login_session('donor', (int) $user['id'], $user['full_name']);
redirect('/donor/dashboard.php');
