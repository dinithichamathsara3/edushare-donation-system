<?php
// CREATE — a donor lists a new item for donation
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role('donor');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/edushare/frontend/donor-add-donation.html');
}

$itemName   = clean($_POST['item_name'] ?? '');
$categoryId = (int) ($_POST['category_id'] ?? 0);
$subject    = clean($_POST['subject'] ?? '');
$level      = clean($_POST['education_level'] ?? '');
$quantity   = (int) ($_POST['quantity'] ?? 1);
$condition  = clean($_POST['condition_status'] ?? 'Good');
$desc       = clean($_POST['description'] ?? '');
$location   = clean($_POST['location'] ?? '');
$delivery   = clean($_POST['delivery_pref'] ?? 'Either');
$availDate  = $_POST['availability_date'] ?? null;

if ($itemName === '' || $quantity < 1) {
    redirect('/edushare/frontend/donor-add-donation.html?error=' . urlencode('Item name and a valid quantity are required.'));
}

try {
    $image = handle_upload('image', 'donation_images');
} catch (InvalidArgumentException $e) {
    redirect('/edushare/frontend/donor-add-donation.html?error=' . urlencode($e->getMessage()));
}

$stmt = $pdo->prepare(
    "INSERT INTO donations
     (donor_id, category_id, item_name, subject, education_level, quantity,
      condition_status, description, image, location, delivery_pref, availability_date, status)
     VALUES (?,?,?,?,?,?,?,?,?,?,?,?, 'available')"
);
$stmt->execute([
    current_id(), $categoryId ?: null, $itemName, $subject, $level, $quantity,
    $condition, $desc, $image, $location, $delivery, $availDate ?: null,
]);

redirect('/edushare/frontend/donor-my-donations.html?success=' . urlencode('Donation listed successfully.'));
