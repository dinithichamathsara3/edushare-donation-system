<?php
// CREATE — public contact form
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/edushare/frontend/contact.html');
}

$name    = clean($_POST['full_name'] ?? '');
$email   = clean($_POST['email'] ?? '');
$message = clean($_POST['message'] ?? '');

if ($name === '' || $message === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect('/edushare/frontend/contact.html?error=' . urlencode('Please fill in all fields with a valid email.'));
}

$stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?,?,?)");
$stmt->execute([$name, $email, $message]);

redirect('/edushare/frontend/contact.html?success=' . urlencode('Thanks — we\'ll get back to you soon.'));
