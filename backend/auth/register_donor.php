<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/public/donor-register.html');
}

$name    = clean($_POST['full_name'] ?? '');
$email   = clean($_POST['email'] ?? '');
$pass    = $_POST['password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';
$phone   = clean($_POST['phone'] ?? '');
$address = clean($_POST['address'] ?? '');
$location = clean($_POST['location'] ?? '');

// ---- Validation ----
$errors = [];
if ($name === '' || $email === '') $errors[] = 'Name and email are required.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';
if (strlen($pass) < 8) $errors[] = 'Password must be at least 8 characters.';
if ($pass !== $confirm) $errors[] = 'Passwords do not match.';

if ($errors) {
    redirect('/public/donor-register.html?error=' . urlencode(implode(' ', $errors)));
}

// ---- Duplicate check ----
$check = $pdo->prepare("SELECT id FROM donors WHERE email = ?");
$check->execute([$email]);
if ($check->fetch()) {
    redirect('/public/donor-register.html?error=' . urlencode('An account with that email already exists.'));
}

// ---- Insert ----
$hash = password_hash($pass, PASSWORD_DEFAULT);
$stmt = $pdo->prepare(
    "INSERT INTO donors (full_name, email, password_hash, phone, address, location)
     VALUES (?,?,?,?,?,?)"
);
$stmt->execute([$name, $email, $hash, $phone, $address, $location]);
$donorId = (int) $pdo->lastInsertId();

login_session('donor', $donorId, $name);
redirect('/donor/dashboard.php');
