<?php
// READ — tells the front-end pages who is logged in (name, role, and the donor's profile details)
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

if (!is_logged_in()) {
    json_response(['logged_in' => false]);
}

$out = [
    'logged_in' => true,
    'role'      => current_role(),
    'name'      => $_SESSION['user_name'],
];

// Donors also get their profile details, so the My Profile page can fill its form
if (current_role() === 'donor') {
    $stmt = $pdo->prepare("SELECT full_name, email, phone, location, address FROM donors WHERE id = ?");
    $stmt->execute([current_id()]);
    $out['profile'] = $stmt->fetch();
}

json_response($out);
