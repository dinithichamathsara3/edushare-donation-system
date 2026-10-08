<?php
// UPDATE — institution edits its own profile (not the verification-sensitive fields)
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role('institution'); // profile can be edited even while pending

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/receiver/profile.html');
}

$contactPerson = clean($_POST['contact_person'] ?? '');
$contactNumber = clean($_POST['contact_number'] ?? '');
$address       = clean($_POST['address'] ?? '');
$description   = clean($_POST['description'] ?? '');

$stmt = $pdo->prepare(
    "UPDATE institutions SET contact_person=?, contact_number=?, address=?, description=? WHERE id=?"
);
$stmt->execute([$contactPerson, $contactNumber, $address, $description, current_id()]);

redirect('/receiver/profile.html?success=' . urlencode('Profile updated.'));
