<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/public/institution-register.html');
}

$fields = [
    'institution_name' => clean($_POST['institution_name'] ?? ''),
    'institution_type' => clean($_POST['institution_type'] ?? 'School'),
    'reg_number'        => clean($_POST['reg_number'] ?? ''),
    'official_email'    => clean($_POST['official_email'] ?? ''),
    'contact_person'    => clean($_POST['contact_person'] ?? ''),
    'contact_number'    => clean($_POST['contact_number'] ?? ''),
    'address'           => clean($_POST['address'] ?? ''),
    'province'          => clean($_POST['province'] ?? ''),
    'district'          => clean($_POST['district'] ?? ''),
    'city'              => clean($_POST['city'] ?? ''),
    'description'       => clean($_POST['description'] ?? ''),
];
$pass    = $_POST['password'] ?? '';

$errors = [];
if ($fields['institution_name'] === '' || $fields['reg_number'] === '') {
    $errors[] = 'Institution name and registration number are required.';
}
if (!filter_var($fields['official_email'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid official email address.';
}
if (strlen($pass) < 8) {
    $errors[] = 'Password must be at least 8 characters.';
}
if (empty($_FILES['verification_doc']['name'])) {
    $errors[] = 'A verification document is required.';
}
if ($errors) {
    redirect('/public/institution-register.html?error=' . urlencode(implode(' ', $errors)));
}

$check = $pdo->prepare("SELECT id FROM institutions WHERE official_email = ?");
$check->execute([$fields['official_email']]);
if ($check->fetch()) {
    redirect('/public/institution-register.html?error=' . urlencode('An institution with that email already exists.'));
}

try {
    $docPath = handle_upload('verification_doc', 'verification_docs');
} catch (InvalidArgumentException $e) {
    redirect('/public/institution-register.html?error=' . urlencode($e->getMessage()));
}

$hash = password_hash($pass, PASSWORD_DEFAULT);

$stmt = $pdo->prepare(
    "INSERT INTO institutions
     (institution_name, institution_type, reg_number, official_email, password_hash,
      contact_person, contact_number, address, province, district, city,
      verification_doc, description, status)
     VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?, 'pending')"
);
$stmt->execute([
    $fields['institution_name'], $fields['institution_type'], $fields['reg_number'],
    $fields['official_email'], $hash, $fields['contact_person'], $fields['contact_number'],
    $fields['address'], $fields['province'], $fields['district'], $fields['city'],
    $docPath, $fields['description'],
]);

// Institutions cannot log in until an admin approves them — send them to a "pending" page.
redirect('/public/registration-submitted.html');
