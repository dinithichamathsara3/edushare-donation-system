<?php
// UPDATE — donor edits their own profile
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role('donor');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/donor/profile.html');
}

$name     = clean($_POST['full_name'] ?? '');
$phone    = clean($_POST['phone'] ?? '');
$address  = clean($_POST['address'] ?? '');
$location = clean($_POST['location'] ?? '');

if ($name === '') {
    redirect('/donor/profile.html?error=' . urlencode('Name cannot be empty.'));
}

try {
    $image = handle_upload('profile_image', 'profile_images');
} catch (InvalidArgumentException $e) {
    redirect('/donor/profile.html?error=' . urlencode($e->getMessage()));
}

if ($image) {
    $stmt = $pdo->prepare(
        "UPDATE donors SET full_name=?, phone=?, address=?, location=?, profile_image=? WHERE id=?"
    );
    $stmt->execute([$name, $phone, $address, $location, $image, current_id()]);
} else {
    $stmt = $pdo->prepare(
        "UPDATE donors SET full_name=?, phone=?, address=?, location=? WHERE id=?"
    );
    $stmt->execute([$name, $phone, $address, $location, current_id()]);
}

// Keep the session display name in sync
$_SESSION['user_name'] = $name;

redirect('/donor/profile.html?success=' . urlencode('Profile updated.'));
