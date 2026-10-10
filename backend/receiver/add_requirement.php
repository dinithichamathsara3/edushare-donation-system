<?php
// CREATE — a verified institution posts a new requirement
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_approved_institution($pdo);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/edushare/frontend/receiver-add-requirement.html');
}

$itemName   = clean($_POST['item_name'] ?? '');
$categoryId = (int) ($_POST['category_id'] ?? 0);
$subject    = clean($_POST['subject'] ?? '');
$level      = clean($_POST['education_level'] ?? '');
$quantity   = (int) ($_POST['quantity_needed'] ?? 1);
$reqDate    = $_POST['required_date'] ?? null;
$desc       = clean($_POST['description'] ?? '');
$urgent     = isset($_POST['is_urgent']) ? 'urgent' : 'normal';

if ($itemName === '' || $quantity < 1) {
    redirect('/edushare/frontend/receiver-add-requirement.html?error=' . urlencode('Item name and a valid quantity are required.'));
}

$stmt = $pdo->prepare(
    "INSERT INTO requirements
     (institution_id, category_id, item_name, subject, education_level,
      quantity_needed, required_date, description, urgency, status)
     VALUES (?,?,?,?,?,?,?,?,?, 'pending')"
);
$stmt->execute([
    current_id(), $categoryId ?: null, $itemName, $subject, $level,
    $quantity, $reqDate ?: null, $desc, $urgent,
]);

redirect('/edushare/frontend/receiver-my-requirements.html?success=' . urlencode('Requirement posted.'));
